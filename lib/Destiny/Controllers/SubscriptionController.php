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

/**
 * @Controller
 */
class SubscriptionController {
	
	/**
	 * @Route ("/subscription/update/confirm")
	 * @Secure ({"USER"})
	 *
	 * @param array $params        	
	 */
	public function subscriptionUpdateConfirm(array $params, ViewModel $model) {
		$subService = SubscriptionsService::instance ();
		$currentSubscription = $subService->getUserActiveSubscription ( Session::getCredentials ()->getUserId () );
		$log = Application::instance ()->getLogger ();
		if (! isset ( $params ['subscription'] ) || empty ( $params ['subscription'] )) {
			throw new Exception ( 'Empty subscription type' );
		}
		if (empty ( $currentSubscription )) {
			throw new Exception ( 'Subscription required' );
		}
		$currentSubscriptionType = Config::$a ['commerce'] ['subscriptions'] [$currentSubscription ['subscriptionType']];
		$subscription = SubscriptionsService::instance ()->getSubscriptionType ( $params ['subscription'] );
		$model->currentSubscription = $currentSubscription;
		$model->currentSubscriptionType = $currentSubscriptionType;
		$model->subscription = $subscription;
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
		$subService = SubscriptionsService::instance ();
		$orderService = OrdersService::instance ();
		$payPalApiService = PayPalApiService::instance ();
		$userId = Session::getCredentials ()->getUserId ();
		$log = Application::instance ()->getLogger ();

		if (! isset ( $params ['subscription'] ) || empty ( $params ['subscription'] )) {
			throw new Exception ( 'Empty subscription type' );
		}
		
		$currentSubscription = $subService->getUserActiveSubscription ( $userId );
		if (empty ( $currentSubscription )) {
			throw new Exception ( 'Subscription required' );
		}
		$currentSubscriptionType = Config::$a ['commerce'] ['subscriptions'] [$currentSubscription ['subscriptionType']];
		$subscription = $subService->getSubscriptionType ( $params ['subscription'] );
		
		// Change in payment plan (When you upgrade or downgrade the same sub type)
		if ($currentSubscriptionType ['id'] == $subscription ['id']) {
			if ($currentSubscription ['recurring'] == 0) {
				// create profile
				$billingStartDate = Date::getDateTime ( $currentSubscription ['endDate'] );
				$order = $orderService->createSubscriptionOrder ( $subscription, $userId );
				$paymentProfile = $orderService->createPaymentProfile ( $userId, $order, $subscription, $billingStartDate );
				$setECResponse = $payPalApiService->getNoPaymentECResponse ( '/subscription/update/process', $order, $subscription, $paymentProfile );
				if (empty ( $setECResponse ) || $setECResponse->Ack != 'Success') {
					throw new Exception ( 'Failed to create payment profile' );
				}
				return 'redirect: ' . Config::$a ['paypal'] ['api'] ['endpoint'] . urlencode ( $setECResponse->Token );
				//
			} else {
				// clear profile
				$paymentProfile = $orderService->getPaymentProfileById ( $currentSubscription ['paymentProfileId'] );
				if (! empty ( $paymentProfile )) {
					$payPalApiService->cancelPaymentProfile ( $currentSubscription, $paymentProfile );
				}
				$subscription ['amount'] = 0;
				$order = $orderService->createSubscriptionOrder ( $subscription, $userId );
				return 'redirect: /order/' . urlencode ( $order ['orderId'] ) . '/complete'; // @TODO FIX
			}
		}
		
		// create profile & subscription
		$order = $orderService->createSubscriptionOrder ( $subscription, $userId );
		if (isset ( $params ['renew'] ) && $params ['renew'] == '1') {
			$billingStartDate = Date::getDateTime ( date ( 'm/d/y' ) );
			$billingStartDate->modify ( '+' . $subscription ['billingFrequency'] . ' ' . strtolower ( $subscription ['billingPeriod'] ) );
			$paymentProfile = $orderService->createPaymentProfile ( Session::getCredentials ()->getUserId (), $order, $subscription, $billingStartDate );
			$setECResponse = $payPalApiService->createECResponse ( '/subscription/update/process', $order, $subscription, $paymentProfile );
		} else {
			$setECResponse = $payPalApiService->createECResponse ( '/subscription/update/process', $order, $subscription );
		}
		if (isset ( $setECResponse ) && $setECResponse->Ack == 'Success') {
			return 'redirect: ' . Config::$a ['paypal'] ['api'] ['endpoint'] . urlencode ( $setECResponse->Token );
		}
		
		// Error
		$orderService->updateOrderState ( $order ['orderId'], OrderStatus::ERROR );
		$log->error ( $setECResponse->Errors->ShortMessage, $order );
		return 'redirect: /order/' . urlencode ( $order ['orderId'] ) . '/error';
	}
	
	/**
	 * @Route ("/subscription/update/process")
	 * @Secure ({"USER"})
	 *
	 * We were redirected here from PayPal after the buyer approved/cancelled the payment
	 * 
	 * @param array $params        	
	 */
	public function subscriptionUpdateProcess(array $params, ViewModel $model) {
		$ordersService = OrdersService::instance ();
		$subService = SubscriptionsService::instance ();
		$payPalApiService = PayPalApiService::instance ();
		$log = Application::instance ()->getLogger ();
		
		if (! isset ( $params ['orderId'] ) || empty ( $params ['orderId'] )) {
			throw new Exception ( 'Require orderId' );
		}
		if (! isset ( $params ['token'] ) || empty ( $params ['token'] )) {
			throw new Exception ( 'Invalid token' );
		}
		if (! isset ( $params ['success'] )) {
			throw new Exception ( 'Invalid success response' );
		}
		
		$order = $ordersService->getOrderById ( $params ['orderId'] );
		if (empty ( $order )) {
			throw new Exception ( 'Invalid order record' );
		}
		
		// @TODO this should be done better
		if ($order ['userId'] != Session::getCredentials ()->getUserId ()) {
			throw new Exception ( 'Invalid order access' );
		}
		
		if ($order ['state'] != OrderStatus::_NEW) {
			$log->error ( $setECResponse->Errors->ShortMessage, $order );
			return 'redirect: /order/' . urlencode ( $order ['orderId'] ) . '/error';
		}
		
		$order ['items'] = $ordersService->getOrderItems ( $order ['orderId'] );
		$subscription = $subService->getSubscriptionType ( $order ['items'] [0] ['itemSku'] ); // get the subscription off the itemSku - wierd
		$paymentProfile = $ordersService->getPaymentProfileByOrderId ( $order ['orderId'] );
		
		// If we got a failed response URL
		if ($params ['success'] == '0' || $params ['success'] == 'false' || $params ['success'] === false) {
			$ordersService->updateOrderState ( $order ['orderId'], OrderStatus::ERROR );
			// Also set the profile state to error
			if (! empty ( $paymentProfile )) {
				$ordersService->updatePaymentProfileState ( $paymentProfile ['profileId'], PaymentProfileStatus::ERROR );
			}
			$log->error ( 'Order response failed', $order );
			return 'redirect: /order/' . urlencode ( $order ['orderId'] ) . '/error';
		}
		
		// The token from paypal
		$token = $params ['token'];
		
		// Get the checkout info
		$ecResponse = $payPalApiService->retrieveCheckoutInfo ( $token );
		if (! isset ( $ecResponse ) || $ecResponse->Ack != 'Success') {
			$log->error ( 'Failed to retrieve express checkout details', $order );
			return 'redirect: /order/' . urlencode ( $order ['orderId'] ) . '/error';
		}
		
		if ($params ['success'] == 'true' || $params ['success'] === true) {
			if (! empty ( $paymentProfile )) {
				$createRPProfileResponse = $payPalApiService->createRecurringPaymentProfile ( $paymentProfile, $token, $subscription );
				if (! isset ( $createRPProfileResponse ) || $createRPProfileResponse->Ack != 'Success') {
					$log->error ( 'Failed to create recurring payment request', $order );
					return 'redirect: /order/' . urlencode ( $order ['orderId'] ) . '/error';
				}
				$paymentProfileId = $createRPProfileResponse->CreateRecurringPaymentsProfileResponseDetails->ProfileID;
				$paymentStatus = $createRPProfileResponse->CreateRecurringPaymentsProfileResponseDetails->ProfileStatus;
				if (empty ( $paymentProfileId )) {
					$log->error ( 'Invalid recurring payment profileId returned from Paypal', $order );
					return 'redirect: /order/' . urlencode ( $order ['orderId'] ) . '/error';
				}
				// Set the payment profile to active, and paymetProfileId
				$ordersService->updatePaymentProfileId ( $paymentProfile ['profileId'], $paymentProfileId, $paymentStatus );
			}
		}
		
		// Complete the checkout
		$DoECResponse = $payPalApiService->getECPaymentResponse ( $params ['PayerID'], $token, $order );
		if (isset ( $DoECResponse ) && $DoECResponse->Ack == 'Success') {
			if (isset ( $DoECResponse->DoExpressCheckoutPaymentResponseDetails->PaymentInfo )) {
				$payPalApiService->recordECPayments ( $DoECResponse, $params ['PayerID'], $order );
				$ordersService->updateOrderState ( $order ['orderId'], $order ['state'] );
			} else {
				$ordersService->updateOrderState ( $order ['orderId'], OrderStatus::ERROR );
				$log->error ( sprintf ( 'No payments for express checkout order %s', $order ['orderId'] ), $order );
				return 'redirect: /order/' . urlencode ( $order ['orderId'] ) . '/error';
			}
		} else {
			$ordersService->updateOrderState ( $order ['orderId'], OrderStatus::ERROR );
			$log->error ( $DoECResponse->Errors [0]->LongMessage, $order );
			return 'redirect: /order/' . urlencode ( $order ['orderId'] ) . '/error';
		}
		
		// Current subscription
		$currentSubscription = $subService->getUserActiveSubscription ( Session::getCredentials ()->getUserId () );
		if (! empty ( $currentSubscription )) {
			// Clear profile
			$currentPaymentProfile = $ordersService->getPaymentProfileById ( $currentSubscription ['paymentProfileId'] );
			if (! empty ( $currentPaymentProfile )) {
				$payPalApiService->cancelPaymentProfile ( $currentSubscription, $currentPaymentProfile );
			}
			// Clear subscription
			$subService->updateSubscriptionState ( $currentSubscription ['subscriptionId'], SubscriptionStatus::CANCELLED );
		}
		
		// Create new subscription
		$subService->createSubscriptionFromOrder ( $order, $subscription, $paymentProfile );
		
		// Update the user
		AuthenticationService::instance ()->flagUserForUpdate ( $order ['userId'] );

		$chat = ChatIntegrationService::instance ();
		$message = sprintf ( "%s has just become a %s subscriber! FeedNathan", Session::getCredentials ()->getUsername (), $subscription['tierLabel'] );
		$chat->sendBroadcast ( $message );

		$ban = UserService::getUserActiveBan( $order['userId'] );
		// only unban the user if the ban is non-permanent
		// we unban the user if no ban is found because it also unmutes
		if ( empty( $ban ) or $ban['endtimestamp'] )
			$chat->sendUnban ( $order['userId'] );

		return 'redirect: /order/' . urlencode ( $order ['orderId'] ) . '/complete';
	}
	
	/**
	 * @Route ("/subscribe")
	 *
	 * Build subscribe checkout form
	 *
	 * @param array $params        	
	 */
	public function subscribe(array $params, ViewModel $model) {
		$subService = SubscriptionsService::instance ();
		$subscription = $subService->getUserPendingSubscription ( Session::getCredentials ()->getUserId () );
		if (! empty ( $subscription )) {
			throw new Exception ( 'You already have a subscription in the "pending" state. Please cancel this first.' );
		}
		$subscription = $subService->getUserActiveSubscription ( Session::getCredentials ()->getUserId () );
		$formAction = '/order/confirm';
		if (! empty ( $subscription )) {
			$formAction = '/subscription/update/confirm';
		}
		$model->title = 'Subscribe';
		$model->subscriptions = Config::$a ['commerce'] ['subscriptions'];
		$model->formAction = $formAction;
		return 'subscribe';
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
		$orderService = OrdersService::instance ();
		$payPalAPIService = PayPalApiService::instance ();
		$userId = Session::getCredentials ()->getUserId ();
		$subscription = SubscriptionsService::instance ()->getUserActiveSubscription ( $userId );
		if (! empty ( $subscription )) {
			
			if (! empty ( $subscription ['paymentProfileId'] )) {
				$paymentProfile = $orderService->getPaymentProfileById ( $subscription ['paymentProfileId'] );
				if (strcasecmp ( $paymentProfile ['state'], PaymentProfileStatus::ACTIVEPROFILE ) === 0) {
					$payPalAPIService->cancelPaymentProfile ( $subscription, $paymentProfile );
				}
			}
			
			$subscription ['status'] = SubscriptionStatus::CANCELLED;
			SubscriptionsService::instance ()->updateSubscriptionState ( $subscription ['subscriptionId'], $subscription ['status'] );
			AuthenticationService::instance ()->flagUserForUpdate ( $userId );
			
			$model->subscription = $subscription;
			$model->subscriptionCancelled = true;
			return 'profile/cancelsubscription';
		}
		return 'redirect: /profile';
	}
}