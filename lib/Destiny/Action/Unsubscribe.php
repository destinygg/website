<?php

namespace Destiny\Action;

use Destiny\Application;
use Destiny\Service\Subscriptions;
use Destiny\Service\Orders;
use Destiny\Utils\Http;
use Destiny\ViewModel;
use Destiny\Session;
use PayPal\EBLBaseComponents\ManageRecurringPaymentsProfileStatusRequestDetailsType;
use PayPal\PayPalAPI\ManageRecurringPaymentsProfileStatusReq;
use PayPal\PayPalAPI\ManageRecurringPaymentsProfileStatusRequestType;
use PayPal\Service\PayPalAPIInterfaceServiceService;

class Unsubscribe {

	public function execute(array $params, ViewModel $model) {
		$subService = Subscriptions::getInstance ();
		$orderService = Orders::getInstance ();
		
		// Get confirmation
		if (! isset ( $params ['confirmationId'] ) || empty ( $params ['confirmationId'] )) {
			$confirmationId = md5 ( microtime ( true ) . Session::get ( 'userId' ) );
			Session::set ( 'confirmationId', $confirmationId );
			$model->title = 'Unsubscribe';
			$model->confirmationId = $confirmationId;
			$model->unsubscribed = false;
			return 'unsubscribe';
		}
		// Reset confirmation
		Session::set ( 'confirmationId' );
		
		// Confirmation received
		if (isset ( $params ['confirmationId'] ) || $params ['confirmationId'] == Session::get ( 'confirmationId' )) {
			$activeSub = $subService->getUserActiveSubscription ( Session::get ( 'userId' ) );
			if (! empty ( $activeSub )) {
				// Do we have a payment profile, we need to cancel it with paypal
				if (! empty ( $activeSub ['paymentProfileId'] )) {
					$paymentProfile = $orderService->getPaymentProfileById ( $activeSub ['paymentProfileId'] );
					if (empty ( $paymentProfile )) {
						throw new \Exception ( 'Payment profile invalid' );
					}
					$manageRPPStatusReqestDetails = new ManageRecurringPaymentsProfileStatusRequestDetailsType ();
					$manageRPPStatusReqestDetails->Action = 'Cancel';
					$manageRPPStatusReqestDetails->ProfileID = $paymentProfile ['paymentProfileId'];
					
					$manageRPPStatusReqest = new ManageRecurringPaymentsProfileStatusRequestType ();
					$manageRPPStatusReqest->ManageRecurringPaymentsProfileStatusRequestDetails = $manageRPPStatusReqestDetails;
					
					$manageRPPStatusReq = new ManageRecurringPaymentsProfileStatusReq ();
					$manageRPPStatusReq->ManageRecurringPaymentsProfileStatusRequest = $manageRPPStatusReqest;
					
					$paypalService = new PayPalAPIInterfaceServiceService ();
					$manageRPPStatusResponse = $paypalService->ManageRecurringPaymentsProfileStatus ( $manageRPPStatusReq );
					
					if (isset ( $manageRPPStatusResponse ) && $manageRPPStatusResponse->Ack == 'Success') {
						$orderService->updatePaymentProfileState ( $paymentProfile ['profileId'], 'Cancelled' );
					} else {
						throw new \Exception ( $manageRPPStatusResponse->Errors [0]->LongMessage );
					}
				}
				// Cancel subscription status
				$subService->updateUserSubscriptionState ( Session::get ( 'userId' ), 'Cancelled' );
				
				// Show unsubscribed screen
				$model->title = 'Unsubscribe';
				$model->confirmationId = $confirmationId;
				$model->unsubscribed = true;
				return 'unsubscribe';
			} else {
				throw new \Exception ( 'No active subscription to cancel' );
			}
		} else {
			throw new \Exception ( 'Invalid confirmation id' );
		}
		throw new \Exception ( 'Could not cancel subscription' );
	}

}