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
     * @return array
     */
    private function getConfig(){
        return Config::$a['paypal']['sdk'];
    }

    /**
     * @param $paymentProfileId : The unique identifier paypal sending with payment responses.
     * @throws \Exception
     */
    public function cancelPaymentProfile($paymentProfileId) {
        $paypalService = new PayPalAPIInterfaceServiceService ($this->getConfig());
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
     *
     * @throws \Exception
     */
    public function createSubscriptionPaymentProfile($token, $reference, $subscriberName, \DateTime $billingStartDate, array $subscriptionType) {
        $paypalService = new PayPalAPIInterfaceServiceService ($this->getConfig());
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
     *
     * @throws \Exception
     */
    public function createSubscribeECRequest($returnUrl, $cancelUrl, array $subscriptionType, $recurring = false) {
        $paypalService = new PayPalAPIInterfaceServiceService ($this->getConfig());

        $token = null;
        $amount = $subscriptionType ['amount'];
        $agreement = $subscriptionType ['agreement'];
        $currency = Config::$a ['commerce'] ['currency'];

        $details = new SetExpressCheckoutRequestDetailsType ();
        $details->BrandName = Config::$a['meta']['title'];
        $details->SolutionType = 'Sole';
        $details->ReqConfirmShipping = 0;
        $details->NoShipping = 1;
        $details->AllowNote = 0;
        $details->ReturnURL = $returnUrl;
        $details->CancelURL = $cancelUrl;

        if ($recurring) {
            // Create billing agreement for recurring payment
            $billingAgreementDetails = new BillingAgreementDetailsType ( 'RecurringPayments' );
            $billingAgreementDetails->BillingAgreementDescription = $agreement;
            $details->BillingAgreementDetails [0] = $billingAgreementDetails;
        }

        $payment = new PaymentDetailsType ();
        $payment->PaymentAction = 'Sale';
        $payment->NotifyURL = Config::$a['paypal']['endpoint_ipn'];
        $payment->OrderTotal = new BasicAmountType ( $currency, $amount );
        $payment->ItemTotal = new BasicAmountType ( $currency, $amount );
        $payment->Recurring = 0;
        $details->PaymentDetails [0] = $payment;

        $item = new PaymentDetailsItemType ();
        $item->Name = $subscriptionType ['itemLabel'];
        $item->Amount = new BasicAmountType ( $currency, $amount );
        $item->Quantity = 1;
        $item->ItemCategory = 'Physical'; // or 'Physical'. TODO this should be 'Digital' but Paypal requires you to change your account to a digital good account, which is a las
        $item->Number = $subscriptionType ['id'];
        $payment->PaymentDetailsItem [0] = $item;
        
        // Execute checkout
        $setECReqType = new SetExpressCheckoutRequestType ();
        $setECReqType->SetExpressCheckoutRequestDetails = $details;
        $setECReq = new SetExpressCheckoutReq ();
        $setECReq->SetExpressCheckoutRequest = $setECReqType;
        $response = $paypalService->SetExpressCheckout ( $setECReq );
        if ($response->Ack == 'Success') {
            $token = $response->Token;
        } else {
            Log::critical("Error getting checkout response: " . $response->Errors->ShortMessage );
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
     *
     * @throws \Exception
     */
    public function createDonateECRequest($returnUrl, $cancelUrl, array $donation){
        $paypalService = new PayPalAPIInterfaceServiceService ($this->getConfig());
        $token = null;
        $amount = $donation ['amount'];
        $currency = Config::$a ['commerce'] ['currency'];

        $item = new PaymentDetailsItemType ();
        $item->Name = "$amount donation";
        $item->Amount = new BasicAmountType ($currency, $amount);
        $item->Quantity = 1;
        $item->ItemCategory = 'Physical'; // or 'Physical'. TODO this should be 'Digital' but Paypal requires you to change your account to a digital good account, which is a las
        $item->Number = $donation['id'];

        $payment = new PaymentDetailsType ();
        $payment->PaymentAction = 'Sale';
        $payment->ItemTotal = new BasicAmountType ($currency, $amount);
        $payment->NotifyURL = Config::$a['paypal']['endpoint_ipn'];
        $payment->OrderTotal = new BasicAmountType ($currency, $amount);
        $payment->ItemTotal = new BasicAmountType ($currency, $amount);
        $payment->PaymentDetailsItem[0] = $item;

        $details = new SetExpressCheckoutRequestDetailsType ();
        $details->BrandName = Config::$a['meta']['title'];
        $details->SolutionType = 'Sole';
        $details->ReqConfirmShipping = 0;
        $details->NoShipping = 1;
        $details->AllowNote = 0;
        $details->ReturnURL = $returnUrl;
        $details->CancelURL = $cancelUrl;
        $details->InvoiceID = $donation['invoiceId'];
        $details->PaymentDetails[0] = $payment;

        // Execute checkout
        $requestType = new SetExpressCheckoutRequestType ();
        $requestType->SetExpressCheckoutRequestDetails = $details;
        $request = new SetExpressCheckoutReq ();
        $request->SetExpressCheckoutRequest = $requestType;
        $response = $paypalService->SetExpressCheckout($request);
        if ($response->Ack == 'Success') {
            $token = $response->Token;
        } else {
            $errors = $response->Errors;
            Log::critical("Error getting checkout response: " . $errors->ShortMessage);
        }
        return $token;
    }

    /**
     * Retrieve the checkout instance from paypal
     *
     * @param string $token
     * @return null|\PayPal\PayPalAPI\GetExpressCheckoutDetailsResponseType
     *
     * @throws \Exception
     */
    public function retrieveCheckoutInfo($token) {
        $paypalService = new PayPalAPIInterfaceServiceService ($this->getConfig());
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
     *
     * @throws \Exception
     */
    public function getCheckoutPaymentResponse($payerId, $token, $amount) {
        $paypalService = new PayPalAPIInterfaceServiceService ($this->getConfig());
        $currency = Config::$a ['commerce'] ['currency'];

        $DoECRequestDetails = new DoExpressCheckoutPaymentRequestDetailsType ();
        $DoECRequestDetails->PayerID = $payerId;
        $DoECRequestDetails->Token = $token;
        $DoECRequestDetails->PaymentAction = 'Sale';
        
        $paymentDetails = new PaymentDetailsType ();
        $paymentDetails->OrderTotal = new BasicAmountType ( $currency, $amount );
        $paymentDetails->NotifyURL = Config::$a['paypal']['endpoint_ipn'];
        $DoECRequestDetails->PaymentDetails [0] = $paymentDetails;
        
        $DoECRequest = new DoExpressCheckoutPaymentRequestType ();
        $DoECRequest->DoExpressCheckoutPaymentRequestDetails = $DoECRequestDetails;
        $DoECReq = new DoExpressCheckoutPaymentReq ();
        $DoECReq->DoExpressCheckoutPaymentRequest = $DoECRequest;
        return $paypalService->DoExpressCheckoutPayment ( $DoECReq );
    }

    /**
     * @param DoExpressCheckoutPaymentResponseType $DoECResponse
     * @return array
     */
    public function getCheckoutResponsePayments(DoExpressCheckoutPaymentResponseType $DoECResponse){
        $payments = [];
        if (isset ( $DoECResponse ) && $DoECResponse->Ack == 'Success') {
            $details = $DoECResponse->DoExpressCheckoutPaymentResponseDetails;
            if (isset ($details->PaymentInfo) && !empty($details->PaymentInfo)) {
                foreach($details->PaymentInfo as $paymentInfo) {
                    $payment = [];
                    $payment ['amount'] = $paymentInfo->GrossAmount->value;
                    $payment ['currency'] = $paymentInfo->GrossAmount->currencyID;
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