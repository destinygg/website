<?php

namespace Destiny\Action\Order;

use PayPal\CoreComponentTypes\BasicAmountType;
use PayPal\Service\PayPalAPIInterfaceServiceService;
use PayPal\PayPalAPI\SetExpressCheckoutReq;
use PayPal\PayPalAPI\SetExpressCheckoutRequestType;
use PayPal\EBLBaseComponents\BillingAgreementDetailsType;
use PayPal\EBLBaseComponents\PaymentDetailsItemType;
use PayPal\EBLBaseComponents\PaymentDetailsType;
use PayPal\EBLBaseComponents\SetExpressCheckoutRequestDetailsType;
use PayPal\PayPalAPI\GetExpressCheckoutDetailsReq;
use PayPal\PayPalAPI\GetExpressCheckoutDetailsRequestType;
use Destiny\Application;
use Destiny\Session;
use Destiny\Utils\Http;
use Destiny\Service\Orders;
use Destiny\Service\Subscriptions;
use Destiny\Config;

class Create {
	
	/**
	 * Unique checkout token
	 *
	 * @var string
	 */
	protected $checkoutId = '';

	/**
	 * Create and send the order
	 *
	 * @param array $params
	 * @throws \Exception
	 */
	public function execute(array $params) {
		$this->checkoutId = Session::get ( 'checkoutId' );
		
		// Make sure the user hasnt somehow started the process with an active subscription
		$subsService = Subscriptions::getInstance ();
		$subscription = $subsService->getUserActiveSubscription ( Session::get ( 'userId' ) );
		if (! empty ( $subscription )) {
			throw new \Exception ( 'User already has a valid subscription' );
		}
		
		// Make sure our checkoutId is valid
		if (! isset ( $params ['checkoutId'] ) || empty ( $this->checkoutId ) || $this->checkoutId != $params ['checkoutId']) {
			throw new \Exception ( 'Invalid checkout token' );
		}
		if (! isset ( $params ['subscription'] ) || empty ( $params ['subscription'] )) {
			throw new \Exception ( 'Empty subscription type' );
		}
		
		$subscription = $subsService->getSubscriptionType ( $params ['subscription'] );
		$ordersService = Orders::getInstance ();
		$order = $this->createOrder ( $subscription );
		
		if (isset ( $params ['renew'] ) && $params ['renew'] == '1') {
			$paymentProfile = $this->createPaymentProfile ( $order, $subscription );
			$setECResponse = $this->getExpressCheckoutResponse ( $order, $subscription, $paymentProfile );
		} else {
			$setECResponse = $this->getExpressCheckoutResponse ( $order, $subscription );
		}
		
		if (isset ( $setECResponse ) && $setECResponse->Ack == 'Success') {
			Http::header ( Http::HEADER_LOCATION, 'https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&token=' . urlencode ( $setECResponse->Token ) );
			die ();
		}
		
		$ordersService->updateOrderState ( $order ['orderId'], 'Error' );
		throw new \Exception ( $setECResponse->Errors->ShortMessage );
	}

	/**
	 * Execute the setExpressCheckout process, forwards to paypal
	 *
	 * @param array $order
	 * @param array $subscription
	 * @param array $paymentProfile
	 * @throws \Exception
	 */
	protected function getExpressCheckoutResponse(array $order, array $subscription, array $paymentProfile = null) {
		$returnUrl = Http::getBaseUrl () . '/order/complete?success=true&orderId=' . urlencode ( $order ['orderId'] ) . '&checkoutId=' . urlencode ( $this->checkoutId );
		$cancelUrl = Http::getBaseUrl () . '/order/complete?success=false&orderId=' . urlencode ( $order ['orderId'] ) . '&checkoutId=' . urlencode ( $this->checkoutId );
		
		$setECReqDetails = new SetExpressCheckoutRequestDetailsType ();
		$setECReqDetails->ReqConfirmShipping = 0;
		$setECReqDetails->NoShipping = 1;
		$setECReqDetails->AllowNote = 0;
		$setECReqDetails->ReturnURL = $returnUrl;
		$setECReqDetails->CancelURL = $cancelUrl;
		$setECReqDetails->SolutionType = 'Sole';
		
		if (! empty ( $paymentProfile )) {
			// Create billing agreement for recurring payment
			$billingAgreementDetails = new BillingAgreementDetailsType ( 'RecurringPayments' );
			$billingAgreementDetails->BillingAgreementDescription = $subscription ['agreement'];
			$setECReqDetails->BillingAgreementDetails [0] = $billingAgreementDetails;
		}
		
		$paymentDetails = new PaymentDetailsType ();
		$paymentDetails->PaymentAction = 'Sale';
		$paymentDetails->NotifyURL = 'http://cene.co.za/pp/ipn.php';
		$paymentDetails->OrderTotal = new BasicAmountType ( $order ['currency'], $order ['amount'] );
		$paymentDetails->ItemTotal = new BasicAmountType ( $order ['currency'], $order ['amount'] );
		$paymentDetails->Recurring = 0;
		$itemDetails = new PaymentDetailsItemType ();
		$itemDetails->Name = $subscription ['label'];
		$itemDetails->Amount = new BasicAmountType ( $order ['currency'], $order ['amount'] );
		$itemDetails->Quantity = 1;
		$itemDetails->ItemCategory = 'Digital';
		$itemDetails->Number = $subscription ['id'];
		$paymentDetails->PaymentDetailsItem [0] = $itemDetails;
		$setECReqDetails->PaymentDetails [0] = $paymentDetails;
		
		// Paypal UI settings
		$setECReqDetails->BrandName = Config::$a ['commerce'] ['reciever'] ['brandName'];
		
		// Execute checkout
		$setECReqType = new SetExpressCheckoutRequestType ();
		$setECReqType->SetExpressCheckoutRequestDetails = $setECReqDetails;
		$setECReq = new SetExpressCheckoutReq ();
		$setECReq->SetExpressCheckoutRequest = $setECReqType;
		
		$paypalService = new PayPalAPIInterfaceServiceService ();
		return $paypalService->SetExpressCheckout ( $setECReq );
	}

	/**
	 * Create a new order and item based on subscription
	 *
	 * @param array $subscription
	 * @return array
	 */
	private function createOrder(array $subscription) {
		$ordersService = Orders::getInstance ();
		$order = array ();
		$order ['userId'] = Session::get ( 'userId' );
		$order ['description'] = $subscription ['label'];
		$order ['amount'] = $subscription ['amount'];
		$order ['currency'] = Config::$a ['commerce'] ['currency'];
		$order ['orderId'] = $ordersService->addOrder ( $order );
		$order ['items'] = array ();
		$orderItem = array ();
		$orderItem ['orderId'] = $order ['orderId'];
		$orderItem ['itemSku'] = $subscription ['id'];
		$orderItem ['itemPrice'] = $subscription ['amount'];
		$order ['items'] [] = $orderItem;
		$ordersService->addOrderItems ( $order ['items'] );
		return $order;
	}

	/**
	 * Create a new payment
	 *
	 * @param array $order
	 * @param array $subscription
	 * @return array
	 */
	private function createPaymentProfile(array $order, array $subscription) {
		$ordersService = Orders::getInstance ();
		// @TODO this should be set in the payment profile
		$billingStartDate = new \DateTime ( date ( 'm/d/y' ) );
		// @TODO this is dangerous, using strtotime format - there is not solid link between them to prevent it from breaking
		// @TODO does paypal accept any timezones?
		$billingStartDate->modify ( '+' . $subscription ['billingFrequency'] . ' ' . strtolower ( $subscription ['billingPeriod'] ) );
		
		$paymentProfile = array ();
		$paymentProfile ['paymentProfileId'] = '';
		$paymentProfile ['userId'] = Session::get ( 'userId' );
		$paymentProfile ['orderId'] = $order ['orderId'];
		$paymentProfile ['amount'] = $order ['amount'];
		$paymentProfile ['currency'] = $order ['currency'];
		$paymentProfile ['billingFrequency'] = $subscription ['billingFrequency'];
		$paymentProfile ['billingPeriod'] = $subscription ['billingPeriod'];
		$paymentProfile ['billingStartDate'] = $billingStartDate->format ( 'Y-m-d H:i:s' );
		$paymentProfile ['billingNextDate'] = $billingStartDate->format ( 'Y-m-d H:i:s' );
		$paymentProfile ['state'] = 'New';
		$paymentProfile ['profileId'] = $ordersService->addPaymentProfile ( $paymentProfile );
		return $paymentProfile;
	}

}