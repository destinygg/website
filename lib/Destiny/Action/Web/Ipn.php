<?php
namespace Destiny\Action\Web;

use Destiny\Common\Service\AuthenticationService;
use Destiny\Common\Commerce\PaymentProfileStatus;
use Destiny\Common\Commerce\SubscriptionStatus;
use Destiny\Common\Commerce\OrderStatus;
use Destiny\Common\Commerce\PaymentStatus;
use Destiny\Common\Utils\Http;
use Destiny\Common\Application;
use Destiny\Common\Service\OrdersService;
use Destiny\Common\Config;
use Destiny\Common\Utils\Date;
use Destiny\Common\Service\SubscriptionsService;
use Destiny\Common\AppException;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;
use PayPal\IPN\PPIPNMessage;

/**
 * @Action
 */
class Ipn {

	/**
	 * @Route ("/ipn")
	 *
	 * Handles the incoming HTTP request
	 * @param array $params
	 */
	public function execute(array $params) {
		$log = Application::instance ()->getLogger ();
		$ipnMessage = new PPIPNMessage ();
		if (! $ipnMessage->validate ()) {
			$log->error ( 'Got a invalid IPN ' . json_encode ( $ipnMessage->getRawData () ) );
			$this->niceExit ( false );
		}
		$data = $ipnMessage->getRawData ();
		$log->info ( sprintf ( 'Got a valid IPN [txn_id: %s, txn_type: %s]', $ipnMessage->getTransactionId (), $data ['txn_type'] ) );
		$orderService = OrdersService::instance ();
		$orderService->addIPNRecord ( array (
			'ipnTrackId' => $data ['ipn_track_id'],
			'ipnTransactionId' => $data ['txn_id'],
			'ipnTransactionType' => $data ['txn_type'],
			'ipnData' => json_encode ( $data ) 
		) );
		
		// Make sure this IPN is for the merchant
		if (strcasecmp ( Config::$a ['commerce'] ['receiver_email'], $data ['receiver_email'] ) !== 0) {
			$log->critical ( sprintf ( 'IPN originated with incorrect receiver_email' ) );
			$this->niceExit ( false );
		}
		
		$this->handleIPNTransaction ( $data ['txn_id'], $data ['txn_type'], $data );
		$this->niceExit ( true );
	}

	/**
	 * Closes and sends an OK response
	 *
	 * @return void
	 */
	private function niceExit($verified) {
		Http::status ( Http::STATUS_OK );
		echo ($verified) ? 'VERIFIED' : 'INVALID';
		exit ();
	}

	/**
	 * Handles the IPN message
	 *
	 * @param PPIPNMessage $ipnMessage
	 */
	protected function handleIPNTransaction($txnId, $txnType, array $data) {
		$log = Application::instance ()->getLogger ();
		$orderService = OrdersService::instance ();
		$subService = SubscriptionsService::instance ();
		$authService = AuthenticationService::instance ();
		
		switch (strtolower ( $txnType )) {
			
			// Post back from checkout, make sure the payment lines up
			// This is sent when a express checkout has been performed by a user
			case 'express_checkout' :
				$payment = $orderService->getPaymentByTransactionId ( $txnId );
				if (! empty ( $payment )) {
					
					// Make sure the payment values are the same
					if (number_format ( $payment ['amount'], 2 ) != number_format ( $data ['mc_gross'], 2 )) {
						throw new AppException ( 'Amount for payment do not match' );
					}
					
					// Update the payment status
					$orderService->updatePaymentState ( $payment, $data ['payment_status'] );
					$log->notice ( sprintf ( 'Updated payment status %s status %s', $data ['txn_id'], $data ['payment_status'] ) );
					
					// If the payment status WAS PENDING, and the IPN payment status is COMPLETED
					// Then we need to activate the attached subscription and complete the order
					// This is for the ECHECK payment method
					if (strcasecmp ( $payment ['paymentStatus'], PaymentStatus::PENDING ) === 0 && strcasecmp ( $data ['payment_status'], PaymentStatus::COMPLETED ) === 0) {
						$order = $orderService->getOrderByPaymentId ( $payment ['paymentId'] );
						if (! empty ( $order )) {
							$orderService->updateOrderState ( $order ['orderId'], OrderStatus::COMPLETED );
							$log->notice ( sprintf ( 'Updated order status %s status %s', $order ['orderId'], OrderStatus::COMPLETED ) );
							$subscription = $subService->getUserPendingSubscription ( $order ['userId'] );
							if (! empty ( $subscription )) {
								$subService->updateSubscriptionState ( $subscription ['subscriptionId'], SubscriptionStatus::ACTIVE );
								$log->notice ( sprintf ( 'Updated subscription status %s status %s', $order ['orderId'], SubscriptionStatus::ACTIVE ) );
								$authService->flagUserForUpdate ( $subscription ['userId'] );
							}
						}
					}
				}
				break;
			
			// Recurring payment, renew subscriptions, or set to pending depending on the type
			// This is sent from paypal when a recurring payment is billed
			case 'recurring_payment' :
				
				if (! isset ( $data ['payment_status'] )) {
					throw new AppException ( 'Invalid payment status' );
				}
				if (! isset ( $data ['next_payment_date'] )) {
					throw new AppException ( 'Invalid next_payment_date' );
				}
				
				$paymentProfile = $this->getPaymentProfile ( $data );
				$subscription = $subService->getUserActiveSubscription ( $paymentProfile ['userId'] );
				if (empty ( $subscription )) {
					throw new AppException ( 'Invalid subscription for recurring payment' );
				}
				
				$nextPaymentDate = Date::getDateTime ( $data ['next_payment_date'] );
				$orderService->updatePaymentProfileNextPayment ( $paymentProfile ['profileId'], $nextPaymentDate );
				
				// Update the subscription end date regardless if the payment was successful or not
				$end = Date::getDateTime ( $subscription ['endDate'] );
				$end->modify ( '+' . $paymentProfile ['billingFrequency'] . ' ' . strtolower ( $paymentProfile ['billingPeriod'] ) );
				
				$subService->updateSubscriptionDateEnd ( $subscription ['subscriptionId'], $end );
				$log->notice ( sprintf ( 'Update Subscription end date %s [%s]', $subscription ['subscriptionId'], $end->format ( Date::FORMAT ) ) );
				
				// Change the subscription state depending on the payment state
				if (strcasecmp ( $data ['payment_status'], PaymentStatus::PENDING ) === 0) {
					$subService->updateSubscriptionState ( $subscription ['subscriptionId'], SubscriptionStatus::PENDING );
					$log->notice ( sprintf ( 'Updated subscription state %s status %s', $subscription ['subscriptionId'], SubscriptionStatus::PENDING ) );
				} else if (strcasecmp ( $data ['payment_status'], PaymentStatus::COMPLETED ) !== 0) {
					$subService->updateSubscriptionState ( $subscription ['subscriptionId'], $data ['payment_status'] );
					$log->notice ( sprintf ( 'Updated subscription state %s status %s', $subscription ['subscriptionId'], $data ['payment_status'] ) );
				}
				
				// Add a payment to the order
				$payment = array ();
				$payment ['orderId'] = $paymentProfile ['orderId'];
				$payment ['payerId'] = $data ['payer_id'];
				$payment ['amount'] = $data ['mc_gross'];
				$payment ['currency'] = $data ['mc_currency'];
				$payment ['transactionId'] = $txnId;
				$payment ['transactionType'] = $txnType;
				$payment ['paymentType'] = $data ['payment_type'];
				$payment ['paymentStatus'] = $data ['payment_status'];
				$payment ['paymentDate'] = Date::getDateTime ( $data ['payment_date'] )->format ( 'Y-m-d H:i:s' );
				$orderService->addOrderPayment ( $payment );
				$log->notice ( sprintf ( 'Added order payment %s status %s', $data ['recurring_payment_id'], $data ['profile_status'] ) );
				$authService->flagUserForUpdate ( $subscription ['userId'] );
				break;
			
			// Sent if user cancels subscription from Paypal's site.
			case 'recurring_payment_profile_cancel' :
				$paymentProfile = $this->getPaymentProfile ( $data );
				$orderService->updatePaymentProfileState ( $paymentProfile ['profileId'], $data ['profile_status'] );
				$log->notice ( sprintf ( 'Payment profile cancelled %s status %s', $data ['recurring_payment_id'], $data ['profile_status'] ) );
				break;
			
			// sent on first postback when the user subscribes
			case 'recurring_payment_profile_created' :
				$paymentProfile = $this->getPaymentProfile ( $data );
				if (strcasecmp ( $data ['profile_status'], 'Active' ) === 0) {
					$data ['profile_status'] = 'ActiveProfile';
				}
				$orderService->updatePaymentProfileState ( $paymentProfile ['profileId'], $data ['profile_status'] );
				$log->notice ( sprintf ( 'Updated payment profile %s status %s', $data ['recurring_payment_id'], $data ['profile_status'] ) );
				break;
		}
	}

	/**
	 * Get payment profile from IPN
	 *
	 * @param array $data
	 * @return unknown
	 */
	protected function getPaymentProfile(array $data) {
		if (! isset ( $data ['recurring_payment_id'] ) || empty ( $data ['recurring_payment_id'] )) {
			throw new AppException ( 'Invalid recurring_payment_id' );
		}
		$orderService = OrdersService::instance ();
		$paymentProfile = $orderService->getPaymentProfileByPaymentProfileId ( $data ['recurring_payment_id'] );
		if (empty ( $paymentProfile )) {
			throw new AppException ( 'Invalid payment profile' );
		}
		return $paymentProfile;
	}

}