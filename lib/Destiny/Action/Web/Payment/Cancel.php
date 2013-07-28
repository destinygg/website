<?php
namespace Destiny\Action\Web\Payment;

use Destiny\Common\Commerce\PaymentProfileStatus;
use Destiny\Common\Application;
use Destiny\Common\Service\SubscriptionsService;
use Destiny\Common\Service\OrdersService;
use Destiny\Common\Utils\Http;
use Destiny\Common\ViewModel;
use Destiny\Common\Session;
use Destiny\Common\AppException;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;
use PayPal\PayPalAPI\GetRecurringPaymentsProfileDetailsReq;
use PayPal\PayPalAPI\GetRecurringPaymentsProfileDetailsRequestType;
use PayPal\EBLBaseComponents\ManageRecurringPaymentsProfileStatusRequestDetailsType;
use PayPal\PayPalAPI\ManageRecurringPaymentsProfileStatusReq;
use PayPal\PayPalAPI\ManageRecurringPaymentsProfileStatusRequestType;
use PayPal\Service\PayPalAPIInterfaceServiceService;

/**
 * @Action
 */
class Cancel {

	/**
	 * @Route ("/payment/cancel")
	 * @Secure ({"USER"})
	 *
	 * @param array $params
	 * @param ViewModel $model
	 * @throws AppException
	 * @return string
	 */
	public function execute(array $params, ViewModel $model) {
		$subService = SubscriptionsService::instance ();
		$orderService = OrdersService::instance ();
		
		$subscription = SubscriptionsService::instance ()->getUserActiveSubscription ( Session::getCredentials ()->getUserId () );
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
			throw new AppException ( sprintf ( 'Payment profile is not active %s', $paymentProfile ['state'] ) );
		}
		
		$model->title = 'Cancel scheduled payment';
		$model->subscription = $subscription;
		$model->paymentProfile = $paymentProfile;
		
		// Get confirmation
		if (! isset ( $params ['confirmationId'] ) || empty ( $params ['confirmationId'] )) {
			$confirmationId = md5 ( microtime ( true ) . Session::getCredentials ()->getUserId () );
			Session::set ( 'confirmationId', $confirmationId );
			$model->confirmationId = $confirmationId;
			$model->unsubscribed = false;
			return 'paymentcancel';
		}
		
		// Confirmation received
		if (isset ( $params ['confirmationId'] ) && $params ['confirmationId'] == Session::get ( 'confirmationId' )) {
			// Reset confirmation
			Session::set ( 'confirmationId' );
			
			// PPService
			$paypalService = new PayPalAPIInterfaceServiceService ();
			
			$getRPPDetailsReqest = new GetRecurringPaymentsProfileDetailsRequestType ();
			$getRPPDetailsReqest->ProfileID = $paymentProfile ['paymentProfileId'];
			$getRPPDetailsReq = new GetRecurringPaymentsProfileDetailsReq ();
			$getRPPDetailsReq->GetRecurringPaymentsProfileDetailsRequest = $getRPPDetailsReqest;
			$getRPPDetailsResponse = $paypalService->GetRecurringPaymentsProfileDetails ( $getRPPDetailsReq );
			if (empty ( $getRPPDetailsResponse ) || $getRPPDetailsResponse->Ack != 'Success') {
				throw new AppException ( 'Error retrieving payment profile status' );
			}
			$profileStatus = $getRPPDetailsResponse->GetRecurringPaymentsProfileDetailsResponseDetails->ProfileStatus;
			// Active profile, send off the cancel
			if (strcasecmp ( $profileStatus, PaymentProfileStatus::ACTIVEPROFILE ) === 0 || strcasecmp ( $profileStatus, PaymentProfileStatus::CANCELLEDPROFILE ) === 0) {
				if (strcasecmp ( $profileStatus, PaymentProfileStatus::ACTIVEPROFILE ) === 0) {
					// Do we have a payment profile, we need to cancel it with paypal
					$manageRPPStatusReqestDetails = new ManageRecurringPaymentsProfileStatusRequestDetailsType ();
					$manageRPPStatusReqestDetails->Action = 'Cancel';
					$manageRPPStatusReqestDetails->ProfileID = $paymentProfile ['paymentProfileId'];
					
					$manageRPPStatusReqest = new ManageRecurringPaymentsProfileStatusRequestType ();
					$manageRPPStatusReqest->ManageRecurringPaymentsProfileStatusRequestDetails = $manageRPPStatusReqestDetails;
					
					$manageRPPStatusReq = new ManageRecurringPaymentsProfileStatusReq ();
					$manageRPPStatusReq->ManageRecurringPaymentsProfileStatusRequest = $manageRPPStatusReqest;
					
					$manageRPPStatusResponse = $paypalService->ManageRecurringPaymentsProfileStatus ( $manageRPPStatusReq );
					if (! isset ( $manageRPPStatusResponse ) || $manageRPPStatusResponse->Ack != 'Success') {
						throw new AppException ( $manageRPPStatusResponse->Errors [0]->LongMessage );
					}
				} else {
					$log = Application::instance ()->getLogger ();
					$log->info ( sprintf ( 'Payment profile cancelled from status [%s]', $profileStatus ) );
				}
				$orderService->updatePaymentProfileState ( $paymentProfile ['profileId'], PaymentProfileStatus::CANCELLEDPROFILE );
			} else {
				throw new AppException ( sprintf ( 'Invalid payment profile status [%s] %s', $profileStatus, $paymentProfile ['paymentProfileId'] ) );
			}
			
			// Remove the recurring flag on the subscription
			if (! empty ( $subscription )) {
				$subService->updateSubscriptionRecurring ( $subscription ['subscriptionId'], false );
			}
			
			// Show unsubscribed screen
			$model->unsubscribed = true;
			return 'paymentcancel';
		} else {
			throw new AppException ( 'Invalid confirmation id' );
		}
		throw new AppException ( 'Could not cancel subscription' );
	}

}