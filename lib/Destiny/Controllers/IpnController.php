<?php
namespace Destiny\Controllers;

use Destiny\Common\Response;
use Destiny\Commerce\SubscriptionStatus;
use Destiny\Commerce\OrderStatus;
use Destiny\Commerce\PaymentStatus;
use Destiny\Common\Utils\Http;
use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\Utils\Date;
use Destiny\Common\Exception;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\Transactional;
use Destiny\Common\Authentication\AuthenticationService;
use Destiny\Commerce\OrdersService;
use Destiny\Commerce\SubscriptionsService;
use PayPal\IPN\PPIPNMessage;

/**
 * @Controller
 */
class IpnController {

    /**
     * @Route ("/ipn")
     * @Transactional
     *
     * Handles the incoming HTTP request
     * @param array $params
     */
    public function ipn(array $params) {
      $log = Application::instance ()->getLogger ();
      try {
        $ipnMessage = new PPIPNMessage ();
        if (! $ipnMessage->validate ()) {
          $log->error ( 'Got a invalid IPN ' . json_encode ( $ipnMessage->getRawData () ) );
          return new Response ( Http::STATUS_ERROR, 'Got a invalid IPN' );
        }
        $data = $ipnMessage->getRawData ();
        $log->info ( sprintf ( 'Got a valid IPN [txn_id: %s, txn_type: %s]', $ipnMessage->getTransactionId (), $data ['txn_type'] ) );
        $orderService = OrdersService::instance ();
        $orderService->addIPNRecord ( array (
          'ipnTrackId' => $data ['ipn_track_id'],
          'ipnTransactionId' => $data ['txn_id'],
          'ipnTransactionType' => $data ['txn_type'],
          'ipnData' => json_encode ( $data, JSON_UNESCAPED_UNICODE ) 
        ) );
        
        // Make sure this IPN is for the merchant
        if (strcasecmp ( Config::$a ['commerce'] ['receiver_email'], $data ['receiver_email'] ) !== 0) {
          $log->critical ( sprintf('IPN originated with incorrect receiver_email [%s]', $data ['ipn_track_id']) );
          return new Response ( Http::STATUS_ERROR, 'Invalid IPN' );
        }

        // Handle the IPN
        $this->handleIPNTransaction ( $data ['txn_id'], $data ['txn_type'], $data );

        // Return success response
        return new Response ( Http::STATUS_OK );

      } catch ( \Exception $e ) {

        $log->critical ( $e->getMessage () );
        return new Response ( Http::STATUS_ERROR, 'Error' );

      }
      
      $log->critical ( 'Unhandled IPN' );
      return new Response ( Http::STATUS_ERROR, 'Unhandled IPN' );
    }

    /**
     * Handles the IPN message
     *
     * @param PPIPNMessage $ipnMessage
     */
    protected function handleIPNTransaction($txnId, $txnType, array $data) {
      
      $log = Application::instance ()->getLogger ();
      $orderService = OrdersService::instance ();
      $subService = SubscriptionsService::instance ();
      $authService = AuthenticationService::instance ();
      
      switch (strtolower ( $txnType )) {
        
        // Post back from checkout, make sure the payment lines up
        // This is sent when a express checkout has been performed by a user
        case 'express_checkout' :
          
          $payment = $orderService->getPaymentByTransactionId ( $txnId );
          if (! empty ( $payment )) {
            
            // Make sure the payment values are the same
            if (number_format ( $payment ['amount'], 2 ) != number_format ( $data ['mc_gross'], 2 )) {
              throw new Exception ( 'Amount for payment do not match' );
            }
            
            // Update the payment status
            $orderService->updatePaymentStatus ( $payment ['paymentId'], $data ['payment_status'] );
            $log->notice ( sprintf ( 'Updated payment status %s status %s', $data ['txn_id'], $data ['payment_status'] ) );
            
            // If the payment status WAS PENDING, and the IPN payment status is COMPLETED
            // Then we need to activate the attached subscription and complete the order
            // This is for the ECHECK payment method
            if (strcasecmp ( $payment ['paymentStatus'], PaymentStatus::PENDING ) === 0 && strcasecmp ( $data ['payment_status'], PaymentStatus::COMPLETED ) === 0) {
              $order = $orderService->getOrderByPaymentId ( $payment ['paymentId'] );
              if (! empty ( $order )) {
                $orderService->updateOrderState ( $order ['orderId'], OrderStatus::COMPLETED );
                $log->debug ( sprintf ( 'Updated order status %s status %s', $order ['orderId'], OrderStatus::COMPLETED ) );
                $subscription = $subService->getUserPendingSubscription ( $order ['userId'] );
                if (! empty ( $subscription )) {
                  $subService->updateSubscriptionState ( $subscription ['subscriptionId'], SubscriptionStatus::ACTIVE );
                  $log->notice ( sprintf ( 'Updated subscription status %s status %s', $order ['orderId'], SubscriptionStatus::ACTIVE ) );
                  $authService->flagUserForUpdate ( $subscription ['userId'] );
                }
              }
            }
          }else{
            $log->info ( sprintf ( 'Express checkout IPN called, but no payment found [%s]', $txnId ) );
          }
          break;
        
        // Recurring payment, renew subscriptions, or set to pending depending on the type
        // This is sent from paypal when a recurring payment is billed
        case 'recurring_payment' :
          
          if (! isset ( $data ['payment_status'] )) {
            throw new Exception ( 'Invalid payment status' );
          }
          if (! isset ( $data ['next_payment_date'] )) {
            throw new Exception ( 'Invalid next_payment_date' );
          }
          
          $paymentProfile = $this->getPaymentProfile ( $data );
          
          // We dont care about what state the sub is in.... 
          $subscription = $subService->getSubscriptionByOrderId ( $paymentProfile ['orderId'] );

          if (empty ( $subscription )) {
            $log->critical ( 'Invalid recurring_payment', $data );
            throw new Exception ( 'Invalid subscription for recurring payment' );
          }

          if($subscription['userId'] != $paymentProfile ['userId'] && $subscription['gifter'] != $paymentProfile ['userId']){
            throw new Exception ( sprintf ('Invalid subscription for user %s', $subscription['userId']) );
          }
          
          $nextPaymentDate = Date::getDateTime ( $data ['next_payment_date'] );
          $orderService->updatePaymentProfileNextPayment ( $paymentProfile ['profileId'], $nextPaymentDate );
          
          // Update the subscription end date regardless if the payment was successful or not
          // We dont actually know if paypal moves the automatic payment forward if one fails and is then manually processed
          $end = Date::getDateTime ( $subscription ['endDate'] );
          $end->modify ( '+' . $paymentProfile ['billingFrequency'] . ' ' . strtolower ( $paymentProfile ['billingPeriod'] ) );
          
          // Update subscription end-date
          $subService->updateSubscriptionDateEnd ( $subscription ['subscriptionId'], $end );
          $log->debug ( sprintf ( 'Update Subscription end date %s [%s]', $subscription ['subscriptionId'], $end->format ( Date::FORMAT ) ) );
          
          // Change the subscription state depending on the payment state
          if (strcasecmp ( $data ['payment_status'], PaymentStatus::PENDING ) === 0) {
            $subService->updateSubscriptionState ( $subscription ['subscriptionId'], SubscriptionStatus::PENDING );
            $log->debug ( sprintf ( 'Updated subscription state %s status %s', $subscription ['subscriptionId'], SubscriptionStatus::PENDING ) );
          } else if (strcasecmp ( $data ['payment_status'], PaymentStatus::COMPLETED ) === 0) {
            $subService->updateSubscriptionState ( $subscription ['subscriptionId'], SubscriptionStatus::ACTIVE );
            $log->debug ( sprintf ( 'Updated subscription %s status %s', $subscription ['subscriptionId'], SubscriptionStatus::ACTIVE ) );
          }else{
            $log->notice ( sprintf ( 'Subscription status %s not changed for payment profile %s', $subscription ['subscriptionId'], $paymentProfile ['profileId'] ) );
          }
          
          // Add a payment to the order
          $payment = array ();
          $payment ['orderId'] = $paymentProfile ['orderId'];
          $payment ['payerId'] = $data ['payer_id'];
          $payment ['amount'] = $data ['mc_gross'];
          $payment ['currency'] = $data ['mc_currency'];
          $payment ['transactionId'] = $txnId;
          $payment ['transactionType'] = $txnType;
          $payment ['paymentType'] = $data ['payment_type'];
          $payment ['paymentStatus'] = $data ['payment_status'];
          $payment ['paymentDate'] = Date::getDateTime ( $data ['payment_date'] )->format ( 'Y-m-d H:i:s' );
          $orderService->addOrderPayment ( $payment );
          $log->notice ( sprintf ( 'Added order payment %s status %s', $data ['recurring_payment_id'], $data ['profile_status'] ) );
          $authService->flagUserForUpdate ( $subscription ['userId'] );
          break;
        
        // Sent if user cancels subscription from Paypal's site.
        case 'recurring_payment_profile_cancel' :
          $paymentProfile = $this->getPaymentProfile ( $data );
          $orderService->updatePaymentProfileState ( $paymentProfile ['profileId'], $data ['profile_status'] );
          $log->debug ( sprintf ( 'Payment profile cancelled %s status %s', $data ['recurring_payment_id'], $data ['profile_status'] ) );
          break;
        
        // sent on first postback when the user subscribes
        case 'recurring_payment_profile_created' :
          $paymentProfile = $this->getPaymentProfile ( $data );
          if (strcasecmp ( $data ['profile_status'], 'Active' ) === 0) {
            $data ['profile_status'] = 'ActiveProfile';
          }
          $orderService->updatePaymentProfileState ( $paymentProfile ['profileId'], $data ['profile_status'] );
          $log->debug ( sprintf ( 'Updated payment profile %s status %s', $data ['recurring_payment_id'], $data ['profile_status'] ) );
          break;
      }
    }

    /**
     * Get payment profile from IPN
     *
     * @param array $data
     * @return unknown
     */
    protected function getPaymentProfile(array $data) {
      if (! isset ( $data ['recurring_payment_id'] ) || empty ( $data ['recurring_payment_id'] )) {
        throw new Exception ( 'Invalid recurring_payment_id' );
      }
      $orderService = OrdersService::instance ();
      $paymentProfile = $orderService->getPaymentProfileByPaymentProfileId ( $data ['recurring_payment_id'] );
      if (empty ( $paymentProfile )) {
        throw new Exception ( 'Invalid payment profile' );
      }
      return $paymentProfile;
    }

}