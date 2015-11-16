<?php
namespace Destiny\Tasks;

use Destiny\Chat\ChatIntegrationService;
use Destiny\Commerce\SubscriptionStatus;
use Destiny\Common\Annotation\Schedule;
use Destiny\Common\Application;
use Destiny\Commerce\SubscriptionsService;
use Destiny\Common\Authentication\AuthenticationService;
use Destiny\Common\TaskInterface;
use Destiny\Common\User\UserService;
use Destiny\Common\Utils\Date;

/**
 * @Schedule(frequency=5,period="minute")
 */
class SubscriptionExpire implements TaskInterface {

    public function execute() {
        $log = Application::instance()->getLogger();
        $conn = Application::instance()->getConnection();
        $authenticationService = AuthenticationService::instance();
        $subscriptionService = SubscriptionsService::instance ();
        $users = array();

        // Renew any subscription that has an active payment profile.
        $subscriptions = $subscriptionService->getRecurringSubscriptionsToRenew();
        foreach ( $subscriptions as $subscription ){
            try {
                $subType = $subscriptionService->getSubscriptionType ( $subscription ['subscriptionType'] );
                $end = Date::getDateTime ( $subscription ['endDate'] );
                $end->modify ( '+' . $subType ['billingFrequency'] . ' ' . strtolower ( $subType ['billingPeriod'] ) );
                $subscriptionService->updateSubscriptionDateEnd ( $subscription ['subscriptionId'], $end );
                $this->sendResubscribeBroadcast ( $subscription );
                $users[] = $subscription ['userId'];
            } catch (\Exception $e) {
                $log->critical("Could not roll over subscription", $subscription);
            }
        }

        // Expire subscriptions
        $subscriptions = $subscriptionService->getExpiredSubscriptions();
        if (! empty ( $subscriptions )) {
            foreach ( $subscriptions as $subscription ) {
                $users[] = $subscription ['userId'];
                $conn->update ( 'dfl_users_subscriptions',
                    array ('status' => SubscriptionStatus::EXPIRED),
                    array ('subscriptionId' => $subscription ['subscriptionId'])
                );
            }
        }

        // Update users
        $users = array_unique($users);
        foreach ($users as $id) {
            $authenticationService->flagUserForUpdate ( $id );
        };
    }

    private function sendResubscribeBroadcast(array $subscription) {
        $log = Application::instance ()->getLogger ();
        $userService = UserService::instance();
        $user = $userService->getUserById ( $subscription['userId'] );
        if(!empty($user)){
            try {
                // the subscription endDate has not been updated with the new subscription time
                $months = max(1, Date::getDateTime($subscription['createdDate'])->diff(Date::getDateTime($subscription['endDate']))->m);
                $months = ($months > 1) ? $months. " months" : $months. " month";
                $chatIntegrationService = ChatIntegrationService::instance();
                $chatIntegrationService->sendBroadcast(sprintf("%s has resubscribed! Active for %s", $user['username'], $months));
            }catch (\Exception $e){
                $log->critical ( 'Could not send resubscribe broadcast', $subscription );
            }
        }
    }
}