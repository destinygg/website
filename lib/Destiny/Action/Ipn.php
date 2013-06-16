<?php

namespace Destiny\Action;

use PayPal\IPN\PPIPNMessage;
use Destiny\Application;
use Destiny\Service\OrdersService;
use Destiny\Config;
use Destiny\Utils\Date;
use Destiny\Service\SubscriptionsService;
use Destiny\AppException;

class Ipn {

	/**
	 * Handles the incoming HTTP request
	 *
	 * @param array $params
	 */
	public function execute(array $params) {
		$log = Application::instance ()->getLogger ();
		$ipnMessage = new PPIPNMessage ();
		if (! $ipnMessage->validate ()) {
			$log->error ( 'Got a invalid IPN ' . json_encode ( $ipnMessage->getRawData () ) );
			return;
		}
		$log->info ( sprintf ( 'Got a valid IPN [txn_id: %s, txn_type: %s]', $ipnMessage->getTransactionId (), $ipnMessage->getTransactionType () ) );
		$data = $ipnMessage->getRawData ();
		$orderService = OrdersService::instance ();
		$orderService->addIPNRecord ( array (
				'ipnTrackId' => $data ['ipn_track_id'],
				'ipnTransactionId' => $ipnMessage->getTransactionId (),
				'ipnTransactionType' => $ipnMessage->getTransactionType (),
				'ipnData' => json_encode ( $data ) 
		) );
		$this->handleIPN ( $ipnMessage );
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

	/**
	 * Handles the IPN message
	 *
	 * @param PPIPNMessage $ipnMessage
	 */
	protected function handleIPN(PPIPNMessage $ipnMessage) {
		$log = Application::instance ()->getLogger ();
		$transactionId = $ipnMessage->getTransactionId ();
		$transactionType = $ipnMessage->getTransactionType ();
		$data = $ipnMessage->getRawData ();
		$orderService = OrdersService::instance ();
		
		// Make sure this IPN is for the merchant
		if (strcasecmp ( Config::$a ['commerce'] ['receiver_email'], $data ['receiver_email'] ) !== 0) {
			throw new AppException ( sprintf ( 'IPN originated with incorrect receiver_email' ) );
		}
		
		switch (strtolower ( $transactionType )) {
			
			// Post back from checkout, make sure the payment lines up
			case 'express_checkout' :
				$payment = $orderService->getPaymentByTransactionId ( $data ['txn_id'] );
				if (! empty ( $payment )) {
					if (number_format ( $payment ['amount'], 2 ) != number_format ( $data ['mc_gross'], 2 )) {
						throw new AppException ( 'Amount for payment do not match' );
					}
					$orderService->updatePaymentState ( $payment, $data ['payment_status'] );
					$log->notice ( sprintf ( 'Updated payment status %s status %s', $data ['txn_id'], $data ['payment_status'] ) );
				}
				break;
			
			// Recurring payment, renew subscriptions. make sure 'payment_status' == 'completed'
			case 'recurring_payment' :
				if (! isset ( $data ['payment_status'] )) {
					throw new AppException ( 'Invalid payment status' );
				}
				$paymentProfile = $this->getPaymentProfile ( $data );
				$nextPaymentDate = Date::getDateTime ( $data ['next_payment_date'] );
				$orderService->updatePaymentProfileState ( $paymentProfile ['profileId'], $data ['payment_status'] );
				$orderService->updatePaymentProfileNextPayment ( $paymentProfile ['profileId'], $nextPaymentDate );
				
				if (strcasecmp ( $data ['payment_status'], 'Completed' ) === 0) {
					// Add a payment
					// @TODO it doesnt say wether this IPN request is in addition to another payment request I assume not
					$payment = array ();
					$payment ['orderId'] = $paymentProfile ['orderId'];
					$payment ['payerId'] = $data ['payer_id'];
					$payment ['amount'] = $data ['mc_gross'];
					$payment ['currency'] = $data ['mc_currency'];
					$payment ['transactionId'] = $transactionId;
					$payment ['transactionType'] = $transactionType;
					$payment ['paymentType'] = $data ['payment_type'];
					$payment ['paymentStatus'] = $data ['payment_status'];
					$payment ['paymentDate'] = Date::getDateTime ( $data ['payment_date'], 'Y-m-d H:i:s' );
					$orderService->addOrderPayment ( $payment );
					
					// Extend the subscription if the payment was successful
					SubscriptionsService::instance ()->updateUserSubscriptionDateEnd ( $paymentProfile ['userId'], $nextPaymentDate );
					$log->notice ( sprintf ( 'Renewed profile %s status %s', $data ['recurring_payment_id'], $data ['profile_status'] ) );
				} else {
					SubscriptionsService::instance ()->updateUserSubscriptionState ( $paymentProfile ['userId'], $data ['payment_status'] );
					$log->notice ( sprintf ( 'Failed to renew profile %s status %s', $data ['recurring_payment_id'], $data ['profile_status'] ) );
				}
				
				break;
			
			// Sent if user cancels subscription from Paypal's site.
			case 'recurring_payment_profile_cancel' :
				$paymentProfile = $this->getPaymentProfile ( $data );
				$orderService->updatePaymentProfileState ( $paymentProfile ['profileId'], $data ['profile_status'] );
				$log->notice ( sprintf ( 'Payment profile cancelled %s status %s', $data ['recurring_payment_id'], $data ['profile_status'] ) );
				break;
			
			// sent on first postback when the user first subscribes.
			case 'recurring_payment_profile_created' :
				$paymentProfile = $this->getPaymentProfile ( $data );
				$orderService->updatePaymentProfileState ( $paymentProfile ['profileId'], $data ['profile_status'] );
				$log->notice ( sprintf ( 'Updated payment profile %s status %s', $data ['recurring_payment_id'], $data ['profile_status'] ) );
				break;
		}
	}

}