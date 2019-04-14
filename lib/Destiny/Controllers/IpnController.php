<?php
namespace Destiny\Controllers;

use Destiny\Commerce\PaymentStatus;
use Destiny\Commerce\SubscriptionsService;
use Destiny\Common\Annotation\ResponseBody;
use Destiny\Common\Application;
use Destiny\Common\Log;
use Destiny\Common\Request;
use Destiny\Common\Response;
use Destiny\Common\Utils\Http;
use Destiny\Common\Config;
use Destiny\Common\Utils\Date;
use Destiny\Common\Exception;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Commerce\OrdersService;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\DBALException;
use PayPal\IPN\PPIPNMessage;

/**
 * @Controller
 */
class IpnController {

    /**
     * @Route ("/ipn")
     * @ResponseBody
     *
     * Handles the incoming HTTP request
     *
     * @param Request $request
     * @param Response $response
     * @return string
     */
    public function ipn(Request $request, Response $response) {
        try {
            $body = $request->getBody();
            $ipnMessage = new PPIPNMessage ($body, Config::$a['paypal']['sdk']);
            if (!$ipnMessage->validate()) {
                Log::error('Got a invalid IPN ' . $body, $request->headers);
                $response->setStatus(Http::STATUS_BAD_REQUEST);
                return 'invalid_ipn';
            }
            try {
                $data = $ipnMessage->getRawData();
                Log::info('Got a valid IPN [txn_id: {txn_id}, txn_type: {txn_type}]', $data);
                $orderService = OrdersService::instance();
                $orderService->addIpnRecord([
                    'ipnTrackId' => $data ['ipn_track_id'],
                    'ipnTransactionId' => $data ['txn_id'],
                    'ipnTransactionType' => $data ['txn_type'],
                    'ipnData' => json_encode($data, JSON_UNESCAPED_UNICODE)
                ]);
            } catch (\Exception $e) {
                Log::critical('Could not save IPN Record');
                throw $e;
            }
            // Handle the IPN
            // TODO should be handled asynchronously
            $this->handleIPNTransaction($data);
            //
        } catch (Exception $e) {
            Log::error($e);
        } catch (\Exception $e) {
            Log::critical($e);
        }
        return 'ok';
    }

    /**
     * @param array $data
     *
     * @throws ConnectionException
     * @throws DBALException
     * @throws Exception
     */
    protected function handleIPNTransaction(array $data) {
        $txnId = $data ['txn_id'];
        $txnType = $data ['txn_type'];
        $orderService = OrdersService::instance();
        $subscriptionsService = SubscriptionsService::instance();
        $conn = Application::getDbConn();
        switch (strtoupper($txnType)) {

            // This is sent when a express checkout has been performed by a user
            // We need to handle the case where orders go through, but have pending payments.
            case 'EXPRESS_CHECKOUT' :
                $this->checkTransactionRecipientEmail($data);
                $payment = $orderService->getPaymentByTransactionId($txnId);
                if (!empty ($payment)) {
                    // Make sure the payment values are the same
                    if (number_format($payment ['amount'], 2) != number_format($data ['mc_gross'], 2)) {
                        throw new Exception ('Amount for payment do not match');
                    }
                    $subscription = $subscriptionsService->findById($payment ['subscriptionId']);
                    try {
                        // Update the payment status and subscription paymentStatus to active (may have been pending)
                        $conn->beginTransaction();
                        $orderService->updatePayment([
                            'paymentId' => $payment ['paymentId'],
                            'paymentStatus' => $data ['payment_status']
                        ]);
                        if (!empty ($subscription)) {
                            $subscriptionsService->updateSubscription([
                                'subscriptionId' => $subscription['subscriptionId'],
                                'paymentStatus' => PaymentStatus::ACTIVE
                            ]);
                        }
                        $conn->commit();
                    } catch (DBALException $e) {
                        $conn->rollBack();
                        throw $e;
                    }
                } else {
                    Log::warn('Express checkout IPN called, but no payment found {txn_id}', $data);
                }
                break;

            // This is sent from paypal when a recurring payment is billed
            case 'RECURRING_PAYMENT' :
                $this->checkTransactionRecipientEmail($data);
                if (!isset ($data ['payment_status']))
                    throw new Exception ('Invalid payment status');
                if (!isset ($data ['next_payment_date']))
                    throw new Exception ('Invalid next_payment_date');

                $nextPaymentDate = Date::getDateTime($data ['next_payment_date']);
                $subscription = $this->getSubscriptionByPaymentProfileData($data);
                try {
                    $conn->beginTransaction();
                    $subscriptionsService->updateSubscription([
                        'subscriptionId' => $subscription['subscriptionId'],
                        'billingNextDate' => $nextPaymentDate->format('Y-m-d H:i:s'),
                        'paymentStatus' => PaymentStatus::ACTIVE
                    ]);
                    $orderService->addPayment([
                        'subscriptionId' => $subscription ['subscriptionId'],
                        'payerId' => $data ['payer_id'],
                        'amount' => $data ['mc_gross'],
                        'currency' => $data ['mc_currency'],
                        'transactionId' => $txnId,
                        'transactionType' => $txnType,
                        'paymentType' => $data ['payment_type'],
                        'paymentStatus' => $data ['payment_status'],
                        'paymentDate' => Date::getDateTime($data ['payment_date'])->format('Y-m-d H:i:s'),
                    ]);
                    $conn->commit();
                } catch (DBALException $e) {
                    $conn->rollBack();
                    throw $e;
                }
                Log::notice('Added order payment {recurring_payment_id} status {profile_status}', $data);
                break;

            case 'RECURRING_PAYMENT_SKIPPED':
                $this->checkTransactionRecipientEmail($data);
                $subscription = $this->findSubscriptionByPaymentProfileData($data);
                if (!empty($subscription)) {
                    $subscriptionsService->updateSubscription([
                        'subscriptionId' => $subscription['subscriptionId'],
                        'paymentStatus' => PaymentStatus::SKIPPED
                    ]);
                    Log::debug('Payment skipped {recurring_payment_id}', $data);
                }
                break;

            case 'RECURRING_PAYMENT_PROFILE_CANCEL' :
                $this->checkTransactionRecipientEmail($data);
                $subscription = $this->findSubscriptionByPaymentProfileData($data);
                if (!empty($subscription)) {
                    $subscriptionsService->updateSubscription([
                        'subscriptionId' => $subscription['subscriptionId'],
                        'paymentStatus' => PaymentStatus::CANCELLED
                    ]);
                    Log::debug('Payment profile cancelled {recurring_payment_id} status {profile_status}', $data);
                }
                break;

            case 'RECURRING_PAYMENT_FAILED' :
                $this->checkTransactionRecipientEmail($data);
                $subscription = $this->findSubscriptionByPaymentProfileData($data);
                if (!empty($subscription)) {
                    $subscriptionsService->updateSubscription([
                        'subscriptionId' => $subscription['subscriptionId'],
                        'paymentStatus' => PaymentStatus::FAILED
                    ]);
                    Log::debug('Payment profile cancelled {recurring_payment_id} status {profile_status}', $data);
                }
                break;

            // Sent on first post-back when the user subscribes
            case 'RECURRING_PAYMENT_PROFILE_CREATED' :
                $this->checkTransactionRecipientEmail($data);
                $subscription = $this->getSubscriptionByPaymentProfileData($data);
                $subscriptionsService->updateSubscription([
                    'subscriptionId' => $subscription['subscriptionId'],
                    'paymentStatus' => PaymentStatus::ACTIVE
                ]);
                Log::debug('Updated payment profile {recurring_payment_id} status {profile_status}', $data);
                break;

            case 'ADJUSTMENT':
                Log::debug('Received payment adjustment'. $data['reason_code'], $data);
                break;
        }
    }

    /**
     * @param array $data
     * @return array|null
     * @throws DBALException
     * @throws Exception
     */
    protected function getSubscriptionByPaymentProfileData(array $data) {
        $subscription = null;
        $paymentId = $data['recurring_payment_id'] ?? null;
        if (!empty($paymentId)) {
            $subscriptionService = SubscriptionsService::instance();
            $subscription = $subscriptionService->findByPaymentProfileId($paymentId);
        }
        if (empty($subscription)) {
            throw new Exception("Could not load subscription using IPN [#$paymentId]");
        }
        return $subscription;
    }

    /**
     * @param array $data
     * @return array|null
     * @throws DBALException
     */
    protected function findSubscriptionByPaymentProfileData(array $data) {
        try {
            return $this->getSubscriptionByPaymentProfileData($data);
        } catch (Exception $e) {
            Log::warn($e->getMessage());
        }
        return null;
    }

    /**
     * @param array $data
     * @throws Exception
     */
    private function checkTransactionRecipientEmail(array $data) {
        $email = $data['receiver_email'] ?? null;
        if (empty($email) || strcasecmp(Config::$a['commerce']['receiver_email'], $email) !== 0) {
            throw new Exception("IPN originated with incorrect receiver_email [$email]");
        }
    }

}