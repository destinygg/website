<?php

namespace Destiny\Controllers;

use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\Transactional;
use Destiny\Common\Annotation\Secure;
use Destiny\Commerce\OrdersService;
use Destiny\Commerce\SubscriptionsService;
use Destiny\Commerce\PayPalApiService;
use Destiny\Common\ViewModel;
use Destiny\Common\Application;
use Destiny\Common\Exception;
use Destiny\Common\Session;
use Destiny\Commerce\OrderStatus;
use Destiny\Commerce\PaymentProfileStatus;
use Destiny\Common\Authentication\AuthenticationService;
use Destiny\Common\Utils\Date;
use Destiny\Common\Config;
use Destiny\Common\User\UserRole;

/**
 * @Controller
 */
class OrderController {
	
	/**
	 * @Route ("/order/{orderId}/error")
	 * @Secure ({"USER"})
	 *
	 * @param array $params        	
	 */
	public function orderError(array $params, ViewModel $model) {
		if (! isset ( $params ['orderId'] ) || empty ( $params ['orderId'] )) {
			throw new Exception ( 'Invalid order' );
		}

		// @TODO make this more solid
		$userId = Session::getCredentials ()->getUserId ();
		$ordersService = OrdersService::instance ();
		$order = $ordersService->getOrderByIdAndUserId ( $params ['orderId'], $userId );
		
		if (empty ( $order )) {
			throw new Exception ( 'Order failed' );
		}
		
		$model->order = $order;
		$model->orderId = $params ['orderId'];
		return 'order/ordererror';
	}
	
	/**
	 * @Route ("/order/confirm")
	 *
	 * Create and send the order
	 *
	 * @param array $params        	
	 */
	public function orderConfirm(array $params, ViewModel $model) {
		$subService = SubscriptionsService::instance ();
		
		if (! isset ( $params ['subscription'] ) || empty ( $params ['subscription'] )) {
			throw new Exception ( 'Empty subscription type' );
		}
		
		// If there is no user, save the selection, and go to the login screen
		if (! Session::hasRole ( UserRole::USER )) {
			Session::start ( Session::START_NOCOOKIE );
			Session::set ( 'subscription', $params ['subscription'] );
			return 'redirect: /login';
		}
		
		// @TODO make this more solid
		$userId = Session::getCredentials ()->getUserId ();
		
		// Make sure the user hasnt somehow started the process with an active subscription
		$currentSubscription = $subService->getUserActiveSubscription ( $userId );
		if (! empty ( $currentSubscription )) {
			throw new Exception ( 'Empty subscription type' );
		}
		
		$subscription = $subService->getSubscriptionType ( $params ['subscription'] );
		$model->subscription = $subscription;
		return 'order/orderconfirm';
	}
	
	/**
	 * @Route ("/order/create")
	 * @Secure ({"USER"})
	 * @Transactional
	 *
	 * Create and send the order
	 *
	 * @param array $params        	
	 */
	public function orderCreate(array $params, ViewModel $model) {
		$subService = SubscriptionsService::instance ();
		$ordersService = OrdersService::instance ();
		$payPalApiService = PayPalApiService::instance ();
		$log = Application::instance ()->getLogger ();
		
		// @TODO make this more solid
		$userId = Session::getCredentials ()->getUserId ();
		
		// Make sure the user hasnt somehow started the process with an active subscription
		$currentSubscription = $subService->getUserActiveSubscription ( $userId );
		if (! empty ( $currentSubscription )) {
			throw new Exception ( 'User already has a valid subscription' );
		}
		
		if (! isset ( $params ['subscription'] ) || empty ( $params ['subscription'] )) {
			throw new Exception ( 'Invalid subscription type' );
		}
		
		$subscription = $subService->getSubscriptionType ( $params ['subscription'] );
		$order = $ordersService->createSubscriptionOrder ( $subscription, $userId );
		
		if (isset ( $params ['renew'] ) && $params ['renew'] == '1') {
			$billingStartDate = Date::getDateTime ( date ( 'm/d/y' ) );
			$billingStartDate->modify ( '+' . $subscription ['billingFrequency'] . ' ' . strtolower ( $subscription ['billingPeriod'] ) );
			$paymentProfile = $ordersService->createPaymentProfile ( $userId, $order, $subscription, $billingStartDate );
			$setECResponse = $payPalApiService->createECResponse ( '/order/process', $order, $subscription, $paymentProfile );
		} else {
			$setECResponse = $payPalApiService->createECResponse ( '/order/process', $order, $subscription );
		}
		if (isset ( $setECResponse ) && $setECResponse->Ack == 'Success') {
			return 'redirect: ' . Config::$a ['paypal'] ['api'] ['endpoint'] . urlencode ( $setECResponse->Token );
		}
		
		// Error
		$ordersService->updateOrderState ( $order ['orderId'], 'Error' );
		$log->error ( $setECResponse->Errors->ShortMessage, $order );
		return 'redirect: /order/' . urlencode ( $order ['orderId'] ) . '/error';
	}
	
	/**
	 * @Route ("/order/{orderId}/complete")
	 * @Secure ({"USER"})
	 * @Transactional
	 *
	 * @param array $params        	
	 */
	public function orderComplete(array $params, ViewModel $model) {
		$ordersService = OrdersService::instance ();
		$subService = SubscriptionsService::instance ();
		$log = Application::instance ()->getLogger ();
		
		if (! isset ( $params ['orderId'] ) || empty ( $params ['orderId'] )) {
			throw new Exception ( 'Require orderId' );
		}
		
		$userId = Session::getCredentials ()->getUserId ();
		
		$order = $ordersService->getOrderByIdAndUserId ( $params ['orderId'], $userId );
		if (empty ( $order )) {
			throw new Exception ( sprintf ( 'Invalid order record orderId:%s userId:%s', $params ['orderId'], $userId ) );
		}
		
		$order ['items'] = $ordersService->getOrderItems ( $order ['orderId'] );
		$subscriptionType = $subService->getSubscriptionType ( $order ['items'] [0] ['itemSku'] ); // get the subscription off the itemSku - wierd
		$subscription = $subService->getUserActiveSubscription ( $userId );
		$paymentProfile = $ordersService->getPaymentProfileByOrderId ( $order ['orderId'] );
		
		// Show the order complete screen
		$model->order = $order;
		$model->subscription = $subscription;
		$model->subscriptionType = $subscriptionType;
		$model->paymentProfile = $paymentProfile;
		return 'order/ordercomplete';
	}
	
	/**
	 * @Route ("/order/process")
	 * @Secure ({"USER"})
	 * @Transactional
	 *
	 * We were redirected here from PayPal after the buyer approved/cancelled the payment
	 *
	 * @param array $params        	
	 */
	public function orderProcess(array $params, ViewModel $model) {
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
		
		// The token from paypal
		$token = $params ['token'];
		$order = $ordersService->getOrderById ( $params ['orderId'] );
		if (empty ( $order )) {
			throw new Exception ( 'Invalid order record' );
		}
		
		// @TODO this should be done better
		if ($order ['userId'] != Session::getCredentials ()->getUserId ()) {
			throw new Exception ( 'Invalid order access' );
		}
		// @TODO there should be more logic to handle different types of status'es
		if ($order ['state'] != OrderStatus::_NEW) {
			$log->error ( 'Invalid order status', $order );
			return 'redirect: /order/' . urlencode ( $order ['orderId'] ) . '/error';
		}
		
		$order ['items'] = $ordersService->getOrderItems ( $order ['orderId'] );
		$subscription = $subService->getSubscriptionType ( $order ['items'] [0] ['itemSku'] ); // get the subscription off the itemSku - wierd
		$paymentProfile = $ordersService->getPaymentProfileByOrderId ( $order ['orderId'] );
		
		if(empty($paymentProfile)){
			$paymentProfile = null;
		}
		
		// If we got a failed response URL
		if ($params ['success'] == '0' || $params ['success'] == 'false' || $params ['success'] === false) {
			$ordersService->updateOrderState ( $order ['orderId'], OrderStatus::ERROR );
			// Also set the profile state to error
			if (! empty ( $paymentProfile )) {
				$ordersService->updatePaymentProfileState ( $paymentProfile ['profileId'], PaymentProfileStatus::ERROR );
			}
			$log->error ( 'Order request failed', $order );
			return 'redirect: /order/' . urlencode ( $order ['orderId'] ) . '/error';
		}
		
		// Get the checkout info
		$ecResponse = $payPalApiService->retrieveCheckoutInfo ( $token );
		if (! isset ( $ecResponse ) || $ecResponse->Ack != 'Success') {
			$ordersService->updateOrderState ( $order ['orderId'], OrderStatus::ERROR );
			$log->error ( 'Failed to retrieve express checkout details', $order );
			return 'redirect: /order/' . urlencode ( $order ['orderId'] ) . '/error';
		}
		
		// Is done after the success check
		if (! isset ( $params ['PayerID'] ) || empty ( $params ['PayerID'] )) {
			$model->error = new Exception ( 'Invalid PayerID' );
			$log->error ( 'Invalid PayerID', $order );
			return 'redirect: /order/' . urlencode ( $order ['orderId'] ) . '/error';
		}
		
		// Point of no return - we only every want a person to get here if their order was a successful sequence
		Session::set ( 'token' );
		Session::set ( 'orderId' );
		
		// RECURRING PAYMENT
		if (! empty ( $paymentProfile )) {
			$createRPProfileResponse = $payPalApiService->createRecurringPaymentProfile ( $paymentProfile, $token, $subscription );
			if (! isset ( $createRPProfileResponse ) || $createRPProfileResponse->Ack != 'Success') {
				$ordersService->updateOrderState ( $order ['orderId'], OrderStatus::ERROR );
				$log->error ( 'Failed to create recurring payment request', $order );
				return 'redirect: /order/' . urlencode ( $order ['orderId'] ) . '/error';
			}
			$paymentProfileId = $createRPProfileResponse->CreateRecurringPaymentsProfileResponseDetails->ProfileID;
			$paymentStatus = $createRPProfileResponse->CreateRecurringPaymentsProfileResponseDetails->ProfileStatus;
			if (empty ( $paymentProfileId )) {
				$ordersService->updateOrderState ( $order ['orderId'], OrderStatus::ERROR );
				$log->error ( 'Invalid recurring payment profileId returned from Paypal', $order );
				return 'redirect: /order/' . urlencode ( $order ['orderId'] ) . '/error';
			}
			// Set the payment profile to active, and paymetProfileId
			$ordersService->updatePaymentProfileId ( $paymentProfile ['profileId'], $paymentProfileId, $paymentStatus );
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
		
		// Create new subscription
		$subService->createSubscriptionFromOrder ( $order, $subscription, $paymentProfile );
		
		// Update the user
		AuthenticationService::instance ()->flagUserForUpdate ( $order ['userId'] );
		
		// Redirect to completion page
		return 'redirect: /order/' . urlencode ( $order ['orderId'] ) . '/complete';
	}
}
