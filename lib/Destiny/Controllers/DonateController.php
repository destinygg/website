<?php
namespace Destiny\Controllers;

use Destiny\Chat\ChatEmotes;
use Destiny\Chat\ChatIntegrationService;
use Destiny\Commerce\DonationService;
use Destiny\Commerce\DonationStatus;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Application;
use Destiny\Common\Authentication\AuthenticationService;
use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\Log;
use Destiny\Common\Session;
use Destiny\Common\User\UserRole;
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\FilterParams;
use Destiny\Common\Utils\Http;
use Destiny\Common\ViewModel;
use Destiny\PayPal\PayPalApiService;
use Destiny\StreamLabs\StreamLabsAlertsType;
use Destiny\StreamLabs\StreamLabsService;
use Doctrine\DBAL\DBALException;

/**
 * @Controller
 */
class DonateController {

    /**
     * @Route("/donate")
     * @HttpMethod({"GET"})
     *
     * @param ViewModel $model
     * @return string
     */
    public function donateGet(ViewModel $model){
        $model->username = Session::hasRole(UserRole::USER) ? Session::getCredentials()->getUsername() : "";
        return 'donate';
    }

    /**
     * @Route("/donate/complete")
     * @HttpMethod({"GET"})
     *
     * @param ViewModel $model
     * @return string
     */
    public function donateComplete(ViewModel $model){
        $model->username = Session::hasRole(UserRole::USER) ? Session::getCredentials()->getUsername() : "";
        return 'donate';
    }

    /**
     * @Route("/donate/error")
     * @HttpMethod({"GET"})
     *
     * @return string
     */
    public function donateError(){
        return 'donate';
    }

    /**
     * @Route("/donate")
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
        FilterParams::required($params, 'username');
        FilterParams::declared($params, 'message');
        $conn = Application::getDbConn();
        try {

            $amount = floatval($params['amount']);
            $minimum = Config::$a['commerce']['minimum_donation'];
            if ($amount < $minimum) {
                Session::setErrorBag('Only donations of $5.00 and over can be accepted');
                throw new Exception ('Minimum donation amount not met');
            }

            AuthenticationService::instance()->validateUsername($params['username']);
            $userid = Session::hasRole(UserRole::USER) ? Session::getCredentials()->getUserId() : -1;
            $conn->beginTransaction();
            $donationService = DonationService::instance();
            $donation = $donationService->addDonation([
                'userid'    => $userid,
                'username'  => $params['username'],
                'currency'  => Config::$a ['commerce'] ['currency'],
                'amount'    => $amount,
                'status'    => DonationStatus::PENDING,
                'message'   => mb_substr($params['message'], 0, 200),
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
        try {
            $donationService = DonationService::instance();
            $donation = $donationService->findById($params['donationid']);
            if (empty($donation) || $donation['status'] !== DonationStatus::PENDING) {
                throw new Exception ('Invalid donation');
            }
            $username = $donation['username'];
            $userid = Session::hasRole(UserRole::USER) ? Session::getCredentials()->getUserId() :  -1;
            if (intval($donation['userid']) !== intval($userid)) {
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
                Session::setSuccessBag('Your donation was successful, thanks!');
            } catch (\Exception $e) {
                $donationService->updateDonation($params['donationid'], ['status' => DonationStatus::ERROR]);
                throw new Exception ('Invalid payment result', $e);
            }
            try {
                $message = $donation['message'];
                $symbol = $donation['currency'] === 'USD'? '$': $donation['currency']; // todo hokey currency symbol lookup
                $amount = $symbol . number_format($donation['amount'], 2);
                $emote = $randomEmote = ChatEmotes::random('destiny');
                $chatService = ChatIntegrationService::instance();
                $chatService->sendBroadcast(sprintf("%s has donated %s! %s", $username, $amount, $emote));
                if(!empty($message)) {
                    $chatService->sendBroadcast("$username said... $message");
                }
                if(Config::$a['streamlabs']['alert_donations']) {
                    StreamLabsService::withAuth()->sendAlert([
                        'type' => StreamLabsAlertsType::ALERT_DONATION,
                        'message' => sprintf("*%s* has donated *%s*!", $username, $amount)
                    ]);
                }
                if(Config::$a['streamlabs']['send_donations']) {
                    StreamLabsService::withAuth()->sendDonation([
                        'name'          => $username,
                        'message'       => $donation['message'],
                        'identifier'    => $username .'#' . $userid,
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