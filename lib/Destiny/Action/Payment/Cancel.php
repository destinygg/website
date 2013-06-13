<?php

namespace Destiny\Action\Payment;

use Destiny\Application;
use Destiny\Service\SubscriptionsService;
use Destiny\Service\OrdersService;
use Destiny\Utils\Http;
use Destiny\ViewModel;
use Destiny\Session;
use Destiny\AppException;
use PayPal\EBLBaseComponents\ManageRecurringPaymentsProfileStatusRequestDetailsType;
use PayPal\PayPalAPI\ManageRecurringPaymentsProfileStatusReq;
use PayPal\PayPalAPI\ManageRecurringPaymentsProfileStatusRequestType;
use PayPal\Service\PayPalAPIInterfaceServiceService;

class Cancel {

	public function execute(array $params, ViewModel $model) {
		$subService = SubscriptionsService::getInstance ();
		$orderService = OrdersService::getInstance ();
		
		$subscription = SubscriptionsService::getInstance ()->getUserActiveSubscription ( Session::get ( 'userId' ) );
		$paymentProfile = null;
		if (! empty ( $subscription ['paymentProfileId'] )) {
			$paymentProfile = $orderService->getPaymentProfileById ( $subscription ['paymentProfileId'] );
			if (! empty ( $paymentProfile )) {
				$paymentProfile ['billingCycle'] = $orderService->buildBillingCycleString ( $paymentProfile ['billingFrequency'], $paymentProfile ['billingPeriod'] );
			}
		}
		
		// We need an active sub
		if (empty ( $subscription )) {
			throw new AppException ( 'No active subscription to cancel' );
		}
		// We always need an active profile
		if (empty ( $paymentProfile ) || strcasecmp ( $paymentProfile ['state'], 'ActiveProfile' ) !== 0) {
			throw new AppException ( sprintf ( 'Invalid payment profile' ) );
		}
		
		$model->title = 'Cancel scheduled payment';
		$model->subscription = $subscription;
		$model->paymentProfile = $paymentProfile;
		
		// Get confirmation
		if (! isset ( $params ['confirmationId'] ) || empty ( $params ['confirmationId'] )) {
			$confirmationId = md5 ( microtime ( true ) . Session::get ( 'userId' ) );
			Session::set ( 'confirmationId', $confirmationId );
			$model->confirmationId = $confirmationId;
			$model->unsubscribed = false;
			return 'paymentcancel';
		}
		
		// Confirmation received
		if (isset ( $params ['confirmationId'] ) && $params ['confirmationId'] == Session::get ( 'confirmationId' )) {
			// Reset confirmation
			Session::set ( 'confirmationId' );
			
			// Do we have a payment profile, we need to cancel it with paypal
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
				$paymentProfile ['state'] = 'Cancelled';
				$orderService->updatePaymentProfileState ( $paymentProfile ['profileId'], $paymentProfile ['state'] );
			} else {
				throw new AppException ( $manageRPPStatusResponse->Errors [0]->LongMessage );
			}
			// Cancel subscription status
			$subscription ['recurring'] = 0;
			$subService->updateUserSubscriptionRecurring ( Session::get ( 'userId' ), 0 );
			
			// Show unsubscribed screen
			$model->unsubscribed = true;
			return 'paymentcancel';
		} else {
			throw new AppException ( 'Invalid confirmation id' );
		}
		throw new AppException ( 'Could not cancel subscription' );
	}

}