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
use Destiny\StreamLabs\StreamLabsService;

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
     * @throws \Exception
     */
    public function donatePost(array $params){
        FilterParams::required($params, 'amount');
        FilterParams::declared($params, 'message');
        $conn = Application::instance()->getConnection();
        $conn->beginTransaction();
        try {
            $userId = Session::getCredentials ()->getUserId ();
            $donationService = DonationService::instance();
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
            $token = $payPalApiService->createDonateECResponse($returnUrl, $cancelUrl, $donation);
            if (empty ($token)) {
                throw new Exception ('Error getting paypal response');
            }
            $conn->commit();
            return 'redirect: ' . Config::$a ['paypal'] ['api'] ['endpoint'] . urlencode($token);
        } catch (\Exception $e) {
            Log::critical('Error creating order');
            $conn->rollBack();
        }
        return 'redirect: /donate/error';
    }

    /**
     * @Route("/donate/process")
     * @Secure({"USER"})
     * @HttpMethod({"GET"})
     *
     * @param array $params
     * @return string
     * @throws \Exception
     */
    public function donateProcess(array $params){
        FilterParams::required($params, 'donationid');
        FilterParams::required($params, 'token');
        FilterParams::declared($params, 'success');
        try {
            if ($params ['success'] == '0' || $params ['success'] == 'false' || $params ['success'] === false) {
                throw new Exception ('Donation failed');
            }
            try {
                $payPalApiService = PayPalApiService::instance();
                $checkinfo = $payPalApiService->retrieveCheckoutInfo($params ['token']);
                if ($checkinfo === null) {
                    throw new Exception ('Failed to retrieve express checkout details');
                }
                /** @var \PayPal\EBLBaseComponents\GetExpressCheckoutDetailsResponseDetailsType $details */
                $details = $checkinfo->GetExpressCheckoutDetailsResponseDetails;
                /** @var \PayPal\EBLBaseComponents\PaymentDetailsType $payment */
                $payment = $details->PaymentDetails[0];
                /** @var \PayPal\CoreComponentTypes\BasicAmountType $total */
                $total = $payment->OrderTotal;
            } catch (\Exception $e) {
                throw new Exception ('Invalid payment result', $e);
            }

            $creds = Session::getCredentials();
            $donationService = DonationService::instance();
            $donation = $donationService->findById($params['donationid']);
            if (empty($donation)) {
                throw new Exception ('Invalid donation');
            }
            if (intval($donation['userid']) !== intval($creds->getUserId ())) {
                throw new Exception ('Permission to donation denied');
            }
            if (strcasecmp($total->currencyID, $donation['currency']) !== 0 || number_format($total->value, 2) !== number_format($donation['amount'], 2)) {
                throw new Exception ('Invalid donation details');
            }
            $donationService->updateDonation($params['donationid'], ['status' => DonationStatus::COMPLETED]);

            try {
                $emote = $randomEmote = ChatEmotes::random('destiny');
                $chatService = ChatIntegrationService::instance();
                $chatService->sendBroadcast(sprintf("%s has donated %s! %s", $creds->getUsername(), '$' . number_format($donation['amount'], 2), $emote)); // todo $ currency symbol
                $streamLabService = StreamLabsService::instance();
                $streamLabService->useDefaultAuth();
                $streamLabService->sendDonation([
                    'name'          => $creds->getUsername(),
                    'message'       => $donation['message'],
                    'identifier'    => $creds->getUsername() .'#' . $creds->getUserId(),
                    'amount'        => number_format($donation['amount'], 2),
                    'currency'      => $donation['currency']
                ]);
                Session::setSuccessBag('Donation successful! Thank you');
            } catch (\Exception $e) {
                Log::error('Error sending donation broadcast. ' . $e->getMessage());
                Session::setErrorBag('Error sending donation broadcast.');
            }
        } catch (\Exception $e) {
            Log::error('Error processing donation. ' . $e->getMessage());
            Session::setErrorBag('Error processing donation.');
            return 'redirect: /donate/error';
        }
        return 'redirect: /donate/complete';
    }
}