<?php
namespace Destiny\PayPal;

use DateTime;
use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\Service;
use Destiny\Common\Utils\Date;
use PayPal\CoreComponentTypes\BasicAmountType;
use PayPal\EBLBaseComponents\BillingAgreementDetailsType;
use PayPal\EBLBaseComponents\BillingPeriodDetailsType;
use PayPal\EBLBaseComponents\CreateRecurringPaymentsProfileRequestDetailsType;
use PayPal\EBLBaseComponents\DoExpressCheckoutPaymentRequestDetailsType;
use PayPal\EBLBaseComponents\ManageRecurringPaymentsProfileStatusRequestDetailsType;
use PayPal\EBLBaseComponents\PaymentDetailsItemType;
use PayPal\EBLBaseComponents\PaymentDetailsType;
use PayPal\EBLBaseComponents\RecurringPaymentsProfileDetailsType;
use PayPal\EBLBaseComponents\ScheduleDetailsType;
use PayPal\EBLBaseComponents\SetExpressCheckoutRequestDetailsType;
use PayPal\PayPalAPI\CreateRecurringPaymentsProfileReq;
use PayPal\PayPalAPI\CreateRecurringPaymentsProfileRequestType;
use PayPal\PayPalAPI\DoExpressCheckoutPaymentReq;
use PayPal\PayPalAPI\DoExpressCheckoutPaymentRequestType;
use PayPal\PayPalAPI\DoExpressCheckoutPaymentResponseType;
use PayPal\PayPalAPI\GetExpressCheckoutDetailsReq;
use PayPal\PayPalAPI\GetExpressCheckoutDetailsRequestType;
use PayPal\PayPalAPI\GetExpressCheckoutDetailsResponseType;
use PayPal\PayPalAPI\GetRecurringPaymentsProfileDetailsReq;
use PayPal\PayPalAPI\GetRecurringPaymentsProfileDetailsRequestType;
use PayPal\PayPalAPI\ManageRecurringPaymentsProfileStatusReq;
use PayPal\PayPalAPI\ManageRecurringPaymentsProfileStatusRequestType;
use PayPal\PayPalAPI\SetExpressCheckoutReq;
use PayPal\PayPalAPI\SetExpressCheckoutRequestType;
use PayPal\Service\PayPalAPIInterfaceServiceService;

/**
 * @method static PayPalApiService instance()
 */
class PayPalApiService extends Service {

    private function getConfig(): array {
        return Config::$a['paypal']['sdk'];
    }

    /**
     * @param $paymentProfileId : The unique identifier paypal sending with payment responses.
     * @throws Exception
     */
    public function cancelPaymentProfile(string $paymentProfileId) {
        $paypalService = new PayPalAPIInterfaceServiceService ($this->getConfig());
        $getRPPDetailsRequest = new GetRecurringPaymentsProfileDetailsRequestType ();
        $getRPPDetailsRequest->ProfileID = $paymentProfileId;
        $getRPPDetailsReq = new GetRecurringPaymentsProfileDetailsReq ();
        $getRPPDetailsReq->GetRecurringPaymentsProfileDetailsRequest = $getRPPDetailsRequest;

        try {
            $getRPPDetailsResponse = $paypalService->GetRecurringPaymentsProfileDetails($getRPPDetailsReq);
            if (empty($getRPPDetailsResponse) || $getRPPDetailsResponse->Ack != 'Success') {
                throw new Exception ('Error retrieving payment profile status');
            }
        } catch (\Exception $e) {
            throw new Exception("Error cancelling payment profile.", $e);
        }

        $profileStatus = $getRPPDetailsResponse->GetRecurringPaymentsProfileDetailsResponseDetails->ProfileStatus;
        // Active profile, send off the cancel
        if (strcasecmp($profileStatus, PaymentProfileStatus::ACTIVE_PROFILE) === 0 || strcasecmp($profileStatus, PaymentProfileStatus::CANCELLED_PROFILE) === 0) {
            if (strcasecmp($profileStatus, PaymentProfileStatus::ACTIVE_PROFILE) === 0) {
                // Do we have a payment profile, we need to cancel it with paypal
                $statusRequestType = new ManageRecurringPaymentsProfileStatusRequestType();
                $statusRequestType->ManageRecurringPaymentsProfileStatusRequestDetails = new ManageRecurringPaymentsProfileStatusRequestDetailsType($paymentProfileId, 'Cancel');
                $statusRequest = new ManageRecurringPaymentsProfileStatusReq();
                $statusRequest->ManageRecurringPaymentsProfileStatusRequest = $statusRequestType;
                try {
                    $res = $paypalService->ManageRecurringPaymentsProfileStatus($statusRequest);
                } catch (\Exception $e) {
                    throw new Exception("Could not cancel active profile.", $e);
                }
                if (!$res || $res->Ack != 'Success') {
                    throw new Exception($res->Errors[0]->LongMessage);
                }
            }
        }
    }

    /**
     * @return string|mixed
     * @throws Exception
     */
    public function createSubscriptionPaymentProfile(string $token, string $reference, string $subscriberName, DateTime $billingStartDate, array $subscriptionType = []) {
        $paypalService = new PayPalAPIInterfaceServiceService($this->getConfig());
        $paymentProfileId = null;
        $amount = $subscriptionType ['amount'];
        $agreement = $subscriptionType ['agreement'];
        $currency = Config::$a ['commerce'] ['currency'];

        $RPProfileDetails = new RecurringPaymentsProfileDetailsType($billingStartDate->format(DateTime::ATOM));
        $RPProfileDetails->SubscriberName = $subscriberName;
        $RPProfileDetails->ProfileReference = $reference;

        $paymentBillingPeriod = new BillingPeriodDetailsType($subscriptionType ['billingPeriod'], $subscriptionType ['billingFrequency'], new BasicAmountType ($currency, $amount));
        $scheduleDetails = new ScheduleDetailsType($agreement, $paymentBillingPeriod);

        $createRPProfileRequestDetail = new CreateRecurringPaymentsProfileRequestDetailsType($RPProfileDetails, $scheduleDetails);
        $createRPProfileRequestDetail->Token = $token;

        $createRPProfileRequest = new CreateRecurringPaymentsProfileRequestType();
        $createRPProfileRequest->CreateRecurringPaymentsProfileRequestDetails = $createRPProfileRequestDetail;
        $createRPProfileReq = new CreateRecurringPaymentsProfileReq();
        $createRPProfileReq->CreateRecurringPaymentsProfileRequest = $createRPProfileRequest;

        try {
            $createRPProfileResponse = $paypalService->CreateRecurringPaymentsProfile($createRPProfileReq);
            if (!empty($createRPProfileResponse) && $createRPProfileResponse->Ack == 'Success') {
                $paymentProfileId = $createRPProfileResponse->CreateRecurringPaymentsProfileResponseDetails->ProfileID;
            }
        } catch (\Exception $e) {
            throw new Exception("Error creating subscription payment.", $e);
        }

        return $paymentProfileId;
    }

    /**
     * Create an ExpressCheckout @ paypal before doing a 302 redirect
     * @return null|string
     * @throws Exception
     */
    public function createSubscribeECRequest(string $returnUrl, string $cancelUrl, array $subscriptionType = [], $recurring = false, int $quantity = 1, string $giftee = null) {
        $paypalService = new PayPalAPIInterfaceServiceService ($this->getConfig());

        $token = null;
        $amount = $subscriptionType['amount'];
        $agreement = $subscriptionType['agreement'];
        $currency = Config::$a['commerce']['currency'];
        $totalAmount = number_format($amount * $quantity, 2);

        $details = new SetExpressCheckoutRequestDetailsType();
        $details->BrandName = Config::$a['meta']['title'];
        $details->SolutionType = 'Sole';
        $details->ReqConfirmShipping = 0;
        $details->NoShipping = 1;
        $details->AllowNote = 0;
        $details->ReturnURL = $returnUrl;
        $details->CancelURL = $cancelUrl;

        if (!empty($giftee)) {
            $details->Custom = json_encode(['g' => $giftee]);
        }

        if ($recurring) {
            // Create billing agreement for recurring payment
            $billingAgreementDetails = new BillingAgreementDetailsType('RecurringPayments');
            $billingAgreementDetails->BillingAgreementDescription = $agreement;
            $details->BillingAgreementDetails[0] = $billingAgreementDetails;
        }

        $payment = new PaymentDetailsType();
        $payment->PaymentAction = 'Sale';
        $payment->NotifyURL = Config::$a['paypal']['endpoint_ipn'];
        $payment->OrderTotal = new BasicAmountType($currency, $totalAmount);
        $payment->ItemTotal = new BasicAmountType($currency, $totalAmount);
        $payment->Recurring = 0;
        $details->PaymentDetails [0] = $payment;

        $item = new PaymentDetailsItemType();
        $item->Name = $subscriptionType ['itemLabel'];
        $item->Amount = new BasicAmountType($currency, $amount); // The cost of a single subscription.
        $item->Quantity = $quantity;
        $item->ItemCategory = 'Physical'; // or 'Physical'. TODO this should be 'Digital' but Paypal requires you to change your account to a digital good account, which is a las
        $item->Number = $subscriptionType ['id'];
        $payment->PaymentDetailsItem [0] = $item;

        // Execute checkout
        $setECReqType = new SetExpressCheckoutRequestType($details);
        $setECReq = new SetExpressCheckoutReq();
        $setECReq->SetExpressCheckoutRequest = $setECReqType;

        try {
            $response = $paypalService->SetExpressCheckout($setECReq);
            if (empty($response) || $response->Ack != 'Success') {
                throw new Exception("Error getting checkout response: " . $response->Errors->ShortMessage);
            }
            return $response->Token;
        } catch (\Exception $e) {
            throw new Exception("Error creating subscription payment request", $e);
        }
    }

    /**
     * Complete a PayPal Express Checkout subscription transaction.
     *
     * @param GetExpressCheckoutDetailsResponseType $checkoutInfo The details of the transaction. Obtain this value with `PayPalApiService->retrieveCheckoutInfo()`.
     * @return DoExpressCheckoutPaymentResponseType An object with details on the completed transaction.
     * @throws Exception
     */
    public function completeSubscribeECTransaction(GetExpressCheckoutDetailsResponseType $checkoutInfo) {
        $paypalService = new PayPalAPIInterfaceServiceService($this->getConfig());

        $responseDetails = $checkoutInfo->GetExpressCheckoutDetailsResponseDetails;

        $doECRequestDetails = new DoExpressCheckoutPaymentRequestDetailsType();
        $doECRequestDetails->PayerID = $responseDetails->PayerInfo->PayerID;
        $doECRequestDetails->Token = $responseDetails->Token;
        $doECRequestDetails->PaymentAction = $responseDetails->PaymentDetails[0]->PaymentAction;
        $doECRequestDetails->PaymentDetails = $responseDetails->PaymentDetails;

        $doECRequest = new DoExpressCheckoutPaymentRequestType($doECRequestDetails);
        $doECReq = new DoExpressCheckoutPaymentReq();
        $doECReq->DoExpressCheckoutPaymentRequest = $doECRequest;

        try {
            return $paypalService->DoExpressCheckoutPayment($doECReq);
        } catch (\Exception $e) {
            throw new Exception("Error completing subscription checkout transaction.", $e);
        }
    }

    /**
     * Create an ExpressCheckout @ paypal before doing a 302 redirect
     * @return null|string
     * @throws Exception
     */
    public function createDonateECRequest(string $returnUrl, string $cancelUrl, array $donation = []){
        $paypalService = new PayPalAPIInterfaceServiceService ($this->getConfig());
        $token = null;
        $amount = $donation ['amount'];
        $currency = Config::$a ['commerce'] ['currency'];

        $item = new PaymentDetailsItemType();
        $item->Name = "$amount donation";
        $item->Amount = new BasicAmountType($currency, $amount);
        $item->Quantity = 1;
        $item->ItemCategory = 'Physical'; // or 'Physical'. TODO this should be 'Digital' but Paypal requires you to change your account to a digital good account, which is a las
        $item->Number = $donation['id'];

        $payment = new PaymentDetailsType();
        $payment->PaymentAction = 'Sale';
        $payment->ItemTotal = new BasicAmountType ($currency, $amount);
        $payment->NotifyURL = Config::$a['paypal']['endpoint_ipn'];
        $payment->OrderTotal = new BasicAmountType ($currency, $amount);
        $payment->ItemTotal = new BasicAmountType ($currency, $amount);
        $payment->PaymentDetailsItem[0] = $item;

        $details = new SetExpressCheckoutRequestDetailsType();
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
        $response = null;
        try {
            $requestType = new SetExpressCheckoutRequestType($details);
            $request = new SetExpressCheckoutReq();
            $request->SetExpressCheckoutRequest = $requestType;
            $response = $paypalService->SetExpressCheckout($request);
            if (!empty($response) && $response->Ack == 'Success') {
                return $response->Token;
            }
            throw new Exception($response->Errors->ShortMessage);
        } catch (\Exception $e) {
            throw new Exception("Error getting checkout response. {$e->getMessage()}");
        }
    }

    /**
     * Retrieve the checkout instance from paypal
     * @return GetExpressCheckoutDetailsResponseType
     * @throws Exception
     */
    public function retrieveCheckoutInfo(string $token) {
        try {
            $paypalService = new PayPalAPIInterfaceServiceService($this->getConfig());
            $getExpressCheckoutReq = @(new GetExpressCheckoutDetailsReq());
            $getExpressCheckoutReq->GetExpressCheckoutDetailsRequest = new GetExpressCheckoutDetailsRequestType($token);
            $response = $paypalService->GetExpressCheckoutDetails($getExpressCheckoutReq);
            if (!empty($response) && $response->Ack == 'Success') {
                return $response;
            }
        } catch (\Exception $e) {
            throw new Exception("Error retrieving check-out information. {$e->getMessage()}");
        }
        throw new Exception("Error retrieving check-out information.");
    }

    /**
     * Get express checkout payment request response
     * @throws Exception
     */
    public function getCheckoutPaymentResponse(string $payerId, string $token, $amount): DoExpressCheckoutPaymentResponseType {
        $paypalService = new PayPalAPIInterfaceServiceService($this->getConfig());
        $currency = Config::$a['commerce']['currency'];

        $DoECRequestDetails = new DoExpressCheckoutPaymentRequestDetailsType();
        $DoECRequestDetails->PayerID = $payerId;
        $DoECRequestDetails->Token = $token;
        $DoECRequestDetails->PaymentAction = 'Sale';

        $paymentDetails = new PaymentDetailsType();
        $paymentDetails->OrderTotal = new BasicAmountType($currency, $amount);
        $paymentDetails->NotifyURL = Config::$a['paypal']['endpoint_ipn'];
        $DoECRequestDetails->PaymentDetails[0] = $paymentDetails;

        $DoECRequest = new DoExpressCheckoutPaymentRequestType($DoECRequestDetails);
        $DoECReq = new DoExpressCheckoutPaymentReq();
        $DoECReq->DoExpressCheckoutPaymentRequest = $DoECRequest;
        try {
            return $paypalService->DoExpressCheckoutPayment($DoECReq);
        } catch (\Exception $e) {
            throw new Exception("Error getting checkout response.", $e);
        }
    }

    public function getCheckoutResponsePayments(DoExpressCheckoutPaymentResponseType $DoECResponse): array {
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

    public function extractSubscriptionInfoFromCheckoutResponse(GetExpressCheckoutDetailsResponseType $checkoutResponse): array {
        $subscriptionInfo = [];

        $checkoutDetails = $checkoutResponse->GetExpressCheckoutDetailsResponseDetails;
        $paymentDetails = $checkoutDetails->PaymentDetails[0];

        // Somehow this property is a string despite the DocBlock saying it's a
        // bool.
        $subscriptionInfo['recurring'] = ($checkoutDetails->BillingAgreementAcceptedStatus ?? 'false') === 'true';

        // Extract the username of the giftee if it exists.
        if (!empty($checkoutDetails->Custom)) {
            $customField = json_decode($checkoutDetails->Custom, true);
            if (!empty($customField['g'])) {
                $subscriptionInfo['giftee'] = $customField['g'];
            }
        }

        $subscriptionInfo['subscriptionId'] = $paymentDetails->PaymentDetailsItem[0]->Number;
        $subscriptionInfo['quantity'] = $paymentDetails->PaymentDetailsItem[0]->Quantity;

        return $subscriptionInfo;
    }
}