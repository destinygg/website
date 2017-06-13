<?php
namespace Destiny\PayPal;

use Destiny\Common\Log;
use Destiny\Common\Service;
use Destiny\Common\Utils\Date;
use Destiny\Common\Config;
use PayPal\PayPalAPI\DoExpressCheckoutPaymentResponseType;
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
use PayPal\EBLBaseComponents\SetExpressCheckoutRequestDetailsType;
use PayPal\EBLBaseComponents\BillingAgreementDetailsType;
use PayPal\EBLBaseComponents\PaymentDetailsType;
use PayPal\PayPalAPI\SetExpressCheckoutRequestType;
use PayPal\PayPalAPI\SetExpressCheckoutReq;
use PayPal\EBLBaseComponents\PaymentDetailsItemType;
use PayPal\EBLBaseComponents\DoExpressCheckoutPaymentRequestDetailsType;
use PayPal\PayPalAPI\DoExpressCheckoutPaymentRequestType;
use PayPal\PayPalAPI\DoExpressCheckoutPaymentReq;

/**
 * @method static PayPalApiService instance()
 */
class PayPalApiService extends Service {

    /**
     * @param $paymentProfileId : The unique identifier paypal sending with payment responses.
     * @throws Exception
     */
    public function cancelPaymentProfile($paymentProfileId) {
        $paypalService = new PayPalAPIInterfaceServiceService ();
        $getRPPDetailsRequest = new GetRecurringPaymentsProfileDetailsRequestType ();
        $getRPPDetailsRequest->ProfileID = $paymentProfileId;
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
                $manageRPPStatusRequestDetails->ProfileID = $paymentProfileId;
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
     * @param string $token
     * @param $reference
     * @param $subscriberName
     * @param \DateTime $billingStartDate
     * @param array $subscriptionType
     * @return string $paymentProfileId
     */
    public function createRecurringPaymentProfile($token, $reference, $subscriberName, \DateTime $billingStartDate, array $subscriptionType) {
        $paymentProfileId = null;
        $amount = $subscriptionType ['amount'];
        $agreement = $subscriptionType ['agreement'];
        $currency = Config::$a ['commerce'] ['currency'];

        $RPProfileDetails = new RecurringPaymentsProfileDetailsType ();
        $RPProfileDetails->SubscriberName = $subscriberName;
        $RPProfileDetails->BillingStartDate = $billingStartDate->format ( \DateTime::ATOM );
        $RPProfileDetails->ProfileReference = $reference;
        
        $paymentBillingPeriod = new BillingPeriodDetailsType ();
        $paymentBillingPeriod->BillingFrequency = $subscriptionType ['billingFrequency'];
        $paymentBillingPeriod->BillingPeriod = $subscriptionType ['billingPeriod'];
        $paymentBillingPeriod->Amount = new BasicAmountType ( $currency, $amount );
        
        $scheduleDetails = new ScheduleDetailsType ();
        $scheduleDetails->Description = $agreement;
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
        $createRPProfileResponse = $paypalService->CreateRecurringPaymentsProfile ( $createRPProfileReq );

        if ( isset ( $createRPProfileResponse ) && $createRPProfileResponse->Ack == 'Success'){
            $paymentProfileId = $createRPProfileResponse->CreateRecurringPaymentsProfileResponseDetails->ProfileID;
        }

        return $paymentProfileId;
    }

    /**
     * Create an ExpressCheckout @ paypal before doing a 302 redirect
     *
     * @param string $returnUrl
     * @param string $cancelUrl
     * @param array $subscriptionType
     * @param boolean $recurring
     * @return null|string
     */
    public function createSubscribeECResponse($returnUrl, $cancelUrl, array $subscriptionType, $recurring = false) {

        $token = null;
        $amount = $subscriptionType ['amount'];
        $agreement = $subscriptionType ['agreement'];
        $currency = Config::$a ['commerce'] ['currency'];

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
            $billingAgreementDetails->BillingAgreementDescription = $agreement;
            $setECReqDetails->BillingAgreementDetails [0] = $billingAgreementDetails;
        }
        
        $paymentDetails = new PaymentDetailsType ();
        $paymentDetails->PaymentAction = 'Sale';
        $paymentDetails->NotifyURL = Config::$a ['paypal'] ['api'] ['ipn'];
        $paymentDetails->OrderTotal = new BasicAmountType ( $currency, $amount );
        $paymentDetails->ItemTotal = new BasicAmountType ( $currency, $amount );
        $paymentDetails->Recurring = 0;
        $setECReqDetails->PaymentDetails [0] = $paymentDetails;

        $itemDetails = new PaymentDetailsItemType ();
        $itemDetails->Name = $subscriptionType ['itemLabel'];
        $itemDetails->Amount = new BasicAmountType ( $currency, $amount );
        $itemDetails->Quantity = 1;
        $itemDetails->ItemCategory = 'Physical'; // TODO this should be 'Digital' but Paypal requires you to change your account to a digital good account, which is a las
        $itemDetails->Number = $subscriptionType ['id'];
        $paymentDetails->PaymentDetailsItem [0] = $itemDetails;
        
        // Paypal UI settings
        $setECReqDetails->BrandName = Config::$a['meta']['title'];
        
        // Execute checkout
        $setECReqType = new SetExpressCheckoutRequestType ();
        $setECReqType->SetExpressCheckoutRequestDetails = $setECReqDetails;
        $setECReq = new SetExpressCheckoutReq ();
        $setECReq->SetExpressCheckoutRequest = $setECReqType;
        $paypalService = new PayPalAPIInterfaceServiceService ();
        /** @var \PayPal\PayPalAPI\SetExpressCheckoutResponseType $response */
        $response = $paypalService->SetExpressCheckout ( $setECReq );

        if ($response->Ack == 'Success') {
            $token = $response->Token;
        } else {
            /** @var \PayPal\EBLBaseComponents\ErrorType $errors */
            $errors = $response->Errors;
            Log::critical("Error getting checkout response: " . $errors->ShortMessage );
        }

        return $token;
    }

    /**
     * Create an ExpressCheckout @ paypal before doing a 302 redirect
     *
     * @param string $returnUrl
     * @param string $cancelUrl
     * @param array $donation
     * @return null|string
     */
    public function createDonateECResponse($returnUrl, $cancelUrl, array $donation){
        $token = null;
        $amount = $donation ['amount'];
        $label = "$amount donation";
        $currency = Config::$a ['commerce'] ['currency'];

        $setECReqDetails = new SetExpressCheckoutRequestDetailsType ();
        $setECReqDetails->BrandName = Config::$a['meta']['title'];
        $setECReqDetails->ReqConfirmShipping = 0;
        $setECReqDetails->NoShipping = 1;
        $setECReqDetails->AllowNote = 0;
        $setECReqDetails->ReturnURL = $returnUrl;
        $setECReqDetails->CancelURL = $cancelUrl;
        $setECReqDetails->SolutionType = 'Sole';

        $paymentDetails = new PaymentDetailsType ();
        $paymentDetails->PaymentAction = 'Sale';
        $paymentDetails->NotifyURL = Config::$a ['paypal'] ['api'] ['ipn'];
        $paymentDetails->OrderTotal = new BasicAmountType ( $currency, $amount );
        $paymentDetails->ItemTotal = new BasicAmountType ( $currency, $amount );
        $paymentDetails->Recurring = 0;

        $paymentDetails = new PaymentDetailsType ();
        $paymentDetails->PaymentAction = 'Sale';
        $paymentDetails->NotifyURL = Config::$a ['paypal'] ['api'] ['ipn'];
        $paymentDetails->OrderTotal = new BasicAmountType ( $currency, $amount );
        $paymentDetails->ItemTotal = new BasicAmountType ( $currency, $amount );
        $setECReqDetails->PaymentDetails [0] = $paymentDetails;

        $itemDetails = new PaymentDetailsItemType ();
        $itemDetails->Name = $label;
        $itemDetails->Amount = new BasicAmountType ( $currency, $amount );
        $itemDetails->Quantity = 1;
        $itemDetails->ItemCategory = 'Physical'; // TODO this should be 'Digital' but Paypal requires you to change your account to a digital good account, which is a las
        $itemDetails->Number = -1;
        $paymentDetails->PaymentDetailsItem [0] = $itemDetails;

        // Execute checkout
        $setECReqType = new SetExpressCheckoutRequestType ();
        $setECReqType->SetExpressCheckoutRequestDetails = $setECReqDetails;
        $setECReq = new SetExpressCheckoutReq ();
        $setECReq->SetExpressCheckoutRequest = $setECReqType;
        $paypalService = new PayPalAPIInterfaceServiceService ();
        /** @var \PayPal\PayPalAPI\SetExpressCheckoutResponseType $response */
        $response = $paypalService->SetExpressCheckout ($setECReq);

        if ($response->Ack == 'Success') {
            $token = $response->Token;
        } else {
            /** @var \PayPal\EBLBaseComponents\ErrorType $errors */
            $errors = $response->Errors;
            Log::critical("Error getting checkout response: " . $errors->ShortMessage );
        }
        return $token;
    }

    /**
     * Retrieve the checkout instance from paypal
     *
     * @param string $token
     * @return null|\PayPal\PayPalAPI\GetExpressCheckoutDetailsResponseType
     */
    public function retrieveCheckoutInfo($token) {
        $paypalService = new PayPalAPIInterfaceServiceService ();
        $getExpressCheckoutReq = new GetExpressCheckoutDetailsReq ();
        $getExpressCheckoutReq->GetExpressCheckoutDetailsRequest = new GetExpressCheckoutDetailsRequestType ( $token );
        $response = $paypalService->GetExpressCheckoutDetails ( $getExpressCheckoutReq );
        return (isset ( $response ) && $response->Ack == 'Success') ? $response : null;
    }

    /**
     * Get express checkout payment request response
     *
     * @param string $payerId
     * @param string $token
     * @param $amount
     * @return DoExpressCheckoutPaymentResponseType
     */
    public function getECPaymentResponse($payerId, $token, $amount) {
        $currency = Config::$a ['commerce'] ['currency'];

        $DoECRequestDetails = new DoExpressCheckoutPaymentRequestDetailsType ();
        $DoECRequestDetails->PayerID = $payerId;
        $DoECRequestDetails->Token = $token;
        $DoECRequestDetails->PaymentAction = 'Sale';
        
        $paymentDetails = new PaymentDetailsType ();
        $paymentDetails->OrderTotal = new BasicAmountType ( $currency, $amount );
        $paymentDetails->NotifyURL = Config::$a ['paypal'] ['api'] ['ipn'];
        $DoECRequestDetails->PaymentDetails [0] = $paymentDetails;
        
        $DoECRequest = new DoExpressCheckoutPaymentRequestType ();
        $DoECRequest->DoExpressCheckoutPaymentRequestDetails = $DoECRequestDetails;
        $DoECReq = new DoExpressCheckoutPaymentReq ();
        $DoECReq->DoExpressCheckoutPaymentRequest = $DoECRequest;
        
        $paypalService = new PayPalAPIInterfaceServiceService ();
        return $paypalService->DoExpressCheckoutPayment ( $DoECReq );
    }

    /**
     * @param DoExpressCheckoutPaymentResponseType $DoECResponse
     * @return array <array>
     */
    public function getResponsePayments(DoExpressCheckoutPaymentResponseType $DoECResponse){
        $payments = array();
        if (isset ( $DoECResponse ) && $DoECResponse->Ack == 'Success') {
            /** @var \PayPal\EBLBaseComponents\DoExpressCheckoutPaymentResponseDetailsType $details */
            $details = $DoECResponse->DoExpressCheckoutPaymentResponseDetails;
            if (isset ($details->PaymentInfo) && !empty($details->PaymentInfo)) {
                /** @var \PayPal\EBLBaseComponents\PaymentInfoType $payments */
                $payments = $details->PaymentInfo;
                for ($i = 0; $i < count($payments); ++$i) {
                    $paymentInfo = $payments [$i];
                    /** @var \PayPal\CoreComponentTypes\BasicAmountType $amount */
                    $amount = $paymentInfo->GrossAmount;
                    $payment = array ();
                    $payment ['amount'] = $amount->value;
                    $payment ['currency'] = $amount->currencyID;
                    $payment ['transactionId'] = $paymentInfo->TransactionID;
                    $payment ['transactionType'] = $paymentInfo->TransactionType;
                    $payment ['paymentType'] = $paymentInfo->PaymentType;
                    $payment ['paymentStatus'] = $paymentInfo->PaymentStatus;
                    $payment ['paymentDate'] = Date::getDateTime ( $paymentInfo->PaymentDate )->format ( 'Y-m-d H:i:s' );
                    $payments[] = $payment;
                }
            }
        }
        return $payments;
    }

}