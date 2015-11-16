<?php
namespace Destiny\Commerce;

use Destiny\Common\Service;
use Destiny\Common\Utils\Date;
use Destiny\Common\Session;
use Destiny\Common\Config;
use PayPal\Service\PayPalAPIInterfaceServiceService;
use PayPal\PayPalAPI\GetRecurringPaymentsProfileDetailsRequestType;
use PayPal\PayPalAPI\GetRecurringPaymentsProfileDetailsReq;
use Destiny\Common\Exception;
use PayPal\EBLBaseComponents\ManageRecurringPaymentsProfileStatusRequestDetailsType;
use PayPal\PayPalAPI\ManageRecurringPaymentsProfileStatusRequestType;
use PayPal\PayPalAPI\ManageRecurringPaymentsProfileStatusReq;
use PayPal\EBLBaseComponents\RecurringPaymentsProfileDetailsType;
use PayPal\EBLBaseComponents\BillingPeriodDetailsType;
use PayPal\CoreComponentTypes\BasicAmountType;
use PayPal\EBLBaseComponents\ScheduleDetailsType;
use PayPal\EBLBaseComponents\CreateRecurringPaymentsProfileRequestDetailsType;
use PayPal\PayPalAPI\CreateRecurringPaymentsProfileRequestType;
use PayPal\PayPalAPI\CreateRecurringPaymentsProfileReq;
use PayPal\PayPalAPI\GetExpressCheckoutDetailsReq;
use PayPal\PayPalAPI\GetExpressCheckoutDetailsRequestType;
use Destiny\Common\Utils\Http;
use PayPal\EBLBaseComponents\SetExpressCheckoutRequestDetailsType;
use PayPal\EBLBaseComponents\BillingAgreementDetailsType;
use PayPal\EBLBaseComponents\PaymentDetailsType;
use PayPal\PayPalAPI\SetExpressCheckoutRequestType;
use PayPal\PayPalAPI\SetExpressCheckoutReq;
use PayPal\EBLBaseComponents\PaymentDetailsItemType;
use PayPal\EBLBaseComponents\DoExpressCheckoutPaymentRequestDetailsType;
use PayPal\PayPalAPI\DoExpressCheckoutPaymentRequestType;
use PayPal\PayPalAPI\DoExpressCheckoutPaymentReq;
use PayPal\PayPalAPI\DoExpressCheckoutPaymentResponseType;

/**
 * @method static PayPalApiService instance()
 */
class PayPalApiService extends Service {
  
    /**
     * @param array $paymentProfile
     * @throws Exception
     */
    public function cancelPaymentProfile(array $paymentProfile) {
        // PPService
        $paypalService = new PayPalAPIInterfaceServiceService ();
        $getRPPDetailsRequest = new GetRecurringPaymentsProfileDetailsRequestType ();
        $getRPPDetailsRequest->ProfileID = $paymentProfile ['paymentProfileId'];
        $getRPPDetailsReq = new GetRecurringPaymentsProfileDetailsReq ();
        $getRPPDetailsReq->GetRecurringPaymentsProfileDetailsRequest = $getRPPDetailsRequest;
        $getRPPDetailsResponse = $paypalService->GetRecurringPaymentsProfileDetails ( $getRPPDetailsReq );
        if (empty ( $getRPPDetailsResponse ) || $getRPPDetailsResponse->Ack != 'Success') {
           throw new Exception ( 'Error retrieving payment profile status' );
        }
        $profileStatus = $getRPPDetailsResponse->GetRecurringPaymentsProfileDetailsResponseDetails->ProfileStatus;

        // Active profile, send off the cancel
        if (strcasecmp ( $profileStatus, PaymentProfileStatus::ACTIVE_PROFILE ) === 0 || strcasecmp ( $profileStatus, PaymentProfileStatus::CANCELLED_PROFILE ) === 0) {
            if (strcasecmp ( $profileStatus, PaymentProfileStatus::ACTIVE_PROFILE ) === 0) {
                // Do we have a payment profile, we need to cancel it with paypal
                $manageRPPStatusRequestDetails = new ManageRecurringPaymentsProfileStatusRequestDetailsType ();
                $manageRPPStatusRequestDetails->Action = 'Cancel';
                $manageRPPStatusRequestDetails->ProfileID = $paymentProfile ['paymentProfileId'];
                $manageRPPStatusRequest = new ManageRecurringPaymentsProfileStatusRequestType ();
                $manageRPPStatusRequest->ManageRecurringPaymentsProfileStatusRequestDetails = $manageRPPStatusRequestDetails;
                $manageRPPStatusReq = new ManageRecurringPaymentsProfileStatusReq ();
                $manageRPPStatusReq->ManageRecurringPaymentsProfileStatusRequest = $manageRPPStatusRequest;
                $manageRPPStatusResponse = $paypalService->ManageRecurringPaymentsProfileStatus ( $manageRPPStatusReq );
                if (! isset ( $manageRPPStatusResponse ) || $manageRPPStatusResponse->Ack != 'Success') {
                    throw new Exception ( $manageRPPStatusResponse->Errors [0]->LongMessage );
                }
            }
        }
      
    }

    /**
     * @param array $paymentProfile
     * @param string $token
     * @param array $subscriptionType
     * @return \PayPal\PayPalAPI\CreateRecurringPaymentsProfileResponseType
     */
    public function createRecurringPaymentProfile(array $paymentProfile, $token, array $subscriptionType) {
        $billingStartDate = Date::getDateTime ( $paymentProfile ['billingStartDate'] );
        
        $RPProfileDetails = new RecurringPaymentsProfileDetailsType ();
        $RPProfileDetails->SubscriberName = Session::getCredentials ()->getUsername (); // This should be passed in
        $RPProfileDetails->BillingStartDate = $billingStartDate->format ( \DateTime::ATOM );
        $RPProfileDetails->ProfileReference = $paymentProfile ['userId'] . '-' . $paymentProfile ['orderId'];
        
        $paymentBillingPeriod = new BillingPeriodDetailsType ();
        $paymentBillingPeriod->BillingFrequency = $paymentProfile ['billingFrequency'];
        $paymentBillingPeriod->BillingPeriod = $paymentProfile ['billingPeriod'];
        $paymentBillingPeriod->Amount = new BasicAmountType ( $paymentProfile ['currency'], $paymentProfile ['amount'] );
        
        $scheduleDetails = new ScheduleDetailsType ();
        $scheduleDetails->Description = $subscriptionType ['agreement'];
        $scheduleDetails->PaymentPeriod = $paymentBillingPeriod;
        
        $createRPProfileRequestDetail = new CreateRecurringPaymentsProfileRequestDetailsType ();
        $createRPProfileRequestDetail->Token = $token;
        $createRPProfileRequestDetail->ScheduleDetails = $scheduleDetails;
        $createRPProfileRequestDetail->RecurringPaymentsProfileDetails = $RPProfileDetails;
        
        $createRPProfileRequest = new CreateRecurringPaymentsProfileRequestType ();
        $createRPProfileRequest->CreateRecurringPaymentsProfileRequestDetails = $createRPProfileRequestDetail;
        $createRPProfileReq = new CreateRecurringPaymentsProfileReq ();
        $createRPProfileReq->CreateRecurringPaymentsProfileRequest = $createRPProfileRequest;
        
        $paypalService = new PayPalAPIInterfaceServiceService ();
        return $paypalService->CreateRecurringPaymentsProfile ( $createRPProfileReq );
    }

    /**
     * Execute the setExpressCheckout process, forwards to paypal
     *
     * @param string $responseUrl
     * @param array $order
     * @param array $subscriptionType
     * @return \PayPal\PayPalAPI\SetExpressCheckoutResponseType
     */
    public function getNoPaymentECResponse($responseUrl, array $order, array $subscriptionType) {
        $returnUrl = Http::getBaseUrl () . $responseUrl .'?success=true&orderId=' . urlencode ( $order ['orderId'] );
        $cancelUrl = Http::getBaseUrl () . $responseUrl .'?success=false&orderId=' . urlencode ( $order ['orderId'] );
        
        $setECReqDetails = new SetExpressCheckoutRequestDetailsType ();
        $setECReqDetails->ReqConfirmShipping = 0;
        $setECReqDetails->NoShipping = 1;
        $setECReqDetails->AllowNote = 0;
        $setECReqDetails->ReturnURL = $returnUrl;
        $setECReqDetails->CancelURL = $cancelUrl;
        $setECReqDetails->SolutionType = 'Sole';
        
        // Create billing agreement for recurring payment
        $billingAgreementDetails = new BillingAgreementDetailsType ( 'RecurringPayments' );
        $billingAgreementDetails->BillingAgreementDescription = $subscriptionType ['agreement'];
        $setECReqDetails->BillingAgreementDetails [0] = $billingAgreementDetails;
        
        $paymentDetails = new PaymentDetailsType ();
        $paymentDetails->PaymentAction = 'Sale';
        $paymentDetails->NotifyURL = Config::$a ['paypal'] ['api'] ['ipn'];
        
        $paymentDetails->OrderTotal = new BasicAmountType ( $order ['currency'], $order ['amount'] );
        $paymentDetails->ItemTotal = new BasicAmountType ( $order ['currency'], $order ['amount'] );
        $paymentDetails->Recurring = 0;
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
     * Execute the setExpressCheckout process, forwards to paypal
     *
     * @param string $responseUrl
     * @param array $order
     * @param array $subscriptionType
     * @param bool $recurring
     * @return \PayPal\PayPalAPI\SetExpressCheckoutResponseType
     */
    public function createECResponse($responseUrl, array $order, array $subscriptionType, $recurring = false) {
        // @todo should pass these urls in
        $returnUrl = Http::getBaseUrl () . $responseUrl .'?success=true&orderId=' . urlencode ( $order ['orderId'] );
        $cancelUrl = Http::getBaseUrl () . $responseUrl .'?success=false&orderId=' . urlencode ( $order ['orderId'] );
        
        $setECReqDetails = new SetExpressCheckoutRequestDetailsType ();
        $setECReqDetails->ReqConfirmShipping = 0;
        $setECReqDetails->NoShipping = 1;
        $setECReqDetails->AllowNote = 0;
        $setECReqDetails->ReturnURL = $returnUrl;
        $setECReqDetails->CancelURL = $cancelUrl;
        $setECReqDetails->SolutionType = 'Sole';
        
        if ($recurring) {
            // Create billing agreement for recurring payment
            $billingAgreementDetails = new BillingAgreementDetailsType ( 'RecurringPayments' );
            $billingAgreementDetails->BillingAgreementDescription = $subscriptionType ['agreement'];
            $setECReqDetails->BillingAgreementDetails [0] = $billingAgreementDetails;
        }
        
        $paymentDetails = new PaymentDetailsType ();
        $paymentDetails->PaymentAction = 'Sale';
        $paymentDetails->NotifyURL = Config::$a ['paypal'] ['api'] ['ipn'];
        $paymentDetails->OrderTotal = new BasicAmountType ( $order ['currency'], $order ['amount'] );
        $paymentDetails->ItemTotal = new BasicAmountType ( $order ['currency'], $order ['amount'] );
        $paymentDetails->Recurring = 0;
        $itemDetails = new PaymentDetailsItemType ();
        $itemDetails->Name = $subscriptionType ['itemLabel'];
        $itemDetails->Amount = new BasicAmountType ( $order ['currency'], $order ['amount'] );
        $itemDetails->Quantity = 1;
        // TODO this should be 'Digital' but Paypal requires you to change your account to a digital good account, which is a las
        $itemDetails->ItemCategory = 'Physical';
        $itemDetails->Number = $subscriptionType ['id'];
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
     * Retrieve the checkout instance from paypal
     *
     * @param string $token
     * @return \PayPal\PayPalAPI\GetExpressCheckoutDetailsResponseType
     */
    public function retrieveCheckoutInfo($token) {
        $paypalService = new PayPalAPIInterfaceServiceService ();
        $getExpressCheckoutReq = new GetExpressCheckoutDetailsReq ();
        $getExpressCheckoutReq->GetExpressCheckoutDetailsRequest = new GetExpressCheckoutDetailsRequestType ( $token );
        return $paypalService->GetExpressCheckoutDetails ( $getExpressCheckoutReq );
    }

    /**
     * Get express checkout payment request response
     *
     * @param string $payerId
     * @param string $token
     * @param array $order
     * @return \PayPal\PayPalAPI\DoExpressCheckoutPaymentResponseType
     */
    public function getECPaymentResponse($payerId, $token, array $order) {
        $DoECRequestDetails = new DoExpressCheckoutPaymentRequestDetailsType ();
        $DoECRequestDetails->PayerID = $payerId;
        $DoECRequestDetails->Token = $token;
        $DoECRequestDetails->PaymentAction = 'Sale';
        
        $paymentDetails = new PaymentDetailsType ();
        $paymentDetails->OrderTotal = new BasicAmountType ( $order ['currency'], $order ['amount'] );
        $paymentDetails->NotifyURL = Config::$a ['paypal'] ['api'] ['ipn'];
        $DoECRequestDetails->PaymentDetails [0] = $paymentDetails;
        
        $DoECRequest = new DoExpressCheckoutPaymentRequestType ();
        $DoECRequest->DoExpressCheckoutPaymentRequestDetails = $DoECRequestDetails;
        $DoECReq = new DoExpressCheckoutPaymentReq ();
        $DoECReq->DoExpressCheckoutPaymentRequest = $DoECRequest;
        
        $paypalService = new PayPalAPIInterfaceServiceService ();
        return $paypalService->DoExpressCheckoutPayment ( $DoECReq );
    }

}