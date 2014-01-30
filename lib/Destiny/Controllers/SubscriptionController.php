<?php
namespace Destiny\Controllers;

use Destiny\Common\Exception;
use Destiny\Common\ViewModel;
use Destiny\Common\Session;
use Destiny\Common\Config;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\Transactional;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Commerce\SubscriptionsService;
use Destiny\Commerce\OrdersService;
use Destiny\Commerce\PayPalApiService;
use Destiny\Commerce\PaymentProfileStatus;
use Destiny\Commerce\SubscriptionStatus;
use Destiny\Common\Authentication\AuthenticationService;
use Destiny\Common\Application;
use Destiny\Commerce\OrderStatus;
use Destiny\Common\Utils\Date;
use Destiny\Chat\ChatIntegrationService;
use Destiny\Common\User\UserService;
use Destiny\Common\User\UserRole;
use Destiny\Common\Utils\FilterParams;
use Destiny\Commerce\PaymentStatus;

/**
 * @Controller
 */
class SubscriptionController {
	
	/**
	 * @Route ("/subscribe")
	 *
	 * Build subscribe checkout form
	 *
	 * @param array $params        	
	 */
	public function subscribe(array $params, ViewModel $model) {
		$subscriptionsService = SubscriptionsService::instance ();
		$subscription = $subscriptionsService->getUserPendingSubscription ( Session::getCredentials ()->getUserId () );
		if (! empty ( $subscription )) {
			throw new Exception ( 'You already have a subscription in the "pending" state. Please cancel this first.' );
		}
		$subscription = $subscriptionsService->getUserActiveSubscription ( Session::getCredentials ()->getUserId () );
		$model->title = 'Subscribe';
		$model->subscriptions = Config::$a ['commerce'] ['subscriptions'];
		$model->formAction = (empty ( $subscription )) ? '/subscription/confirm' : '/subscription/update/confirm';
		return 'subscribe';
	}
	
	/**
	 * @Route ("/subscription/update/confirm")
	 * @Secure ({"USER"})
	 *
	 * @param array $params        	
	 */
	public function subscriptionUpdateConfirm(array $params, ViewModel $model) {
		FilterParams::isRequired($params, 'subscription');
		
		$subscriptionsService = SubscriptionsService::instance ();
		
		$userId = Session::getCredentials ()->getUserId ();
		
		$currentSubscription = $subscriptionsService->getUserActiveSubscription ( $userId );
		if (empty ( $currentSubscription )) {
			throw new Exception ( 'Subscription required' );
		}
		
		$currentSubscriptionType = $subscriptionsService->getSubscriptionType ( $currentSubscription ['subscriptionType'] );
		$subscriptionType = $subscriptionsService->getSubscriptionType ( $params ['subscription'] );
		$model->currentSubscription = $currentSubscription;
		$model->currentSubscriptionType = $currentSubscriptionType;
		$model->subscriptionType = $subscriptionType;
		return 'subscription/updateconfirm';
	}
	
	/**
	 * @Route ("/subscription/update")
	 * @Secure ({"USER"})
	 * @HttpMethod ({"POST"})
	 *
	 * @param array $params        	
	 */
	public function subscriptionUpdateConfirmProcess(array $params, ViewModel $model) {
		FilterParams::isRequired($params, 'subscription');
		
		$subscriptionsService = SubscriptionsService::instance ();
		$ordersService = OrdersService::instance ();
		$payPalApiService = PayPalApiService::instance ();
		
		$userId = Session::getCredentials ()->getUserId ();
		
		$currentSubscription = $subscriptionsService->getUserActiveSubscription ( $userId );
		if (empty ( $currentSubscription )) {
			throw new Exception ( 'Existing subscription required' );
		}
		$currentSubscriptionType = $subscriptionsService->getSubscriptionType ( $currentSubscription ['subscriptionType'] );
		$subscriptionType = $subscriptionsService->getSubscriptionType ( $params ['subscription'] );
		
		try {

			// isRecurring
			$recurring = (isset ( $params ['renew'] ) && $params ['renew'] == '1');
			
			// The upgrade is basically cancelling the recurring payment
			// no need to create a PENDING order, we simply cancel it locally and redirect
			// @TODO should be done better
			if ($currentSubscriptionType ['id'] == $subscriptionType ['id'] && $currentSubscription ['recurring'] == 1) {
				$paymentProfile = $ordersService->getPaymentProfileById ( $currentSubscription ['paymentProfileId'] );
				if (! empty ( $paymentProfile )) {
					$payPalApiService->cancelPaymentProfile ( $currentSubscription, $paymentProfile );
				}
				Session::set ( 'modelSuccess', 'Recurring payment profile cancelled' );
				return 'redirect: /profile';
			}
	
			// Create NEW order
			$order = $ordersService->createSubscriptionOrder ( $subscriptionType, $userId );
			$subscriptionId = $subscriptionsService->createSubscriptionFromOrder ( $order, $subscriptionType );
			$paymentProfile = null;

			// None recurring to recurring, we need to add a payment profile
			// But since this isnt an upgrade, we dont need an immediate payment
			if ($currentSubscriptionType ['id'] == $subscriptionType ['id'] && $currentSubscription ['recurring'] == 0 && $recurring) {
				// We set the billing start date to the end of the current subscription
				$billingStartDate = Date::getDateTime ( $currentSubscription ['endDate'] );
				$paymentProfile = $ordersService->createPaymentProfile ( $userId, $order, $subscriptionType, $billingStartDate );
				$setECResponse = $payPalApiService->getNoPaymentECResponse ( '/subscription/update/process', $order, $subscriptionType );
				if (empty ( $setECResponse ) || $setECResponse->Ack != 'Success') {
					throw new Exception ( 'Failed to create payment profile' );
				}
				return 'redirect: ' . Config::$a ['paypal'] ['api'] ['endpoint'] . urlencode ( $setECResponse->Token );
			}
			
			// Add payment profile
			if ($recurring) {
				$billingStartDate = Date::getDateTime ( date ( 'm/d/y' ) );
				$billingStartDate->modify ( '+' . $subscriptionType ['billingFrequency'] . ' ' . strtolower ( $subscriptionType ['billingPeriod'] ) );
				$paymentProfile = $ordersService->createPaymentProfile ( $userId, $order, $subscriptionType, $billingStartDate );
			}
			
			// Send auth request to paypal
			$setECResponse = $payPalApiService->createECResponse ( '/subscription/update/process', $order, $subscriptionType, $recurring );
			if (empty ( $setECResponse ) || $setECResponse->Ack != 'Success') {
				throw new Exception ( $setECResponse->Errors->ShortMessage );
			}
			return 'redirect: ' . Config::$a ['paypal'] ['api'] ['endpoint'] . urlencode ( $setECResponse->Token );
			
		} catch (Exception $e) {
			
			if (! empty ( $order ))
				$ordersService->updateOrderState ( $order ['orderId'], OrderStatus::ERROR );
			if (! empty ( $paymentProfile ))
				$ordersService->updatePaymentStatus ( $paymentProfile ['paymentId'], PaymentStatus::ERROR );
			if (! empty ( $subscriptionId ))
				$subscriptionsService->updateSubscriptionState ( $subscriptionId, SubscriptionStatus::ERROR );
			
			$log = Application::instance ()->getLogger ();
			$log->error ( $e->getMessage(), $order);
			return 'redirect: /subscription/' . urlencode ( $order ['orderId'] ) . '/error';
		}
		
	}
	
	/**
	 * @Route ("/subscription/update/process")
	 * @Secure ({"USER"})
	 * @Transactional
	 *
	 * We were redirected here from PayPal after the buyer approved/cancelled the payment
	 * 
	 * @param array $params        	
	 */
	public function subscriptionUpdateProcess(array $params, ViewModel $model) {
		FilterParams::isRequired($params, 'orderId');
		FilterParams::isRequired($params, 'token');
		FilterParams::isRequired($params, 'success');
	
		$ordersService = OrdersService::instance ();
		$subscriptionsService = SubscriptionsService::instance ();
		$payPalApiService = PayPalApiService::instance ();
		$authService = AuthenticationService::instance ();
		$userService = UserService::instance();
		$chat = ChatIntegrationService::instance ();
			
		$userId = Session::getCredentials ()->getUserId ();
			
		$order = $ordersService->getOrderByIdAndUserId ( $params ['orderId'], $userId );
		if (empty ( $order ) || strcasecmp($order ['state'], OrderStatus::_NEW) !== 0) {
			throw new Exception ( 'Invalid order record' );
		}
	
		try {
			
			// is user trying to upgrade subscription
			$isSubscriptionUpgrade = false;
			
			$paymentProfile = $ordersService->getPaymentProfileByOrderId ( $order ['orderId'] );
			$orderSubscription = $subscriptionsService->getSubscriptionByOrderIdAndUserId ( $order ['orderId'], $order ['userId'] );
			if (empty ( $orderSubscription )) {
				throw new Exception ( 'Invalid subscription record' );
			}
			$subscriptionType = $subscriptionsService->getSubscriptionType ( $orderSubscription ['subscriptionType'] );

			// If we got a failed response URL
			if ($params ['success'] == '0' || $params ['success'] == 'false' || $params ['success'] === false) {
				throw new Exception ( 'Order response failed' );
			}
			
			// Get the checkout info
			$ecResponse = $payPalApiService->retrieveCheckoutInfo ( $params ['token'] );
			if (! isset ( $ecResponse ) || $ecResponse->Ack != 'Success') {
				throw new Exception ( 'Failed to retrieve express checkout details' );
			}
			
			if (! empty ( $paymentProfile )) {
				$createRPProfileResponse = $payPalApiService->createRecurringPaymentProfile ( $paymentProfile, $params ['token'], $subscriptionType );
				if (! isset ( $createRPProfileResponse ) || $createRPProfileResponse->Ack != 'Success') {
					throw new Exception ( 'Failed to create recurring payment request' );
				}
				$paymentProfileId = $createRPProfileResponse->CreateRecurringPaymentsProfileResponseDetails->ProfileID;
				$paymentStatus = $createRPProfileResponse->CreateRecurringPaymentsProfileResponseDetails->ProfileStatus;
				if (empty ( $paymentProfileId )) {
					throw new Exception ( 'Invalid recurring payment profileId returned from Paypal' );
				}
				// Set the payment profile to active, and paymetProfileId
				$ordersService->updatePaymentProfileId ( $paymentProfile ['profileId'], $paymentProfileId, $paymentStatus );
			}
			
			// Complete the checkout
			$DoECResponse = $payPalApiService->getECPaymentResponse ( $params ['PayerID'], $params ['token'], $order );
			if (isset ( $DoECResponse ) && $DoECResponse->Ack == 'Success') {
				if (isset ( $DoECResponse->DoExpressCheckoutPaymentResponseDetails->PaymentInfo )) {
					$payPalApiService->recordECPayments ( $DoECResponse, $params ['PayerID'], $order );
					$ordersService->updateOrderState ( $order ['orderId'], $order ['state'] );
				} else {
					throw new Exception ( sprintf ( 'No payments for express checkout order %s', $order ['orderId'] ) );
				}
			} else {
				throw new Exception ( $DoECResponse->Errors [0]->LongMessage );
			}
			
			// Current subscription
			$currentSubscription = $subscriptionsService->getUserActiveSubscription ( $userId );
			if (! empty ( $currentSubscription )) {
				// Clear profile
				$currentPaymentProfile = $ordersService->getPaymentProfileById ( $currentSubscription ['paymentProfileId'] );
				if (! empty ( $currentPaymentProfile )) {
					$payPalApiService->cancelPaymentProfile ( $currentSubscription, $currentPaymentProfile );
				}
				// Clear subscription
				$subscriptionsService->updateSubscriptionState ( $currentSubscription ['subscriptionId'], SubscriptionStatus::CANCELLED );
					
				// If the current sub tier is lower than the new sub tier, its an upgrade
				if (floatval ( $currentSubscription ['subscriptionTier'] ) < floatval ( $subscriptionType ['tier'] ))
					$isSubscriptionUpgrade = true;
			}
			
			if (! empty ( $orderSubscription )) {
				// update the subscription status
				$subscriptionsService->updateSubscriptionState ( $orderSubscription ['subscriptionId'], SubscriptionStatus::ACTIVE );
				if (! empty ( $paymentProfile )) {
					$subscriptionsService->updateSubscriptionPaymentProfile ( $orderSubscription ['subscriptionId'], $paymentProfile ['profileId'], true );
				}
			}
			
			// Handle the sub broadcast
			$this->handleNewSubscriptionBroadcast ( $userId, $subscriptionType, $isSubscriptionUpgrade );
			
			// Redirect to completion page
			return 'redirect: /subscription/' . urlencode ( $order ['orderId'] ) . '/complete';
			
		} catch ( Exception $e ) {
			
			if (! empty ( $order ))
				$ordersService->updateOrderState ( $order ['orderId'], OrderStatus::ERROR );
			if (! empty ( $paymentProfile ))
				$ordersService->updatePaymentStatus ( $paymentProfile ['paymentId'], PaymentStatus::ERROR );
			if (! empty ( $orderSubscription ))
				$subscriptionsService->updateSubscriptionState ( $orderSubscription ['subscriptionId'], SubscriptionStatus::ERROR );
			
			$log = Application::instance ()->getLogger ();
			$log->error ( $e->getMessage(), $order);
			return 'redirect: /subscription/' . urlencode ( $order ['orderId'] ) . '/error';
			
		}
	}
	
	/**
	 * @Route ("/subscription/cancel")
	 * @Secure ({"USER"})
	 * @HttpMethod ({"GET"})
	 *
	 * @param array $params        	
	 * @param ViewModel $model        	
	 * @throws Exception
	 * @return string
	 */
	public function subscriptionCancel(array $params, ViewModel $model) {
		$subscription = SubscriptionsService::instance ()->getUserActiveSubscription ( Session::getCredentials ()->getUserId () );
		if (empty ( $subscription )) {
			throw new Exception ( 'Must have an active subscription' );
		}
		$model->subscription = $subscription;
		return 'profile/cancelsubscription';
	}
	
	/**
	 * @Route ("/subscription/cancel")
	 * @Secure ({"USER"})
	 * @HttpMethod ({"POST"})
	 * @Transactional
	 *
	 * @param array $params        	
	 * @param ViewModel $model        	
	 * @throws Exception
	 * @return string
	 */
	public function subscriptionCancelProcess(array $params, ViewModel $model) {
		$ordersService = OrdersService::instance ();
		$payPalAPIService = PayPalApiService::instance ();
		$subscriptionsService = SubscriptionsService::instance();
		$authenticationService = AuthenticationService::instance();
		
		$userId = Session::getCredentials ()->getUserId ();
		$subscription = $subscriptionsService->getUserActiveSubscription ( $userId );
		if (! empty ( $subscription )) {
			
			if (! empty ( $subscription ['paymentProfileId'] )) {
				$paymentProfile = $ordersService->getPaymentProfileById ( $subscription ['paymentProfileId'] );
				if (strcasecmp ( $paymentProfile ['state'], PaymentProfileStatus::ACTIVEPROFILE ) === 0) {
					$payPalAPIService->cancelPaymentProfile ( $subscription, $paymentProfile );
				}
			}
			
			$subscription ['status'] = SubscriptionStatus::CANCELLED;
			$subscriptionsService->updateSubscriptionState ( $subscription ['subscriptionId'], $subscription ['status'] );
			$authenticationService->flagUserForUpdate ( $userId );
			
			$model->subscription = $subscription;
			$model->subscriptionCancelled = true;
			return 'profile/cancelsubscription';
		}
		return 'redirect: /profile';
	}
	
	
	/**
	 * @Route ("/subscription/{orderId}/error")
	 * @Secure ({"USER"})
	 *
	 * @param array $params
	 */
	public function subscriptionError(array $params, ViewModel $model) {
		FilterParams::isRequired($params, 'orderId');
	
		// @TODO make this more solid
		$userId = Session::getCredentials ()->getUserId ();
		$ordersService = OrdersService::instance ();
		$order = $ordersService->getOrderByIdAndUserId ( $params ['orderId'], $userId );
	
		if (empty ( $order )) {
			throw new Exception ( 'Subscription failed' );
		}
	
		$model->order = $order;
		$model->orderId = $params ['orderId'];
		return 'order/ordererror';
	}
	
	/**
	 * @Route ("/subscription/confirm")
	 *
	 * Create and send the order
	 *
	 * @param array $params
	 */
	public function subscriptionConfirm(array $params, ViewModel $model) {
		FilterParams::isRequired($params, 'subscription');
		
		$subscriptionsService = SubscriptionsService::instance ();
		
		// If there is no user, save the selection, and go to the login screen
		if (! Session::hasRole ( UserRole::USER )) {
			Session::start ( Session::START_NOCOOKIE );
			Session::set ( 'subscription', $params ['subscription'] );
			return 'redirect: /login';
		}
	
		$userId = Session::getCredentials ()->getUserId ();
	
		// Make sure the user hasnt somehow started the process with an active subscription
		$currentSubscription = $subscriptionsService->getUserActiveSubscription ( $userId );
		if (! empty ( $currentSubscription )) {
			throw new Exception ( 'Empty subscription type' );
		}
	
		$subscriptionType = $subscriptionsService->getSubscriptionType ( $params ['subscription'] );
		$model->subscriptionType = $subscriptionType;
		return 'order/orderconfirm';
	}
	
	/**
	 * @Route ("/subscription/create")
	 * @Secure ({"USER"})
	 * @Transactional
	 *
	 * Create and send the order
	 *
	 * @param array $params
	 */
	public function subscriptionCreate(array $params, ViewModel $model) {
		FilterParams::isRequired($params, 'subscription');
		
		$subscriptionsService = SubscriptionsService::instance ();
		$ordersService = OrdersService::instance ();
		$payPalApiService = PayPalApiService::instance ();
		
		$userId = Session::getCredentials ()->getUserId ();
		$subscription = $subscriptionsService->getSubscriptionType ( $params ['subscription'] );

		// Make sure the user hasnt somehow started the process with an active subscription
		$currentSubscription = $subscriptionsService->getUserActiveSubscription ( $userId );
		if (! empty ( $currentSubscription )) {
			throw new Exception ( 'User already has a valid subscription' );
		}
		
		try {
			
			// isRecurring
			$recurring = (isset ( $params ['renew'] ) && $params ['renew'] == '1');
	
			// Create NEW order
			$order = $ordersService->createSubscriptionOrder ( $subscription, $userId );
			$subscriptionId = $subscriptionsService->createSubscriptionFromOrder ( $order, $subscription );
			$paymentProfile = null;

			// Add payment profile
			if ($recurring) {
				$billingStartDate = Date::getDateTime ( date ( 'm/d/y' ) );
				$billingStartDate->modify ( '+' . $subscription ['billingFrequency'] . ' ' . strtolower ( $subscription ['billingPeriod'] ) );
				$paymentProfile = $ordersService->createPaymentProfile ( $userId, $order, $subscription, $billingStartDate );
			}
			
			$setECResponse = $payPalApiService->createECResponse ( '/subscription/process', $order, $subscription, $recurring );
			if (empty ( $setECResponse ) || $setECResponse->Ack != 'Success') {
				throw new Exception ( $setECResponse->Errors->ShortMessage );
			}
			return 'redirect: ' . Config::$a ['paypal'] ['api'] ['endpoint'] . urlencode ( $setECResponse->Token );
			
		}catch (Exception $e){

			if (! empty ( $order ))
				$ordersService->updateOrderState ( $order ['orderId'], OrderStatus::ERROR );
			if (! empty ( $paymentProfile ))
				$ordersService->updatePaymentStatus ( $paymentProfile ['paymentId'], PaymentStatus::ERROR );
			if (! empty ( $subscriptionId ))
				$subscriptionsService->updateSubscriptionState ( $subscriptionId, SubscriptionStatus::ERROR );

			$log = Application::instance ()->getLogger ();
			$log->error ( $e->getMessage(), $order );
			return 'redirect: /subscription/' . urlencode ( $order ['orderId'] ) . '/error';
		}
	}
	
	/**
	 * @Route ("/subscription/{orderId}/complete")
	 * @Secure ({"USER"})
	 * @Transactional
	 *
	 * @param array $params
	 */
	public function subscriptionComplete(array $params, ViewModel $model) {
		FilterParams::isRequired($params, 'orderId');
		
		$ordersService = OrdersService::instance ();
		$subscriptionsService = SubscriptionsService::instance ();
	
		$userId = Session::getCredentials ()->getUserId ();
	
		$order = $ordersService->getOrderByIdAndUserId ( $params ['orderId'], $userId );
		if (empty ( $order )) {
			throw new Exception ( sprintf ( 'Invalid order record orderId:%s userId:%s', $params ['orderId'], $userId ) );
		}
	
		$subscription = $subscriptionsService->getSubscriptionByOrderIdAndUserId ( $order ['orderId'], $userId );
		if (empty ( $subscription )) {
			throw new Exception ( 'Invalid subscription record' );
		}
		
		$subscriptionType = $subscriptionsService->getSubscriptionType ( $subscription ['subscriptionType'] );
		$paymentProfile = $ordersService->getPaymentProfileByOrderId ( $order ['orderId'] );
	
		// Show the order complete screen
		$model->order = $order;
		$model->subscription = $subscription;
		$model->subscriptionType = $subscriptionType;
		$model->paymentProfile = $paymentProfile;
		return 'order/ordercomplete';
	}
	
	/**
	 * @Route ("/subscription/process")
	 * @Secure ({"USER"})
	 * @Transactional
	 *
	 * We were redirected here from PayPal after the buyer approved/cancelled the payment
	 *
	 * @param array $params
	 */
	public function subscriptionProcess(array $params, ViewModel $model) {

		FilterParams::isRequired ( $params, 'orderId' );
		FilterParams::isRequired ( $params, 'token' );
		FilterParams::isThere ( $params, 'success' );
			
		$ordersService = OrdersService::instance ();
		$subscriptionsService = SubscriptionsService::instance ();
		$payPalApiService = PayPalApiService::instance ();
		$chat = ChatIntegrationService::instance ();
		$userService = UserService::instance ();
		$authService = AuthenticationService::instance ();
		
		$userId = Session::getCredentials ()->getUserId ();
		
		$order = $ordersService->getOrderByIdAndUserId ( $params ['orderId'], $userId );
		if (empty ( $order ) || strcasecmp($order ['state'], OrderStatus::_NEW) !== 0) {
			throw new Exception ( 'Invalid order record' );
		}
			
		try {
			// If we got a failed response URL
			if ($params ['success'] == '0' || $params ['success'] == 'false' || $params ['success'] === false) {
				throw new Exception ( 'Order request failed' );
			}
			
			$orderSubscription = $subscriptionsService->getSubscriptionByOrderIdAndUserId ( $order ['orderId'], $userId );
			if (empty ( $orderSubscription )) {
				throw new Exception ( 'Invalid order subscription' );
			}
			
			$subscriptionType = $subscriptionsService->getSubscriptionType ( $orderSubscription ['subscriptionType'] );
			$paymentProfile = $ordersService->getPaymentProfileByOrderId ( $order ['orderId'] );
			
			// Get the checkout info
			$ecResponse = $payPalApiService->retrieveCheckoutInfo ( $params ['token'] );
			if (! isset ( $ecResponse ) || $ecResponse->Ack != 'Success') {
				throw new Exception ( 'Failed to retrieve express checkout details' );
			}
			
			// Moved this down here, as if the order status is error, the payerID is not returned
			FilterParams::isRequired ( $params, 'PayerID' );
			
			// Point of no return - we only every want a person to get here if their order was a successful sequence
			Session::set ( 'token' );
			Session::set ( 'orderId' );
			
			// RECURRING PAYMENT
			if (! empty ( $paymentProfile )) {
				$createRPProfileResponse = $payPalApiService->createRecurringPaymentProfile ( $paymentProfile, $params ['token'], $subscriptionType );
				if (! isset ( $createRPProfileResponse ) || $createRPProfileResponse->Ack != 'Success') {
					throw new Exception ( 'Failed to create recurring payment request' );
				}
				$paymentProfileId = $createRPProfileResponse->CreateRecurringPaymentsProfileResponseDetails->ProfileID;
				$paymentStatus = $createRPProfileResponse->CreateRecurringPaymentsProfileResponseDetails->ProfileStatus;
				if (empty ( $paymentProfileId )) {
					throw new Exception ( 'Invalid recurring payment profileId returned from Paypal' );
				}
				// Set the payment profile to active, and paymetProfileId
				$ordersService->updatePaymentProfileId ( $paymentProfile ['profileId'], $paymentProfileId, $paymentStatus );
			}
			
			// Complete the checkout
			$DoECResponse = $payPalApiService->getECPaymentResponse ( $params ['PayerID'], $params ['token'], $order );
			if (isset ( $DoECResponse ) && $DoECResponse->Ack == 'Success') {
				if (isset ( $DoECResponse->DoExpressCheckoutPaymentResponseDetails->PaymentInfo )) {
					$payPalApiService->recordECPayments ( $DoECResponse, $params ['PayerID'], $order );
					$ordersService->updateOrderState ( $order ['orderId'], $order ['state'] );
				} else {
					throw new Exception ( 'No payments for express checkout order' );
				}
			} else {
				throw new Exception ( $DoECResponse->Errors [0]->LongMessage );
			}
			
			if (! empty ( $orderSubscription )) {
				$subscriptionsService->updateSubscriptionState ( $orderSubscription ['subscriptionId'], SubscriptionStatus::ACTIVE );
				if (! empty ( $paymentProfile )) {
					// assume this is recurring?
					$subscriptionsService->updateSubscriptionPaymentProfile ( $orderSubscription ['subscriptionId'], $paymentProfile ['profileId'], true );
				}
			}
		
			// Handle the sub broadcast
			$this->handleNewSubscriptionBroadcast ( $userId, $subscriptionType, true );
		
			// Redirect to completion page
			return 'redirect: /subscription/' . urlencode ( $order ['orderId'] ) . '/complete';
			
		}catch (Exception $e){

			if (! empty ( $order ))
				$ordersService->updateOrderState ( $order ['orderId'], OrderStatus::ERROR );
			if (! empty ( $paymentProfile ))
				$ordersService->updatePaymentStatus ( $paymentProfile ['paymentId'], PaymentStatus::ERROR );
			if (! empty ( $orderSubscription ))
				$subscriptionsService->updateSubscriptionState ( $orderSubscription['subscriptionId'], SubscriptionStatus::ERROR );

			$log = Application::instance ()->getLogger ();
			$log->error ( $e->getMessage(), $order );
			return 'redirect: /subscription/' . urlencode ( $order ['orderId'] ) . '/error';
		}
	}
	
	/**
	 * Simple handler for when new a sub is made
	 * 
	 * @param number $userId
	 * @param array $subscription
	 * @param string $unban
	 */
	private function handleNewSubscriptionBroadcast($userId, array $subscriptionType, $unban = true) {
		$userService = UserService::instance ();
		$chatIntegrationService = ChatIntegrationService::instance ();
		$authenticationService = AuthenticationService::instance ();
		$user = $userService->getUserById ( $userId );
		if (! empty ( $user )) {
			if ($unban) {
				$ban = $userService->getUserActiveBan ( $userId );
				// only unban the user if the ban is non-permanent
				// we unban the user if no ban is found because it also unmutes
				if (empty ( $ban ) || $ban ['endtimestamp']) {
					$chatIntegrationService->sendUnban ( $userId );
				}
			}
			// Update the user
			$authenticationService->flagUserForUpdate ( $userId );
			// Broadcast the subscription
			$chatIntegrationService->sendBroadcast ( sprintf ( "%s has just become a %s subscriber! FeedNathan", $user ['username'], $subscriptionType ['tierLabel'] ) );
		}
	}
	
}