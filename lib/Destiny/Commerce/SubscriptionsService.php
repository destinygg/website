<?php
namespace Destiny\Commerce;

use Destiny\Common\Service;
use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\PayPal\PayPalApiService;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\DBALException;

/**
 * @method static SubscriptionsService instance()
 */
class SubscriptionsService extends Service {

    /**
     * @param array $subscription
     * @param bool $cancel
     * @throws ConnectionException
     * @throws Exception
     */
    public function cancelSubscription(array $subscription, $cancel = false){
        $payPalAPIService = PayPalApiService::instance();
        $conn = Application::instance()->getConnection();
        try {
            $conn->beginTransaction();
            // Cancel subscription
            if ($cancel) {
                $subscription['status'] = SubscriptionStatus::CANCELLED;
                $this->updateSubscription(array(
                    'subscriptionId' => $subscription ['subscriptionId'],
                    'status' => $subscription['status']
                ));
            }
            // Update the subscription info
            $this->updateSubscription(array(
                'subscriptionId' => $subscription['subscriptionId'],
                'paymentStatus' => $subscription['paymentStatus'],
                'recurring' => $subscription['recurring'],
                'status' => $subscription['status']
            ));
            // Cancel the payment profile
            if (!empty ($subscription ['paymentProfileId'])) {
                if (strcasecmp($subscription ['paymentStatus'], PaymentStatus::ACTIVE) === 0) {
                    $payPalAPIService->cancelPaymentProfile($subscription ['paymentProfileId']);
                    $subscription['paymentStatus'] = PaymentStatus::CANCELLED;
                }
                $subscription['recurring'] = 0;
            }
            $conn->commit();
        } catch (\Exception $e) {
            $conn->rollBack();
            throw new Exception("Error updating subscription", $e);
        }
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
        if (!empty ($typeId) && isset ($subscriptions [$typeId])) {
            return $subscriptions [$typeId];
        }
        throw new Exception (sprintf('Subscription type [%s] not found', $typeId));
    }

    /**
     * Return recurring subscriptions that have a expired end date, but a active profile.
     *
     * @return array
     * @throws DBALException
     */
    public function getRecurringSubscriptionsToRenew() {
        $conn = Application::instance()->getConnection();
        $stmt = $conn->prepare('
            SELECT s.* FROM dfl_users_subscriptions s
            WHERE s.recurring = 1 AND s.paymentStatus = :paymentStatus AND s.endDate <= NOW() AND s.billingNextDate > NOW()
        ');
        $stmt->bindValue('paymentStatus', PaymentStatus::ACTIVE, \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Return all subscriptions where the state is active and the end date is < now
     *
     * @return array
     * @throws DBALException
     */
    public function getSubscriptionsToExpire() {
        $conn = Application::instance()->getConnection();
        $stmt = $conn->prepare('SELECT subscriptionId,userId FROM dfl_users_subscriptions WHERE status = :status AND endDate <= NOW()');
        $stmt->bindValue('status', SubscriptionStatus::ACTIVE, \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get the first active subscription
     * Note: This does not take into account end date.
     * It relies on the subscription status Active.
     * It also orders by subscriptionTier and createdDate
     * Returning only the highest and newest tier subscription.
     *
     * @param int $userId
     * @return array
     * @throws DBALException
     */
    public function getUserActiveSubscription($userId) {
        $conn = Application::instance()->getConnection();
        $stmt = $conn->prepare('
          SELECT s.*,gifter.username `gifterUsername` FROM dfl_users_subscriptions s
          LEFT JOIN dfl_users gifter ON (gifter.userId = s.gifter)
          WHERE s.userId = :userId AND s.status = :status 
          ORDER BY s.subscriptionTier DESC, s.createdDate DESC
          LIMIT 0,1
        ');
        $stmt->bindValue('userId', $userId, \PDO::PARAM_INT);
        $stmt->bindValue('status', SubscriptionStatus::ACTIVE, \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * @param int $userId
     * @return array
     * @throws Exception
     * @throws DBALException
     */
    public function getUserActiveAndPendingSubscriptions($userId) {
        $conn = Application::instance()->getConnection();
        $stmt = $conn->prepare('
          SELECT s.*,gifter.username `gifterUsername` FROM dfl_users_subscriptions s
          LEFT JOIN dfl_users gifter ON (gifter.userId = s.gifter)
          WHERE s.userId = :userId AND (s.status = :activeStatus OR s.status = :pendingStatus)
          ORDER BY s.subscriptionTier DESC, s.createdDate DESC
        ');
        $stmt->bindValue('userId', $userId, \PDO::PARAM_INT);
        $stmt->bindValue('activeStatus', SubscriptionStatus::ACTIVE, \PDO::PARAM_STR);
        $stmt->bindValue('pendingStatus', SubscriptionStatus::PENDING, \PDO::PARAM_STR);
        $stmt->execute();
        $subscriptions = $stmt->fetchAll();
        for ($i = 0; $i < count($subscriptions); $i++) {
            $subscriptions [$i]['type'] = $this->getSubscriptionType($subscriptions [$i]['subscriptionType']);
        }
        return $subscriptions;
    }

    /**
     * @param int $subscriptionId
     * @param int $userId
     * @param string $status
     * @return array
     * @throws DBALException
     */
    public function getSubscriptionByIdAndUserIdAndStatus($subscriptionId, $userId, $status) {
        $conn = Application::instance()->getConnection();
        $stmt = $conn->prepare('
          SELECT s.*,gifter.username `gifterUsername` FROM dfl_users_subscriptions s
          LEFT JOIN dfl_users gifter ON (gifter.userId = s.gifter)
          WHERE s.userId = :userId AND s.status = :status AND s.subscriptionId = :subscriptionId
          LIMIT 1
        ');
        $stmt->bindValue('subscriptionId', $subscriptionId, \PDO::PARAM_INT);
        $stmt->bindValue('userId', $userId, \PDO::PARAM_INT);
        $stmt->bindValue('status', $status, \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * @param number $subscriptionId
     * @param number $userId
     * @return array
     * @throws DBALException
     */
    public function getSubscriptionByIdAndUserId($subscriptionId, $userId) {
        $conn = Application::instance()->getConnection();
        $stmt = $conn->prepare('
          SELECT s.*,gifter.username `gifterUsername` FROM dfl_users_subscriptions s
          LEFT JOIN dfl_users gifter ON (gifter.userId = s.gifter)
          WHERE s.userId = :userId AND s.subscriptionId = :subscriptionId
          LIMIT 1
        ');
        $stmt->bindValue('subscriptionId', $subscriptionId, \PDO::PARAM_INT);
        $stmt->bindValue('userId', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * @param int $userId
     * @param string $status
     * @return array
     * @throws DBALException
     */
    public function getSubscriptionByUserIdAndStatus($userId, $status) {
        $conn = Application::instance()->getConnection();
        $stmt = $conn->prepare('SELECT * FROM dfl_users_subscriptions WHERE userId = :userId AND status = :status ORDER BY createdDate DESC LIMIT 0,1');
        $stmt->bindValue('userId', $userId, \PDO::PARAM_INT);
        $stmt->bindValue('status', $status, \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * @param int $tier
     * @return array <array>
     * @throws DBALException
     */
    public function getSubscriptionsByTier($tier) {
        $conn = Application::instance()->getConnection();
        $stmt = $conn->prepare('
          SELECT
            u.userId,
            u.username,
            u.email,
            s.subscriptionType,
            s.createdDate,
            s.endDate,
            s.subscriptionSource,
            s.recurring,
            s.status,
            s.gifter,
            u2.username `gifterUsername`
          FROM dfl_users_subscriptions AS s
          INNER JOIN dfl_users AS u ON (u.userId = s.userId)
          LEFT JOIN dfl_users AS u2 ON (u2.userId = s.gifter)
          WHERE s.subscriptionTier = :subscriptionTier AND s.status = :subscriptionStatus AND s.subscriptionSource = :subscriptionSource
          ORDER BY s.createdDate ASC
        ');
        $stmt->bindValue('subscriptionTier', $tier, \PDO::PARAM_INT);
        $stmt->bindValue('subscriptionStatus', SubscriptionStatus::ACTIVE, \PDO::PARAM_STR);
        $stmt->bindValue('subscriptionSource', Config::$a ['subscriptionType'], \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @param int $gifterId
     * @param string $status
     * @return array <array>
     * @throws Exception
     * @throws DBALException
     */
    public function getSubscriptionsByGifterIdAndStatus($gifterId, $status) {
        $conn = Application::instance()->getConnection();
        $stmt = $conn->prepare('
          SELECT s.*, u2.username, u.username `gifterUsername` 
          FROM dfl_users_subscriptions s
          LEFT JOIN dfl_users u ON (u.userId = s.gifter)
          LEFT JOIN dfl_users u2 ON (u2.userId = s.userId)
          WHERE s.gifter = :gifter AND s.status = :status
          ORDER BY endDate ASC
        ');
        $stmt->bindValue('gifter', $gifterId, \PDO::PARAM_INT);
        $stmt->bindValue('status', $status, \PDO::PARAM_STR);
        $stmt->execute();
        $gifts = $stmt->fetchAll();
        for ($i = 0; $i < count($gifts); $i++) {
            $gifts [$i]['type'] = $this->getSubscriptionType($gifts [$i]['subscriptionType']);
        }
        return $gifts;
    }

    /**
     * @param int $subscriptionId
     * @param int $gifterId
     * @param $status
     * @return array <array>
     * @throws DBALException
     */
    public function getSubscriptionByIdAndGifterIdAndStatus($subscriptionId, $gifterId, $status) {
        $conn = Application::instance()->getConnection();
        $stmt = $conn->prepare('
          SELECT s.*, u2.username, u.username `gifterUsername` 
          FROM dfl_users_subscriptions s
          LEFT JOIN dfl_users u ON (u.userId = s.gifter)
          LEFT JOIN dfl_users u2 ON (u2.userId = s.userId)
          WHERE s.gifter = :gifter AND s.subscriptionId = :subscriptionId AND s.status = :status
          ORDER BY createdDate DESC
          LIMIT 0,1
        ');
        $stmt->bindValue('gifter', $gifterId, \PDO::PARAM_INT);
        $stmt->bindValue('subscriptionId', $subscriptionId, \PDO::PARAM_INT);
        $stmt->bindValue('status', $status, \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * @param array $subscription
     * @return string
     * @throws DBALException
     */
    public function addSubscription(array $subscription) {
        $conn = Application::instance()->getConnection();
        $conn->insert('dfl_users_subscriptions', $subscription);
        return $conn->lastInsertId();
    }

    /**
     * Update subscription
     * @param array $subscription
     * @throws DBALException
     */
    public function updateSubscription(array $subscription) {
        $conn = Application::instance()->getConnection();
        $conn->update('dfl_users_subscriptions', $subscription, array('subscriptionId' => $subscription ['subscriptionId']));
    }

    /**
     * @param number $userId
     * @param int $limit
     * @param int $start
     * @return array
     * @throws Exception
     * @throws DBALException
     */
    public function getSubscriptionsByUserId($userId, $limit = 100, $start = 0) {
        $conn = Application::instance()->getConnection();
        $stmt = $conn->prepare('
          SELECT * FROM dfl_users_subscriptions
          WHERE userId = :userId
          ORDER BY createdDate DESC LIMIT :start,:limit
        ');
        $stmt->bindValue('userId', $userId, \PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue('start', $start, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @param int $subscriptionId
     * @return array
     * @throws Exception
     * @throws DBALException
     */
    public function getSubscriptionById($subscriptionId) {
        $conn = Application::instance()->getConnection();
        $stmt = $conn->prepare('
            SELECT * FROM dfl_users_subscriptions
            WHERE subscriptionId = :subscriptionId
            LIMIT 1
        ');
        $stmt->bindValue('subscriptionId', $subscriptionId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * @param number $gifter userId
     * @param number $giftee userId
     * @return boolean
     * @throws DBALException
     */
    public function getCanUserReceiveGift($gifter, $giftee) {

        if ($gifter == $giftee) {
            return false;
        }

        // Make sure the the giftee accepts gifts
        $conn = Application::instance()->getConnection();
        $stmt = $conn->prepare('SELECT userId FROM dfl_users WHERE userId = :userId AND allowGifting = 1');
        $stmt->bindValue('userId', $giftee, \PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() <= 0) {
            return false;
        }

        // make sure the giftee doesn't have an active subscription
        $subscription = $this->getUserActiveSubscription($giftee);
        if (!empty($subscription)) {
            return false;
        }

        return true;
    }

    /**
     * @param number $gifterId
     * @param int $limit
     * @param int $start
     * @return array
     * @throws Exception
     * @throws DBALException
     */
    public function getSubscriptionsByGifter($gifterId, $limit = 100, $start = 0) {
        $conn = Application::instance()->getConnection();
        $stmt = $conn->prepare('
          SELECT * FROM dfl_users_subscriptions
          WHERE gifter = :gifter
          ORDER BY createdDate DESC LIMIT :start,:limit
        ');
        $stmt->bindValue('gifter', $gifterId, \PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue('start', $start, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @param string $paymentProfileId
     * @return array
     * @throws DBALException
     */
    public function getSubscriptionByPaymentProfileId($paymentProfileId) {
        $conn = Application::instance()->getConnection();
        $stmt = $conn->prepare('
            SELECT * FROM dfl_users_subscriptions
            WHERE paymentProfileId = :paymentProfileId
            LIMIT 1
        ');
        $stmt->bindValue('paymentProfileId', $paymentProfileId, \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }
}