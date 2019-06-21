<?php
namespace Destiny\Controllers;

use Destiny\Chat\ChatRedisService;
use Destiny\Commerce\DonationService;
use Destiny\Commerce\DonationStatus;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Application;
use Destiny\Common\Authentication\AuthenticationService;
use Destiny\Common\Authentication\AuthProvider;
use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\Log;
use Destiny\Common\Session\Session;
use Destiny\Common\User\UserRole;
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\FilterParams;
use Destiny\Common\Utils\FilterParamsException;
use Destiny\Common\Utils\Http;
use Destiny\Common\Utils\RandomString;
use Destiny\Common\ViewModel;
use Destiny\PayPal\PayPalApiService;
use Destiny\StreamLabs\StreamLabsAlertsType;
use Destiny\StreamLabs\StreamLabsService;
use Doctrine\DBAL\ConnectionException;
use PayPal\CoreComponentTypes\BasicAmountType;

/**
 * @Controller
 */
class DonateController {

    /**
     * @Route("/donate")
     * @HttpMethod({"GET"})
     */
    public function donateGet(ViewModel $model): string {
        $model->username = Session::hasRole(UserRole::USER) ? Session::getCredentials()->getUsername() : "";
        return 'donate';
    }

    /**
     * @Route("/donate/complete")
     * @HttpMethod({"GET"})
     */
    public function donateComplete(ViewModel $model): string {
        $model->username = Session::hasRole(UserRole::USER) ? Session::getCredentials()->getUsername() : "";
        return 'donate';
    }

    /**
     * @Route("/donate/error")
     * @HttpMethod({"GET"})
     */
    public function donateError(ViewModel $model): string {
        $model->username = Session::hasRole(UserRole::USER) ? Session::getCredentials()->getUsername() : "";
        return 'donate';
    }

    /**
     * @Route("/donate")
     * @HttpMethod({"POST"})
     * @throws ConnectionException
     */
    public function donatePost(array $params): string {
        $conn = Application::getDbConn();
        $authService = AuthenticationService::instance();

        try {
            FilterParams::required($params, 'amount');
            FilterParams::declared($params, 'message');
            $params['amount'] = floatval($params['amount']);
            if ($params['amount'] < Config::$a['commerce']['minimum_donation']) {
                throw new FilterParamsException('Only donations of $5.00 more more are accepted');
            }
            if (!Session::hasRole(UserRole::USER)) {
                FilterParams::required($params, 'username');
                $authService->validateUsername($params['username']);
            }
        } catch (Exception $e) {
            Session::setErrorBag($e->getMessage());
            return 'redirect: /donate';
        } catch (\Exception $e) {
            Session::setErrorBag('You must specify a username if you are not signed in.');
            return 'redirect: /donate';
        }

        try {
            if (Session::hasRole(UserRole::USER)) {
                $userid = Session::getCredentials()->getUserId();
                $username = Session::getCredentials()->getUsername();
            } else {
                $userid = null;
                $username = $params['username'];
            }

            $conn->beginTransaction();
            $donationService = DonationService::instance();
            $donation = $donationService->addDonation([
                'userid' => $userid,
                'username' => $username,
                'currency' => Config::$a ['commerce'] ['currency'],
                'amount' => $params['amount'],
                'status' => DonationStatus::PENDING,
                'message' => mb_substr($params['message'], 0, 200),
                'invoiceId' => RandomString::makeUrlSafe(32),
                'timestamp' => Date::getDateTime()->format('Y-m-d H:i:s')
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
            $conn->rollBack();
            Log::error('Error processing donation. ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            Session::setErrorBag('Error processing donation.');
            return 'redirect: /donate';
        }
    }

    /**
     * @Route("/donate/process")
     * @HttpMethod({"GET"})
     * @throws Exception
     */
    public function donateProcess(array $params): string {
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
            if (!empty($donation['userid']) && intval($donation['userid']) !== intval($userid)) {
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

                /** @var BasicAmountType $total */
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
                $symbol = $donation['currency'] === 'USD' ? '$': $donation['currency']; // todo hokey currency symbol lookup
                $amount = $symbol . number_format($donation['amount'], 2);
                $redisService = ChatRedisService::instance();
                $redisService->sendBroadcast(sprintf("%s has donated %s!", $username, $amount));
                if(!empty($message)) {
                    $redisService->sendBroadcast("$username said... $message");
                }
                if(Config::$a[AuthProvider::STREAMLABS]['alert_donations']) {
                    StreamLabsService::instance()->sendAlert([
                        'type' => StreamLabsAlertsType::ALERT_DONATION,
                        'message' => sprintf("*%s* has donated *%s*!", $username, $amount)
                    ]);
                }
                if(Config::$a[AuthProvider::STREAMLABS]['send_donations']) {
                    StreamLabsService::instance()->sendDonation([
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