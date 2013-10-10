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
	 * @Route ("/order/confirm")
	 *
	 * Create and send the order
	 *
	 * @param array $params        	
	 */
	public function orderConfirm(array $params, ViewModel $model) {
		$subService = SubscriptionsService::instance ();
		
		// @TODO make this more solid
		$userId = Session::getCredentials ()->getUserId ();
		
		// Make sure the user hasnt somehow started the process with an active subscription
		$currentSubscription = $subService->getUserActiveSubscription ( $userId );
		if (! empty ( $currentSubscription )) {
			$model->error = new Exception ( 'User already has a valid subscription' );
			return 'order/ordererror';
		}
		
		if (! isset ( $params ['subscription'] ) || empty ( $params ['subscription'] )) {
			$model->error = new Exception ( 'Empty subscription type' );
			return 'order/ordererror';
		}
		
		// If there is no user, save the selection, and go to the login screen
		if (! Session::hasRole ( UserRole::USER )) {
			Session::start ( Session::START_NOCOOKIE );
			Session::set ( 'subscription', $params ['subscription'] );
			return 'redirect: /login';
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
			$model->error = new Exception ( 'User already has a valid subscription' );
			return 'order/ordererror';
		}
		
		if (! isset ( $params ['subscription'] ) || empty ( $params ['subscription'] )) {
			$model->error = new Exception ( 'Invalid subscription type' );
			return 'order/ordererror';
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
		$model->error = new Exception ( sprintf ( 'A order error has occurred. The order reference is: %s', $order ['orderId'] ) );
		$log->error ( $setECResponse->Errors->ShortMessage );
		return 'order/ordererror';
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
			$model->error = new Exception ( 'Require orderId' );
			return 'order/ordererror';
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
			$model->error = new Exception ( 'Require orderId' );
			return 'order/ordererror';
		}
		if (! isset ( $params ['token'] ) || empty ( $params ['token'] )) {
			$model->error = new Exception ( 'Invalid token' );
			return 'order/ordererror';
		}
		if (! isset ( $params ['success'] )) {
			$model->error = new Exception ( 'Invalid success response' );
			return 'order/ordererror';
		}
		
		// The token from paypal
		$token = $params ['token'];
		$order = $ordersService->getOrderById ( $params ['orderId'] );
		if (empty ( $order )) {
			$model->error = new Exception ( 'Invalid order record' );
			return 'order/ordererror';
		}
		
		// @TODO this should be done better
		if ($order ['userId'] != Session::getCredentials ()->getUserId ()) {
			$model->error = new Exception ( 'Invalid order access' );
			return 'order/ordererror';
		}
		if ($order ['state'] != 'New') {
			$model->error = new Exception ( 'Invalid order status' );
			return 'order/ordererror';
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
			$model->error = new Exception ( 'Order request failed' );
			return 'order/ordererror';
		}
		
		// Get the checkout info
		$ecResponse = $payPalApiService->retrieveCheckoutInfo ( $token );
		if (! isset ( $ecResponse ) || $ecResponse->Ack != 'Success') {
			$ordersService->updateOrderState ( $order ['orderId'], OrderStatus::ERROR );
			$model->error = new Exception ( 'Failed to retrieve express checkout details' );
			return 'order/ordererror';
		}
		
		// Is done after the success check
		if (! isset ( $params ['PayerID'] ) || empty ( $params ['PayerID'] )) {
			$model->error = new Exception ( 'Invalid PayerID' );
			return 'order/ordererror';
		}
		
		// Point of no return - we only every want a person to get here if their order was a successful sequence
		Session::set ( 'token' );
		Session::set ( 'orderId' );
		
		// RECURRING PAYMENT
		if (! empty ( $paymentProfile )) {
			$createRPProfileResponse = $payPalApiService->createRecurringPaymentProfile ( $paymentProfile, $token, $subscription );
			if (! isset ( $createRPProfileResponse ) || $createRPProfileResponse->Ack != 'Success') {
				$ordersService->updateOrderState ( $order ['orderId'], OrderStatus::ERROR );
				$model->error = new Exception ( 'Failed to create recurring payment request' );
				return 'order/ordererror';
			}
			$paymentProfileId = $createRPProfileResponse->CreateRecurringPaymentsProfileResponseDetails->ProfileID;
			$paymentStatus = $createRPProfileResponse->CreateRecurringPaymentsProfileResponseDetails->ProfileStatus;
			if (empty ( $paymentProfileId )) {
				$ordersService->updateOrderState ( $order ['orderId'], OrderStatus::ERROR );
				$model->error = new Exception ( 'Invalid recurring payment profileId returned from Paypal' );
				return 'order/ordererror';
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
				$model->error = new Exception ( sprintf ( 'No payments for express checkout order %s', $order ['orderId'] ) );
				return 'order/ordererror';
			}
		} else {
			$ordersService->updateOrderState ( $order ['orderId'], OrderStatus::ERROR );
			$model->error = new Exception ( 'Unable to retrieve response from Paypal' );
			$log->error ( $DoECResponse->Errors [0]->LongMessage );
			return 'order/ordererror';
		}
		
		// Create new subscription
		$subService->createSubscriptionFromOrder ( $order, $subscription, $paymentProfile );
		
		// Update the user
		AuthenticationService::instance ()->flagUserForUpdate ( $order ['userId'] );
		
		// Redirect to completion page
		return 'redirect: /order/' . urlencode ( $order ['orderId'] ) . '/complete';
	}
}
