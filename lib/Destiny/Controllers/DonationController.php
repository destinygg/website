<?php
namespace Destiny\Controllers;

use Destiny\Chat\ChatEmotes;
use Destiny\Chat\ChatIntegrationService;
use Destiny\Commerce\DonationService;
use Destiny\Commerce\DonationStatus;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\Log;
use Destiny\Common\Session;
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\FilterParams;
use Destiny\Common\Utils\Http;
use Destiny\PayPal\PayPalApiService;
use Destiny\StreamLabs\StreamLabsAlertsType;
use Destiny\StreamLabs\StreamLabsService;
use Doctrine\DBAL\DBALException;

/**
 * @Controller
 */
class DonationController {

    /**
     * @Route("/donate")
     * @HttpMethod({"GET"})
     *
     * @return string
     */
    public function donateGet(){
        return 'donate';
    }

    /**
     * @Route("/donate/complete")
     * @Secure({"USER"})
     * @HttpMethod({"GET"})
     *
     * @return string
     */
    public function donateComplete(){
        return 'donate';
    }

    /**
     * @Route("/donate/error")
     * @Secure({"USER"})
     * @HttpMethod({"GET"})
     *
     * @return string
     */
    public function donateError(){
        return 'donate';
    }

    /**
     * @Route("/donate")
     * @Secure({"USER"})
     * @HttpMethod({"POST"})
     *
     * @param array $params
     * @return string
     *
     * @throws Exception
     * @throws DBALException
     */
    public function donatePost(array $params){
        FilterParams::required($params, 'amount');
        FilterParams::declared($params, 'message');
        $donationService = DonationService::instance();
        $userId = Session::getCredentials ()->getUserId ();
        $conn = Application::getDbConn();
        try {
            $conn->beginTransaction();
            $donation = $donationService->addDonation([
                'userid'    => $userId,
                'currency'  => Config::$a ['commerce'] ['currency'],
                'amount'    => floatval($params['amount']),
                'status'    => DonationStatus::PENDING,
                'message'   => mb_substr($params['message'], 0, 250),
                'timestamp' => Date::getDateTime ()->format ( 'Y-m-d H:i:s' )
            ]);
            $payPalApiService = PayPalApiService::instance();
            $returnUrl = Http::getBaseUrl() . '/donate/process?success=true&donationid=' . urlencode($donation['id']);
            $cancelUrl = Http::getBaseUrl() . '/donate/process?success=false&donationid=' . urlencode($donation['id']);
            $token = $payPalApiService->createDonateECRequest($returnUrl, $cancelUrl, $donation);
            if (empty ($token)) {
                throw new Exception ('Error getting paypal response');
            }
            $conn->commit();
            return 'redirect: ' . Config::$a['paypal']['endpoint_checkout'] . urlencode($token);
        } catch (\Exception $e) {
            Log::critical(new Exception("Failed to create order", $e));
            $conn->rollBack();
            return 'redirect: /donate/error';
        }
    }

    /**
     * @Route("/donate/process")
     * @Secure({"USER"})
     * @HttpMethod({"GET"})
     *
     * @param array $params
     * @return string
     *
     * @throws Exception
     */
    public function donateProcess(array $params){
        FilterParams::required($params, 'donationid');
        FilterParams::required($params, 'token');
        FilterParams::declared($params, 'success');
        $creds = Session::getCredentials();
        $donationService = DonationService::instance();
        try {
            $donation = $donationService->findById($params['donationid']);
            if (empty($donation) || $donation['status'] !== DonationStatus::PENDING) {
                throw new Exception ('Invalid donation');
            }
            if (intval($donation['userid']) !== intval($creds->getUserId ())) {
                throw new Exception ('Permission to donation denied');
            }
            if ($params ['success'] == '0' || $params ['success'] == 'false' || $params ['success'] === false) {
                throw new Exception ('Donation failed');
            }
            try {
                $payPalApiService = PayPalApiService::instance();
                $checkinfo = $payPalApiService->retrieveCheckoutInfo($params ['token']);
                if ($checkinfo === null) {
                    throw new Exception ('Failed to retrieve express checkout details');
                }

                /** @var \PayPal\CoreComponentTypes\BasicAmountType $total */
                $total = $checkinfo->GetExpressCheckoutDetailsResponseDetails->PaymentDetails[0]->OrderTotal;
                if (strcasecmp($total->currencyID, $donation['currency']) !== 0 || number_format($total->value, 2) !== number_format($donation['amount'], 2)) {
                    throw new Exception ('Invalid donation amount');
                }

                // Record the payments
                $DoECResponse = $payPalApiService->getCheckoutPaymentResponse($params ['PayerID'], $params ['token'], $donation['amount']);
                $payments = $payPalApiService->getCheckoutResponsePayments($DoECResponse);
                if (count($payments) > 0) {
                    foreach ($payments as $payment) {
                        $payment['donationId'] = $params['donationid'];
                        $payment['payerId'] = $params ['PayerID'];
                        $donationService->addPayment($payment);
                    }
                }

                $donationService->updateDonation($params['donationid'], ['status' => DonationStatus::COMPLETED]);
                Session::setSuccessBag('Donation successful! Thank you');
            } catch (\Exception $e) {
                $donationService->updateDonation($params['donationid'], ['status' => DonationStatus::ERROR]);
                throw new Exception ('Invalid payment result', $e);
            }
            try {
                $amount = $donation['currency'] . number_format($donation['amount'], 2);
                $emote = $randomEmote = ChatEmotes::random('destiny');
                $chatService = ChatIntegrationService::instance();
                $chatService->sendBroadcast(sprintf("%s has donated %s! %s", $creds->getUsername(), $amount, $emote)); // todo $ currency symbol
                if(Config::$a['streamlabs']['alert_donations']) {
                    StreamLabsService::instance()->sendAlert([
                        'type' => StreamLabsAlertsType::ALERT_DONATION,
                        'message' => sprintf("*%s* has donated *%s*!", $creds->getUsername(), $amount)
                    ]);
                }
                if(Config::$a['streamlabs']['send_donations']) {
                    StreamLabsService::instance()->sendDonation([
                        'name'          => $creds->getUsername(),
                        'message'       => $donation['message'],
                        'identifier'    => $creds->getUsername() .'#' . $creds->getUserId(),
                        'amount'        => number_format($donation['amount'], 2),
                        'currency'      => $donation['currency']
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error sending donation broadcast. ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            Log::error('Error processing donation. ' . $e->getMessage());
            Session::setErrorBag('Error processing donation.');
            return 'redirect: /donate/error';
        }
        return 'redirect: /donate/complete';
    }
}