<?php
namespace Destiny\Controllers;

use Destiny\Commerce\PaymentStatus;
use Destiny\Commerce\SubscriptionsService;
use Destiny\Common\Response;
use Destiny\Common\Utils\Http;
use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\Utils\Date;
use Destiny\Common\Exception;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Commerce\OrdersService;
use PayPal\IPN\PPIPNMessage;

/**
 * @Controller
 */
class IpnController {

    /**
     * @Route ("/ipn")
     *
     * Handles the incoming HTTP request
     * @return Response
     */
    public function ipn() {
        $log = Application::instance()->getLogger();
        try {
            $ipnMessage = new PPIPNMessage ();
            if (!$ipnMessage->validate()) {
                $log->error('Got a invalid IPN ' . json_encode($ipnMessage->getRawData()));
                return new Response (Http::STATUS_ERROR, 'Got a invalid IPN');
            }
            $data = $ipnMessage->getRawData();
            $log->info(sprintf('Got a valid IPN [txn_id: %s, txn_type: %s]', $ipnMessage->getTransactionId(), $data ['txn_type']));
            $orderService = OrdersService::instance();
            $orderService->addIPNRecord(array(
                'ipnTrackId' => $data ['ipn_track_id'],
                'ipnTransactionId' => $data ['txn_id'],
                'ipnTransactionType' => $data ['txn_type'],
                'ipnData' => json_encode($data, JSON_UNESCAPED_UNICODE)
            ));

            // Make sure this IPN is for the merchant
            if (strcasecmp(Config::$a ['commerce'] ['receiver_email'], $data ['receiver_email']) !== 0) {
                $log->critical(sprintf('IPN originated with incorrect receiver_email [%s]', $data ['ipn_track_id']));
                return new Response (Http::STATUS_ERROR, 'Invalid IPN');
            }

            // Handle the IPN
            $this->handleIPNTransaction($data ['txn_id'], $data ['txn_type'], $data);

            // Return success response
            return new Response (Http::STATUS_OK);

        } catch (\Exception $e) {

            $log->critical($e->getMessage());
            return new Response (Http::STATUS_ERROR, 'Error');

        }
    }

    /**
     * @param string $txnId
     * @param string $txnType
     * @param array $data
     * @throws Exception
     */
    protected function handleIPNTransaction($txnId, $txnType, array $data) {

        $log = Application::instance()->getLogger();
        $orderService = OrdersService::instance();
        $subscriptionsService = SubscriptionsService::instance();

        switch (strtoupper($txnType)) {

            // This is sent when a express checkout has been performed by a user
            // We need to handle the case where orders go through, but have pending payments.
            case 'EXPRESS_CHECKOUT' :
                $payment = $orderService->getPaymentByTransactionId($txnId);
                if (!empty ($payment)) {

                    // Make sure the payment values are the same
                    if (number_format($payment ['amount'], 2) != number_format($data ['mc_gross'], 2)) {
                        throw new Exception ('Amount for payment do not match');
                    }

                    // Update the payment status
                    $orderService->updatePayment(array(
                        'paymentId' => $payment ['paymentId'],
                        'paymentStatus' => $data ['payment_status']
                    ));

                    // Update the subscription paymentStatus to active (may have been pending)
                    // TODO we set the paymentStatus to active without checking it because we get the opportunity to check it after subscription completion
                    $subscription = $subscriptionsService->getSubscriptionById ( $payment ['subscriptionId'] );
                    if (!empty ($subscription)) {
                        $subscriptionsService->updateSubscription(array(
                            'subscriptionId' => $subscription['subscriptionId'],
                            'paymentStatus' => PaymentStatus::ACTIVE
                        ));
                    }

                } else {
                    $log->info(sprintf('Express checkout IPN called, but no payment found [%s]', $txnId));
                }
                break;

            // This is sent from paypal when a recurring payment is billed
            case 'RECURRING_PAYMENT' :
                if (!isset ($data ['payment_status']))
                    throw new Exception ('Invalid payment status');
                if (!isset ($data ['next_payment_date']))
                    throw new Exception ('Invalid next_payment_date');

                $nextPaymentDate = Date::getDateTime($data ['next_payment_date']);
                $subscription = $this->getSubscriptionByPaymentProfileData( $data );
                $subscriptionsService->updateSubscription(array(
                    'subscriptionId' => $subscription['subscriptionId'],
                    'billingNextDate' => $nextPaymentDate->format('Y-m-d H:i:s'),
                    'paymentStatus' => PaymentStatus::ACTIVE
                ));

                $orderService->addPayment(array(
                    'subscriptionId'  => $subscription ['subscriptionId'],
                    'payerId'         => $data ['payer_id'],
                    'amount'          => $data ['mc_gross'],
                    'currency'        => $data ['mc_currency'],
                    'transactionId'   => $txnId,
                    'transactionType' => $txnType,
                    'paymentType'     => $data ['payment_type'],
                    'paymentStatus'   => $data ['payment_status'],
                    'paymentDate'     => Date::getDateTime($data ['payment_date'])->format('Y-m-d H:i:s'),
                ));
                $log->notice(sprintf('Added order payment %s status %s', $data ['recurring_payment_id'], $data ['profile_status']));
                break;

            case 'RECURRING_PAYMENT_SKIPPED':
                $subscription = $this->getSubscriptionByPaymentProfileData( $data );
                $subscriptionsService->updateSubscription(array (
                    'subscriptionId' => $subscription['subscriptionId'],
                    'paymentStatus' => PaymentStatus::SKIPPED
                ));
                $log->debug(sprintf('Payment skipped %s', $data ['recurring_payment_id']));
                break;

            case 'RECURRING_PAYMENT_PROFILE_CANCEL' :
                $subscription = $this->getSubscriptionByPaymentProfileData( $data );
                $subscriptionsService->updateSubscription(array (
                    'subscriptionId' => $subscription['subscriptionId'],
                    'paymentStatus' => PaymentStatus::CANCELLED
                ));
                $log->debug(sprintf('Payment profile cancelled %s status %s', $data ['recurring_payment_id'], $data ['profile_status']));
                break;

            case 'RECURRING_PAYMENT_FAILED' :
                $subscription = $this->getSubscriptionByPaymentProfileData( $data );
                $subscriptionsService->updateSubscription(array (
                    'subscriptionId' => $subscription['subscriptionId'],
                    'paymentStatus' => PaymentStatus::FAILED
                ));
                $log->debug(sprintf('Payment profile cancelled %s status %s', $data ['recurring_payment_id'], $data ['profile_status']));
                break;

            // Sent on first post-back when the user subscribes
            case 'RECURRING_PAYMENT_PROFILE_CREATED' :
                $subscription = $this->getSubscriptionByPaymentProfileData( $data );
                $subscriptionsService->updateSubscription(array (
                    'subscriptionId' => $subscription['subscriptionId'],
                    'paymentStatus' => PaymentStatus::ACTIVE
                ));
                $log->debug(sprintf('Updated payment profile %s status %s', $data ['recurring_payment_id'], $data ['profile_status']));
                break;
        }
    }

    /**
     * @param array $data
     * @return array|null
     * @throws Exception
     */
    protected function getSubscriptionByPaymentProfileData( array $data ){
        $subscription = null;
        if (isset ($data ['recurring_payment_id']) && !empty ($data ['recurring_payment_id'])) {
            $subscriptionService = SubscriptionsService::instance();
            $subscription = $subscriptionService->getSubscriptionByPaymentProfileId( $data ['recurring_payment_id'] );
        }
        if(empty($subscription)){
            $log = Application::instance()->getLogger();
            $log->critical('Could not load subscription using IPN', $data);
            throw new Exception( 'Could not load subscription by payment data' );
        }
        return $subscription;
    }

}