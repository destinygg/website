<?php
namespace Destiny\Commerce;

use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\DBException;
use Destiny\Common\Exception;
use Destiny\Common\Service;
use Destiny\Common\Utils\Date;
use Destiny\PayPal\PayPalApiService;
use Doctrine\DBAL\DBALException;
use PDO;

/**
 * @method static SubscriptionsService instance()
 */
class SubscriptionsService extends Service {

    /**
     * @throws DBException
     */
    public function addSubscription(array $subscription = null): int {
        try {
            $conn = Application::getDbConn();
            $conn->insert('dfl_users_subscriptions', $subscription);
            return intval($conn->lastInsertId());
        } catch (DBALException $e) {
            throw new DBException("Error adding subscription", $e);
        }
    }

    /**
     * Update subscription
     * @throws DBException
     */
    public function updateSubscription(array $subscription = null) {
        try {
            $conn = Application::getDbConn();
            $conn->update('dfl_users_subscriptions', $subscription, ['subscriptionId' => $subscription ['subscriptionId']]);
        } catch (DBALException $e) {
            throw new DBException("Error updating subscription", $e);
        }
    }

    /**
     * @throws Exception
     */
    public function cancelSubscription(array $subscription, bool $removeRemaining, int $userId): array {
        $payPalAPIService = PayPalApiService::instance();
        $conn = Application::getDbConn();
        try {
            try {
                $conn->beginTransaction();

                // Set recurring flag
                if ($subscription['recurring'] == 1) {
                    $subscription['recurring'] = 0;
                }
                // Set subscription to cancelled
                if ($removeRemaining) {
                    $subscription['status'] = SubscriptionStatus::CANCELLED;
                }
                // Cancel the payment profile
                if (!empty($subscription['paymentProfileId']) && strcasecmp($subscription['paymentStatus'], PaymentStatus::ACTIVE) === 0) {
                    $payPalAPIService->cancelPaymentProfile($subscription['paymentProfileId']);
                    $subscription['paymentStatus'] = PaymentStatus::CANCELLED;
                }

                $data = [
                    'subscriptionId' => $subscription['subscriptionId'],
                    'paymentStatus' => $subscription['paymentStatus'],
                    'recurring' => $subscription['recurring'],
                    'status' => $subscription['status'],
                    'cancelDate' => Date::getSqlDateTime(),
                    'cancelledBy' => $userId,
                ];

                $this->updateSubscription($data);
                $conn->commit();
            } catch (DBALException $e) {
                $conn->rollBack();
                throw new DBException("Error cancelling subscription.", $e);
            }
        } catch (DBALException $e) {
            throw new DBException("Error cancelling subscription.", $e);
        }
        return $subscription;
    }

    /**
     * @return array||null
     */
    public function getSubscriptionType(string $typeId): array {
        return Config::$a['commerce']['subscriptions'][$typeId] ?? null;
    }

    /**
     * @return array|false
     * @throws DBException
     */
    public function findById(int $subscriptionId) {
        try {
            $conn = Application::getDbConn();
            $stmt = $conn->prepare('
                SELECT * FROM dfl_users_subscriptions
                WHERE subscriptionId = :subscriptionId
                LIMIT 1
            ');
            $stmt->bindValue('subscriptionId', $subscriptionId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch();
        } catch (DBALException $e) {
            throw new DBException("Error loading subscription.", $e);
        }
    }

    /**
     * Return recurring subscriptions that have a expired end date, but a active profile.
     * @throws DBException
     */
    public function getRecurringSubscriptionsToRenew(): array {
        try {
            $conn = Application::getDbConn();
            $stmt = $conn->prepare('
                SELECT s.* FROM dfl_users_subscriptions s
                WHERE s.recurring = 1 AND s.paymentStatus = :paymentStatus 
                AND s.endDate <= NOW() AND s.billingNextDate > NOW()
            ');
            $stmt->bindValue('paymentStatus', PaymentStatus::ACTIVE, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (DBALException $e) {
            throw new DBException("Error loading subscription.", $e);
        }
    }

    /**
     * Return all subscriptions where the state is active and the end date is < now
     * @throws DBException
     */
    public function getSubscriptionsToExpire(): array {
        try {
            $conn = Application::getDbConn();
            $stmt = $conn->prepare('SELECT subscriptionId, userId FROM dfl_users_subscriptions WHERE status = :status AND endDate <= NOW()');
            $stmt->bindValue('status', SubscriptionStatus::ACTIVE, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (DBALException $e) {
            throw new DBException("Error loading subscription.", $e);
        }
    }

    /**
     * Get the first active subscription
     * Note: This does not take into account end date.
     * It relies on the subscription status Active.
     * It also orders by subscriptionTier and createdDate
     * Returning only the highest and newest tier subscription.
     *
     * @return array|false
     * @throws DBException
     */
    public function getUserActiveSubscription(int $userId) {
        try {
            $conn = Application::getDbConn();
            $stmt = $conn->prepare('
              SELECT s.*, gifter.username `gifterUsername` FROM dfl_users_subscriptions s
              LEFT JOIN dfl_users gifter ON (gifter.userId = s.gifter)
              WHERE s.userId = :userId AND s.status = :status 
              ORDER BY s.subscriptionTier DESC, s.createdDate DESC
              LIMIT 1
            ');
            $stmt->bindValue('userId', $userId, PDO::PARAM_INT);
            $stmt->bindValue('status', SubscriptionStatus::ACTIVE, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch();
        } catch (DBALException $e) {
            throw new DBException("Error loading subscriptions.", $e);
        }
    }

    /**
     * @throws DBException
     */
    public function getUserActiveAndPendingSubscriptions(int $userId): array {
        try {
            $conn = Application::getDbConn();
            $stmt = $conn->prepare('
              SELECT s.*, gifter.username `gifterUsername` FROM dfl_users_subscriptions s
              LEFT JOIN dfl_users gifter ON (gifter.userId = s.gifter)
              WHERE s.userId = :userId AND (s.status = :activeStatus OR s.status = :pendingStatus)
              ORDER BY s.subscriptionTier DESC, s.createdDate DESC
            ');
            $stmt->bindValue('userId', $userId, PDO::PARAM_INT);
            $stmt->bindValue('activeStatus', SubscriptionStatus::ACTIVE, PDO::PARAM_STR);
            $stmt->bindValue('pendingStatus', SubscriptionStatus::PENDING, PDO::PARAM_STR);
            $stmt->execute();
            return array_map(function($item) {
                $item['type'] = $this->getSubscriptionType($item['subscriptionType']);
                return $item;
            }, $stmt->fetchAll());
        } catch (DBALException $e) {
            throw new DBException("Error loading subscriptions.", $e);
        }
    }

    /**
     * @throws DBException
     */
    public function searchAll(array $params): array {
        try {
            $conn = Application::getDbConn();
            $clauses = [];
            if (!empty($params['search'])) {
                $clauses[] = 'u.username LIKE :search';
            }
            if (!empty($params['recurring'])) {
                $clauses[] = 's.recurring = :recurring';
            }
            if (!empty($params['status'])) {
                $clauses[] = 's.status = :status';
            }
            if (!empty($params['tier'])) {
                $clauses[] = 's.subscriptionTier = :tier';
            }
            $q = '
              SELECT
                SQL_CALC_FOUND_ROWS
                s.subscriptionId,
                u.userId,
                u.username,
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
            ';
            if (count($clauses) > 0) {
                $q .= ' WHERE ' . join(' AND ', $clauses);
            }
            $q.= ' ORDER BY s.createdDate DESC';
            $q.= ' LIMIT :start, :limit ';
            $stmt = $conn->prepare($q);

            if (!empty($params['search'])) {
                $stmt->bindValue('search', $params['search'], PDO::PARAM_STR);
            }
            if (!empty($params['recurring'])) {
                $stmt->bindValue('recurring', intval($params['recurring']), PDO::PARAM_INT);
            }
            if (!empty($params['status'])) {
                $stmt->bindValue('status', $params['status'], PDO::PARAM_STR);
            }
            if (!empty($params['tier'])) {
                $stmt->bindValue('tier', $params['tier'], PDO::PARAM_STR);
            }

            $stmt->bindValue('start', ($params['page'] - 1) * $params['size'], PDO::PARAM_INT);
            $stmt->bindValue('limit', (int) $params['size'], PDO::PARAM_INT);
            $stmt->execute();

            $items = array_map(function($item) {
                $item['type'] = $this->getSubscriptionType($item['subscriptionType']);
                return $item;
            }, $stmt->fetchAll());
            $total = $conn->fetchColumn('SELECT FOUND_ROWS()');
            return [
                'list' => $items,
                'total' => $total,
                'totalpages' => ceil($total/$params['size']),
                'pages' => 5,
                'page' => $params['page'],
                'limit' => $params['size'],
            ];
        } catch (DBALException $e) {
            throw new DBException("Error searching subscriptions.", $e);
        }
    }

    /**
     * @return array|false
     * @throws DBException
     */
    public function findByUserIdAndStatus(int $userId, string $status) {
        try {
            $conn = Application::getDbConn();
            $stmt = $conn->prepare('
              SELECT * FROM dfl_users_subscriptions 
              WHERE userId = :userId AND status = :status 
              ORDER BY createdDate DESC 
              LIMIT 1
            ');
            $stmt->bindValue('userId', $userId, PDO::PARAM_INT);
            $stmt->bindValue('status', $status, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch();
        } catch (DBALException $e) {
            throw new DBException("Error searching subscriptions.", $e);
        }
    }

    /**
     * @throws DBException
     */
    public function findCompletedByGifterId(int $gifterId, int $limit = 100, int $start = 0): array {
        try {
            $conn = Application::getDbConn();
            $stmt = $conn->prepare('
              SELECT s.*, u2.username, u.username `gifterUsername` 
              FROM dfl_users_subscriptions s
              LEFT JOIN dfl_users u ON (u.userId = s.gifter)
              LEFT JOIN dfl_users u2 ON (u2.userId = s.userId)
              WHERE s.gifter = :gifter AND (s.status = :active OR s.status = :cancelled OR s.status = :expired)
              ORDER BY endDate DESC
              LIMIT :start,:limit
            ');
            $stmt->bindValue('active', SubscriptionStatus::ACTIVE, PDO::PARAM_STR);
            $stmt->bindValue('cancelled', SubscriptionStatus::CANCELLED, PDO::PARAM_STR);
            $stmt->bindValue('expired', SubscriptionStatus::EXPIRED, PDO::PARAM_STR);
            $stmt->bindValue('gifter', $gifterId, PDO::PARAM_INT);
            $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue('start', $start, PDO::PARAM_INT);
            $stmt->execute();
            $gifts = $stmt->fetchAll();
            for ($i = 0; $i < count($gifts); $i++) {
                // TODO possible to assign null to this.
                $gifts[$i]['type'] = $this->getSubscriptionType($gifts [$i]['subscriptionType']);
            }
            return $gifts;
        } catch (DBALException $e) {
            throw new DBException("Error searching subscriptions.", $e);
        }
    }

    /**
     * @throws DBException
     */
    public function findByGifterIdAndStatus(int $gifterId, string $status): array {
        try {
            $conn = Application::getDbConn();
            $stmt = $conn->prepare('
              SELECT s.*, u2.username, u.username `gifterUsername` 
              FROM dfl_users_subscriptions s
              LEFT JOIN dfl_users u ON (u.userId = s.gifter)
              LEFT JOIN dfl_users u2 ON (u2.userId = s.userId)
              WHERE s.gifter = :gifter AND s.status = :status
              ORDER BY endDate ASC
            ');
            $stmt->bindValue('gifter', $gifterId, PDO::PARAM_INT);
            $stmt->bindValue('status', $status, PDO::PARAM_STR);
            $stmt->execute();
            $gifts = $stmt->fetchAll();
            for ($i = 0; $i < count($gifts); $i++) {
                // TODO possible to assign null to this.
                $gifts[$i]['type'] = $this->getSubscriptionType($gifts[$i]['subscriptionType']);
            }
            return $gifts;
        } catch (DBALException $e) {
            throw new DBException("Error searching subscriptions.", $e);
        }
    }

    /**
     * @throws DBException
     */
    public function findByUserId(int $userId, $limit = 100, $start = 0): array {
        try {
            $conn = Application::getDbConn();
            $stmt = $conn->prepare('
              SELECT * FROM dfl_users_subscriptions
              WHERE userId = :userId
              ORDER BY createdDate DESC LIMIT :start,:limit
            ');
            $stmt->bindValue('userId', $userId, PDO::PARAM_INT);
            $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue('start', $start, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (DBALException $e) {
            throw new DBException("Error searching subscriptions.", $e);
        }
    }

    /**
     * @return boolean
     * @throws DBException
     */
    public function canUserReceiveGift(int $gifter, int $giftee): bool {
        if ($gifter == $giftee) {
            return false;
        }

        // Make sure the the giftee accepts gifts
        try {
            $conn = Application::getDbConn();
            $stmt = $conn->prepare('SELECT userId FROM dfl_users WHERE userId = :userId AND allowGifting = 1');
            $stmt->bindValue('userId', $giftee, PDO::PARAM_INT);
            $stmt->execute();
        } catch (DBALException $e) {
            throw new DBException("Error selecting user id.", $e);
        }

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
     * Get users who accept gift subs.
     *
     * Note that this query doesn't exclude users who are already subscribed.
     *
     * @throws DBException
     */
    public function findGiftableUsersByUsernames(array $usernames): array {
        try {
            $conn = Application::getDbConn();
            $stmt = $conn->executeQuery(
                'SELECT *
                FROM dfl_users
                WHERE username IN (?)
                    AND allowGifting = 1',
                [$usernames],
                [\Doctrine\DBAL\Connection::PARAM_STR_ARRAY]
            );
            return $stmt->fetchAll();
        } catch (DBALException $e) {
            throw new DBException("Error finding giftable users.", $e);
        }
    }

    public function findRecentlyModifiedGiftableUsers(int $limit, array $exclusionUserIds = []): array {
        try {
            $conn = Application::getDbConn();
            $stmt = $conn->executeQuery(
                'SELECT u.*
                FROM dfl_users AS u
                LEFT JOIN dfl_users_subscriptions AS s
                ON u.userId = s.userId AND s.status = ? 
                WHERE u.userId NOT IN (?)
                    AND u.allowGifting = 1
                    AND s.subscriptionId IS NULL
                ORDER BY u.modifiedDate
                LIMIT ?',
                [SubscriptionStatus::ACTIVE, $exclusionUserIds, $limit],
                [PDO::PARAM_STR, \Doctrine\DBAL\Connection::PARAM_STR_ARRAY, PDO::PARAM_INT]
            );
            return $stmt->fetchAll();
        } catch (DBALException $e) {
            throw new DBException("Error finding recently modified giftable users.", $e);
        }
    }

    /**
     * @return array|false
     * @throws DBException
     */
    public function findByPaymentProfileId(string $paymentProfileId) {
        try {
            $conn = Application::getDbConn();
            $stmt = $conn->prepare('
                SELECT * FROM dfl_users_subscriptions
                WHERE paymentProfileId = :paymentProfileId
                LIMIT 1
            ');
            $stmt->bindValue('paymentProfileId', $paymentProfileId, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch();
        } catch (DBALException $e) {
            throw new DBException("Error searching payment profile.", $e);
        }
    }

    /**
     * @throws DBException
     */
    public function findCompletedByUserId(int $userId, int $limit = 100, int $start = 0): array {
        try {
            $conn = Application::getDbConn();
            $stmt = $conn->prepare('
              SELECT * FROM dfl_users_subscriptions s
              WHERE s.`userId` = :userId AND (s.status = :active OR s.status = :cancelled OR s.status = :expired)
              ORDER BY createdDate DESC 
              LIMIT :start,:limit
            ');
            $stmt->bindValue('active', SubscriptionStatus::ACTIVE, PDO::PARAM_STR);
            $stmt->bindValue('expired', SubscriptionStatus::EXPIRED, PDO::PARAM_STR);
            $stmt->bindValue('cancelled', SubscriptionStatus::CANCELLED, PDO::PARAM_STR);
            $stmt->bindValue('userId', $userId, PDO::PARAM_INT);
            $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue('start', $start, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (DBALException $e) {
            throw new DBException("Error searching subscriptions.", $e);
        }
    }

    /**
     * @return array|false
     * @throws DBException
     */
    public function getSubscriptionsByPaymentId(int $paymentId): array {
        try {
            $conn = Application::getDbConn();
            $stmt = $conn->prepare('
                SELECT subs.*
                FROM dfl_users_subscriptions AS subs
                INNER JOIN dfl_payments_purchases AS purchases
                ON purchases.subscriptionId = subs.subscriptionId
                    AND purchases.paymentId = :paymentId
            ');
            $stmt->bindValue('paymentId', $paymentId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (DBALException $e) {
            throw new DBException('Error loading payment', $e);
        }
    }

}
