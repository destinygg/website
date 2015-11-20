<?php
namespace Destiny\Commerce;

use Destiny\Common\Service;
use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\Authentication\AuthenticationService;

/**
 * @method static SubscriptionsService instance()
 */
class SubscriptionsService extends Service {

    /**
     * Return recurring subscriptions that have a expired end date, but a active profile.
     *
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getRecurringSubscriptionsToRenew(){
        $conn = Application::instance()->getConnection();
        $stmt = $conn->prepare ( '
            SELECT s.* FROM dfl_users_subscriptions s
            INNER JOIN dfl_orders_payment_profiles p ON (p.orderId = s.orderId)
            WHERE s.recurring = 1 AND s.status = :subscriptionStatus AND p.state = :paymentStatus AND s.endDate <= NOW() AND p.billingNextDate > NOW()
        ' );
        $stmt->bindValue ( 'subscriptionStatus', SubscriptionStatus::ACTIVE, \PDO::PARAM_STR );
        $stmt->bindValue ( 'paymentStatus', PaymentProfileStatus::ACTIVE_PROFILE, \PDO::PARAM_STR );
        $stmt->execute ();
        return $stmt->fetchAll ();
    }

    /**
     * Return all subscriptions where the state is active and the end date is < now
     *
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getExpiredSubscriptions() {
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare ( 'SELECT subscriptionId,userId FROM dfl_users_subscriptions WHERE status = :status AND endDate <= NOW()' );
        $stmt->bindValue ( 'status', SubscriptionStatus::ACTIVE, \PDO::PARAM_STR );
        $stmt->execute ();
        return $stmt->fetchAll ();
    }


    /**
    * Get a subscription type by id
    *
    * @param string $typeId
    * @return array
    * @throws Exception
    */
    public function getSubscriptionType($typeId) {
        $subscriptions = Config::$a ['commerce'] ['subscriptions'];
        if (! empty ( $typeId ) && isset ( $subscriptions [$typeId] )) {
          return $subscriptions [$typeId];
        }
        throw new Exception ( sprintf('Subscription type [%s] not found', $typeId) );
    }

    /**
    * Get the first subscription
    * Note: This does not take into account end date.
    *
    * @param int $userId
    * @return array
    */
    public function getUserSubscription($userId) {
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare ( 'SELECT * FROM dfl_users_subscriptions WHERE userId = :userId ORDER BY createdDate DESC LIMIT 0,1' );
        $stmt->bindValue ( 'userId', $userId, \PDO::PARAM_INT );
        $stmt->execute ();
        return $stmt->fetch ();
    }

    /**
    * Get the first active subscription
    * Note: This does not take into account end date.
    * It relies on the subscription status Active
    *
    * @param int $userId
    * @return array
    */
    public function getUserActiveSubscription($userId) {
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare ( '
          SELECT s.*,gifter.username `gifterUsername` FROM dfl_users_subscriptions s
          LEFT JOIN dfl_users gifter ON (gifter.userId = s.gifter)
          WHERE s.userId = :userId AND s.status = :status 
          ORDER BY s.createdDate DESC 
          LIMIT 0,1
        ' );
        $stmt->bindValue ( 'userId', $userId, \PDO::PARAM_INT );
        $stmt->bindValue ( 'status', SubscriptionStatus::ACTIVE, \PDO::PARAM_STR );
        $stmt->execute ();
        return $stmt->fetch ();
    }

    /**
    * @param int $userId
    * @return array
    */
    public function getUserPendingSubscription($userId) {
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare ( 'SELECT * FROM dfl_users_subscriptions WHERE userId = :userId AND status = :status ORDER BY createdDate DESC LIMIT 0,1' );
        $stmt->bindValue ( 'userId', $userId, \PDO::PARAM_INT );
        $stmt->bindValue ( 'status', SubscriptionStatus::PENDING, \PDO::PARAM_STR );
        $stmt->execute ();
        return $stmt->fetch ();
    }

    /**
    * @param int $tier
    * @return array<array>
    */
    public function getSubscriptionsByTier($tier) {
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare ( '
          SELECT u.userId,u.username,u.email,s.subscriptionType,s.createdDate,s.endDate,s.subscriptionSource,s.recurring,s.status 
          FROM dfl_users_subscriptions AS s
          INNER JOIN dfl_users AS u ON (u.userId = s.userId)
          WHERE s.subscriptionTier = :subscriptionTier AND s.status = :subscriptionStatus AND s.subscriptionSource = :subscriptionSource
          ORDER BY s.createdDate ASC
        ' );
        $stmt->bindValue ( 'subscriptionTier', $tier, \PDO::PARAM_INT );
        $stmt->bindValue ( 'subscriptionStatus', SubscriptionStatus::ACTIVE, \PDO::PARAM_STR );
        $stmt->bindValue ( 'subscriptionSource', Config::$a ['subscriptionType'], \PDO::PARAM_STR );
        $stmt->execute ();
        return $stmt->fetchAll ();
    }

    /**
    * @param int $gifterId
    * @return array<array>
    */
    public function getSubscriptionsByGifterId( $gifterId ) {
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare ( '
          SELECT s.*, u2.username, u.username `gifterUsername` 
          FROM dfl_users_subscriptions s
          LEFT JOIN dfl_users u ON (u.userId = s.gifter)
          LEFT JOIN dfl_users u2 ON (u2.userId = s.userId)
          WHERE s.gifter = :gifter
          ORDER BY createdDate DESC
        ' );
        $stmt->bindValue ( 'gifter', $gifterId, \PDO::PARAM_INT );
        $stmt->execute ();
        return $stmt->fetchAll ();
    }

    /**
    * @param int $gifterId
    * @return array<array>
    */
    public function getActiveSubscriptionsByGifterId( $gifterId ) {
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare ( '
          SELECT s.*, u2.username, u.username `gifterUsername` 
          FROM dfl_users_subscriptions s
          LEFT JOIN dfl_users u ON (u.userId = s.gifter)
          LEFT JOIN dfl_users u2 ON (u2.userId = s.userId)
          WHERE s.gifter = :gifter AND s.status = :status
          ORDER BY endDate ASC
        ' );
        $stmt->bindValue ( 'gifter', $gifterId, \PDO::PARAM_INT );
        $stmt->bindValue ( 'status', SubscriptionStatus::ACTIVE, \PDO::PARAM_STR );
        $stmt->execute ();
        return $stmt->fetchAll ();
    }

    /**
    * @param int $subscriptionId
    * @param int $gifterId
    * @return array<array>
    */
    public function getActiveSubscriptionByIdAndGifterId( $subscriptionId, $gifterId ) {
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare ( '
          SELECT s.*, u2.username, u.username `gifterUsername` 
          FROM dfl_users_subscriptions s
          LEFT JOIN dfl_users u ON (u.userId = s.gifter)
          LEFT JOIN dfl_users u2 ON (u2.userId = s.userId)
          WHERE s.gifter = :gifter AND s.subscriptionId = :subscriptionId AND s.status = :status
          ORDER BY createdDate DESC
          LIMIT 0,1
        ' );
        $stmt->bindValue ( 'gifter', $gifterId, \PDO::PARAM_INT );
        $stmt->bindValue ( 'subscriptionId', $subscriptionId, \PDO::PARAM_INT );
        $stmt->bindValue ( 'status', SubscriptionStatus::ACTIVE, \PDO::PARAM_STR );
        $stmt->execute ();
        return $stmt->fetch ();
    }

    /**
    * @param number $orderId
    * @return array
    */
    public function getSubscriptionByOrderId($orderId){
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare ( '
          SELECT * FROM dfl_users_subscriptions WHERE orderId = :orderId 
          ORDER BY createdDate DESC 
          LIMIT 0,1
        ' );
        $stmt->bindValue ( 'orderId', $orderId, \PDO::PARAM_INT );
        $stmt->execute ();

        $subscription = $stmt->fetch ();
        if (! empty ( $subscription )) {
          $subType = $this->getSubscriptionType ( $subscription ['subscriptionType'] );
          $subscription ['tierLabel'] = $subType ['tierLabel'];
        }

        return $subscription;
    }

    /**
     * @param array $subscription
     * @return string
     */
    public function addSubscription(array $subscription){
        $conn = Application::instance ()->getConnection ();
        $conn->insert ( 'dfl_users_subscriptions', $subscription);
        return $conn->lastInsertId ();
    }

    /**
    * Update subscription
    * @param array $subscription
    */
    public function updateSubscription(array $subscription) {
        $conn = Application::instance ()->getConnection ();
        $conn->update ( 'dfl_users_subscriptions', $subscription, array ('subscriptionId' => $subscription ['subscriptionId']) );
    }

    /**
     * @param number $userId
     * @param int $limit
     * @param int $start
     * @return array
     * @throws Exception
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getUserSubscriptions($userId, $limit = 100, $start = 0) {
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare ( '
          SELECT * FROM dfl_users_subscriptions 
          WHERE userId = :userId
          AND status != :notStatus
          ORDER BY createdDate DESC LIMIT :start,:limit
        ' );
        $stmt->bindValue ( 'notStatus', \PDO::PARAM_STR );
        $stmt->bindValue ( 'userId', $userId, \PDO::PARAM_INT );
        $stmt->bindValue ( 'limit', $limit, \PDO::PARAM_INT );
        $stmt->bindValue ( 'start', $start, \PDO::PARAM_INT );
        $stmt->execute ();
        $subscriptions = $stmt->fetchAll ();
        for($i = 0; $i < count ( $subscriptions ); $i ++) {
          $subType = $this->getSubscriptionType ( $subscriptions [$i] ['subscriptionType'] );
          $subscriptions [$i] ['tierLabel'] = $subType ['tierLabel'];
        }
        return $subscriptions;
    }

    /**
     * @param int $subscriptionId
     * @return array
     * @throws Exception
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getSubscriptionById($subscriptionId) {
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare ( '
            SELECT * FROM dfl_users_subscriptions 
            WHERE subscriptionId = :subscriptionId
            LIMIT 1
        ' );
        $stmt->bindValue ( 'subscriptionId', $subscriptionId, \PDO::PARAM_INT );
        $stmt->execute ();
        $subscription = $stmt->fetch ();
        if (! empty ( $subscription )) {
          $subType = $this->getSubscriptionType ( $subscription ['subscriptionType'] );
          $subscription ['tierLabel'] = $subType ['tierLabel'];
        }
        return $subscription;
    }

    /**
    * @param number $gifter userId
    * @param number $giftee userId
    * @return boolean
    */
    public function getCanUserReceiveGift($gifter, $giftee) {

        if($gifter == $giftee){
          return false;
        }

        // Make sure the the giftee accepts gifts
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare ( 'SELECT userId FROM dfl_users WHERE userId = :userId AND allowGifting = 1' );
        $stmt->bindValue ( 'userId', $giftee, \PDO::PARAM_INT );
        $stmt->execute ();

        if($stmt->rowCount () <= 0){
          return false;
        }

        // make sure the giftee doesn't have an active subscription
        $subscription = $this->getUserActiveSubscription ($giftee);
        if(!empty($subscription)){
          return false;
        }

        return true;
    }
}