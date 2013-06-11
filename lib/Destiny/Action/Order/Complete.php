<?php

namespace Destiny\Action\Order;

use PayPal\EBLBaseComponents\PaymentRequestInfoType;
use PayPal\CoreComponentTypes\BasicAmountType;
use PayPal\EBLBaseComponents\AddressType;
use PayPal\EBLBaseComponents\ActivationDetailsType;
use PayPal\EBLBaseComponents\BillingPeriodDetailsType;
use PayPal\EBLBaseComponents\CreateRecurringPaymentsProfileRequestDetailsType;
use PayPal\EBLBaseComponents\CreditCardDetailsType;
use PayPal\EBLBaseComponents\RecurringPaymentsProfileDetailsType;
use PayPal\EBLBaseComponents\ScheduleDetailsType;
use PayPal\PayPalAPI\CreateRecurringPaymentsProfileReq;
use PayPal\PayPalAPI\CreateRecurringPaymentsProfileRequestType;
use PayPal\PayPalAPI\GetExpressCheckoutDetailsReq;
use PayPal\PayPalAPI\GetExpressCheckoutDetailsRequestType;
use PayPal\Service\PayPalAPIInterfaceServiceService;
use PayPal\EBLBaseComponents\DoExpressCheckoutPaymentRequestDetailsType;
use PayPal\EBLBaseComponents\PaymentDetailsType;
use PayPal\PayPalAPI\DoExpressCheckoutPaymentReq;
use PayPal\PayPalAPI\DoExpressCheckoutPaymentRequestType;
use Destiny\Application;
use Destiny\Session;
use Destiny\Utils\Http;
use Destiny\Service\Subscriptions;
use Destiny\Service\Orders;
use Destiny\Config;
use Destiny\Utils\Date;
use Destiny\ViewModel;

class Complete {
	
	/**
	 * Unique checkout token
	 *
	 * @var string
	 */
	protected $checkoutId = '';

	/**
	 * We were redirected here from PayPal after the buyer approved/cancelled the payment
	 *
	 * @param array $params
	 * @throws \Exception
	 */
	public function execute(array $params, ViewModel $model) {
		$this->checkoutId = Session::get ( 'checkoutId' );
		$ordersService = Orders::getInstance ();
		
		// Make sure our checkoutId is valid
		if (! isset ( $params ['checkoutId'] ) || empty ( $this->checkoutId ) || $this->checkoutId != $params ['checkoutId']) {
			// If we have an invalid checkout token.
			// Find the order, if its Complete, forward the user to the invoice page
			if (isset ( $params ['orderId'] )) {
				$order = $ordersService->getOrderById ( $params ['orderId'] );
				// Make sure the order is for this user
				if (! empty ( $order ) && $order ['userId'] == Session::get ( 'userId' ) && strcasecmp ( $order ['state'], 'Completed' ) === 0) {
					Http::header ( Http::HEADER_LOCATION, '/order/invoice?orderId=' . urlencode ( $params ['orderId'] ) );
					die ();
				}
			}
			throw new \Exception ( 'Invalid checkout token' );
		}
		if (! isset ( $params ['token'] ) || empty ( $params ['token'] )) {
			throw new \Exception ( 'Invalid token' );
		}
		if (! isset ( $params ['success'] )) {
			throw new \Exception ( 'Invalid success response' );
		}
		if (! isset ( $params ['orderId'] )) {
			throw new \Exception ( 'Invalid orderId' );
		}
		
		// The token from paypal
		$token = $params ['token'];
		
		// Get | Build the order | Dirty
		$order = $ordersService->getOrderById ( $params ['orderId'] );
		if (empty ( $order )) {
			throw new \Exception ( 'Invalid order record' );
		}
		if ($order ['state'] != 'New') {
			throw new \Exception ( 'Invalid order status' );
		}
		$order ['items'] = $ordersService->getOrderItems ( $order ['orderId'] );
		$subscription = Subscriptions::getInstance ()->getSubscriptionType ( $order ['items'] [0] ['itemSku'] );
		$paymentProfile = $ordersService->getPaymentProfileByOrderId ( $order ['orderId'] );
		// END REALLY DIRTY
		
		// If we got a failed response URL
		if ($params ['success'] == '0' || $params ['success'] == 'false' || $params ['success'] === false) {
			$ordersService->updateOrderState ( $order ['orderId'], 'Error' );
			// Also set the profile state to error
			if (! empty ( $paymentProfile )) {
				$ordersService->updatePaymentProfileState ( $paymentProfile ['profileId'], 'Error' );
			}
			throw new \Exception ( 'Order request failed' );
		}
		
		// Get the checkout info
		$ecResponse = $this->retrieveCheckoutInfo ( $token );
		if (! isset ( $ecResponse ) || $ecResponse->Ack != 'Success') {
			$ordersService->updateOrderState ( $order ['orderId'], 'Error' );
			throw new \Exception ( 'Failed to retrieve express checkout details' );
		}
		
		// Is done after the success check
		if (! isset ( $params ['PayerID'] ) || empty ( $params ['PayerID'] )) {
			throw new \Exception ( 'Invalid PayerID' );
		}
		
		// Point of no return - we only every want a person to get here if their order was a successful sequence
		Session::set ( 'token' );
		Session::set ( 'orderId' );
		Session::set ( 'checkoutId' );
		
		// RECURRING PAYMENT
		if (! empty ( $paymentProfile )) {
			$createRPProfileResponse = $this->createRecurringPaymentProfile ( $paymentProfile, $token, $subscription );
			if (! isset ( $createRPProfileResponse ) || $createRPProfileResponse->Ack != 'Success') {
				$ordersService->updateOrderState ( $order ['orderId'], 'Error' );
				throw new \Exception ( 'Failed to create recurring payment request' );
			}
			$paymentProfileId = $createRPProfileResponse->CreateRecurringPaymentsProfileResponseDetails->ProfileID;
			$paymentStatus = $createRPProfileResponse->CreateRecurringPaymentsProfileResponseDetails->ProfileStatus;
			if (empty ( $paymentProfileId )) {
				$ordersService->updateOrderState ( $order ['orderId'], 'Error' );
				throw new \Exception ( 'Invalid recurring payment profileId returned from Paypal' );
			}
			// Set the payment profile to active, and paymetProfileId
			$ordersService->activatePaymentProfile ( $paymentProfile ['profileId'], $paymentProfileId, $paymentStatus );
		}
		
		// Complete the checkout
		$DoECRequestDetails = new DoExpressCheckoutPaymentRequestDetailsType ();
		$DoECRequestDetails->PayerID = $params ['PayerID'];
		$DoECRequestDetails->Token = $token;
		$DoECRequestDetails->PaymentAction = 'Sale';
		
		$paymentDetails = new PaymentDetailsType ();
		$paymentDetails->OrderTotal = new BasicAmountType ( $order ['currency'], $order ['amount'] );
		$paymentDetails->NotifyURL = 'http://cene.co.za/pp/ipn.php';
		$DoECRequestDetails->PaymentDetails [0] = $paymentDetails;
		
		$DoECRequest = new DoExpressCheckoutPaymentRequestType ();
		$DoECRequest->DoExpressCheckoutPaymentRequestDetails = $DoECRequestDetails;
		$DoECReq = new DoExpressCheckoutPaymentReq ();
		$DoECReq->DoExpressCheckoutPaymentRequest = $DoECRequest;
		
		$paypalService = new PayPalAPIInterfaceServiceService ();
		$DoECResponse = $paypalService->DoExpressCheckoutPayment ( $DoECReq );
		
		$payments = array ();
		if (isset ( $DoECResponse ) && $DoECResponse->Ack == 'Success') {
			if (isset ( $DoECResponse->DoExpressCheckoutPaymentResponseDetails->PaymentInfo )) {
				$orderStatus = 'Completed';
				for($i = 0; $i < count ( $DoECResponse->DoExpressCheckoutPaymentResponseDetails->PaymentInfo ); ++ $i) {
					$paymentInfo = $DoECResponse->DoExpressCheckoutPaymentResponseDetails->PaymentInfo [$i];
					$payment = array ();
					$payment ['orderId'] = $order ['orderId'];
					$payment ['payerId'] = $params ['PayerID'];
					$payment ['amount'] = $paymentInfo->GrossAmount->value;
					$payment ['currency'] = $paymentInfo->GrossAmount->currencyID;
					$payment ['transactionId'] = $paymentInfo->TransactionID;
					$payment ['transactionType'] = $paymentInfo->TransactionType;
					$payment ['paymentType'] = $paymentInfo->PaymentType;
					$payment ['paymentStatus'] = $paymentInfo->PaymentStatus;
					$payment ['paymentDate'] = Date::getDateTime ( $paymentInfo->PaymentDate, 'Y-m-d H:i:s' );
					if ($paymentInfo->PaymentStatus != 'Completed') {
						$orderStatus = 'Incomplete';
					}
					$ordersService->addOrderPayment ( $payment );
					$payments [] = $payment;
				}
				$ordersService->updateOrderState ( $order ['orderId'], $orderStatus );
			} else {
				$ordersService->updateOrderState ( $order ['orderId'], 'Error' );
				throw new \Exception ( sprintf ( 'No payments for express checkout order %s', $order ['orderId'] ) );
			}
		} else {
			$ordersService->updateOrderState ( $order ['orderId'], 'Error' );
			throw new \Exception ( $DoECResponse->Errors [0]->LongMessage );
		}
		
		// Create / adjust subscription
		Subscriptions::getInstance ()->addUserSubscription ( $order ['userId'], $subscription, 'Active', $paymentProfile );
		
		// Add the subscriber role, this is just for UI
		$authCreds = Session::getAuthCredentials ();
		if (! empty ( $authCreds )) {
			$authCreds->addRoles ( 'subscriber' );
			Session::setAuthCredentials ( $authCreds );
		}
		
		// Show the order complete screen
		$model->order = $order;
		$model->orderReference = $ordersService->buildOrderRef ( $order );
		$model->subscription = $subscription;
		$model->paymentProfile = $paymentProfile;
		return 'ordercomplete';
	}

	/**
	 * Retrieve the checkout instance from paypal
	 *
	 * @return \PayPalAPI\GetExpressCheckoutDetailsResponseType
	 */
	protected function retrieveCheckoutInfo($token) {
		$paypalService = new PayPalAPIInterfaceServiceService ();
		$getExpressCheckoutReq = new GetExpressCheckoutDetailsReq ();
		$getExpressCheckoutReq->GetExpressCheckoutDetailsRequest = new GetExpressCheckoutDetailsRequestType ( $token );
		return $paypalService->GetExpressCheckoutDetails ( $getExpressCheckoutReq );
	}

	/**
	 * Create a Paypal recurring payment profile
	 *
	 * @param array $order
	 * @param string $token
	 * @param array $subscription
	 * @return \PayPalAPI\CreateRecurringPaymentsProfileResponseType
	 */
	protected function createRecurringPaymentProfile(array $paymentProfile, $token, array $subscription) {
		$paypalService = new PayPalAPIInterfaceServiceService ();
		
		// @TODO this is strange, or dirty cant tell
		$billingStartDate = new \DateTime ( $paymentProfile ['billingStartDate'] );
		
		$RPProfileDetails = new RecurringPaymentsProfileDetailsType ();
		$RPProfileDetails->SubscriberName = Session::get ( 'displayName' ); // This should be passed in
		$RPProfileDetails->BillingStartDate = $billingStartDate->format ( \DateTime::ATOM );
		$RPProfileDetails->ProfileReference = $paymentProfile ['userId'] . '-' . $paymentProfile ['profileId'] . '-' . $paymentProfile ['orderId'];
		
		$paymentBillingPeriod = new BillingPeriodDetailsType ();
		$paymentBillingPeriod->BillingFrequency = $paymentProfile ['billingFrequency'];
		$paymentBillingPeriod->BillingPeriod = $paymentProfile ['billingPeriod'];
		$paymentBillingPeriod->Amount = new BasicAmountType ( $paymentProfile ['currency'], $paymentProfile ['amount'] );
		
		$scheduleDetails = new ScheduleDetailsType ();
		$scheduleDetails->Description = $subscription ['agreement'];
		$scheduleDetails->PaymentPeriod = $paymentBillingPeriod;
		
		// $activationDetails = new ActivationDetailsType ();
		// $activationDetails->InitialAmount = new BasicAmountType ( Config::$a ['commerce'] ['currency'], 100000.00 );
		// $activationDetails->FailedInitialAmountAction = 'CancelOnFailure';
		// $scheduleDetails->ActivationDetails = $activationDetails;
		
		$createRPProfileRequestDetail = new CreateRecurringPaymentsProfileRequestDetailsType ();
		$createRPProfileRequestDetail->Token = $token;
		$createRPProfileRequestDetail->ScheduleDetails = $scheduleDetails;
		$createRPProfileRequestDetail->RecurringPaymentsProfileDetails = $RPProfileDetails;
		
		$createRPProfileRequest = new CreateRecurringPaymentsProfileRequestType ();
		$createRPProfileRequest->CreateRecurringPaymentsProfileRequestDetails = $createRPProfileRequestDetail;
		
		$createRPProfileReq = new CreateRecurringPaymentsProfileReq ();
		$createRPProfileReq->CreateRecurringPaymentsProfileRequest = $createRPProfileRequest;
		
		return $paypalService->CreateRecurringPaymentsProfile ( $createRPProfileReq );
	}

}