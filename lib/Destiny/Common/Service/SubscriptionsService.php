<?php
namespace Destiny\Common\Service;

use Destiny\Common\Commerce\OrderStatus;
use Destiny\Common\Commerce\SubscriptionStatus;
use Destiny\Common\Service;
use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\Utils\Date;
use Destiny\Common\AppException;

class SubscriptionsService extends Service {
	protected static $instance = null;

	/**
	 * Singleton
	 *
	 * @return SubscriptionsService
	 */
	public static function instance() {
		return parent::instance ();
	}

	/**
	 * Sets subscription status to 'Expired' where the end date is smaller than NOW +72 HOUR
	 * Since paypal says it takes up to 72 hours for recurring payments to occur
	 *
	 * @return int the number of expired subscriptions
	 */
	public function expiredSubscriptions() {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( 'SELECT subscriptionId,userId FROM dfl_users_subscriptions WHERE status = \'Active\' AND endDate + INTERVAL 24 HOUR <= NOW()' );
		$stmt->execute ();
		$subscriptions = $stmt->fetchAll ();
		if (! empty ( $subscriptions )) {
			foreach ( $subscriptions as $sub ) {
				AuthenticationService::instance ()->flagUserForUpdate ( $sub ['userId'] );
				$conn->executeQuery ( 'UPDATE dfl_users_subscriptions SET `status` = \'Expired\' WHERE subscriptionId = \'' . $sub ['subscriptionId'] . '\'' );
			}
		}
	}

	/**
	 * Get a subscription type by id
	 *
	 * @param string $subscriptionId
	 * @return array
	 * @throws Exception
	 */
	public function getSubscriptionType($subscriptionId) {
		$subscriptions = Config::$a ['commerce'] ['subscriptions'];
		if (! empty ( $subscriptionId ) && isset ( $subscriptions [$subscriptionId] )) {
			return $subscriptions [$subscriptionId];
		}
		throw new AppException ( 'Subscription type not found' );
	}

	/**
	 * This whole method is shit
	 *
	 * @param int $userId
	 * @param array $subscription
	 */
	public function addSubscription($userId, $startDate, $endDate, $status, $recurring, $source) {
		$conn = Application::instance ()->getConnection ();
		$conn->insert ( 'dfl_users_subscriptions', array (
			'userId' => $userId,
			'subscriptionSource' => $source,
			'createdDate' => $startDate,
			'endDate' => $endDate,
			'recurring' => $recurring,
			'status' => $status 
		), array (
			\PDO::PARAM_INT,
			\PDO::PARAM_STR,
			\PDO::PARAM_STR,
			\PDO::PARAM_STR,
			\PDO::PARAM_BOOL,
			\PDO::PARAM_STR 
		) );
		return $conn->lastInsertId ();
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
		$stmt = $conn->prepare ( 'SELECT * FROM dfl_users_subscriptions WHERE userId = :userId AND status = :status ORDER BY createdDate DESC LIMIT 0,1' );
		$stmt->bindValue ( 'userId', $userId, \PDO::PARAM_INT );
		$stmt->bindValue ( 'status', SubscriptionStatus::ACTIVE, \PDO::PARAM_STR );
		$stmt->execute ();
		return $stmt->fetch ();
	}

	/**
	 * Get the first pending subscription
	 *
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
	 * Update a subscriptions end date
	 *
	 * @param int $subscriptionId
	 * @param \DateTime $endDate
	 */
	public function updateSubscriptionDateEnd($subscriptionId,\DateTime $endDate) {
		$conn = Application::instance ()->getConnection ();
		$conn->update ( 'dfl_users_subscriptions', array (
			'endDate' => $endDate->format ( 'Y-m-d H:i:s' ) 
		), array (
			'subscriptionId' => $subscriptionId 
		) );
	}

	/**
	 * Update a subscriptions recurring field
	 *
	 * @param int $subscriptionId
	 * @param \DateTime $endDate
	 */
	public function updateSubscriptionRecurring($subscriptionId, $recurring) {
		$conn = Application::instance ()->getConnection ();
		$conn->update ( 'dfl_users_subscriptions', array (
			'recurring' => $recurring 
		), array (
			'subscriptionId' => $subscriptionId 
		), array (
			\PDO::PARAM_BOOL,
			\PDO::PARAM_INT 
		) );
	}

	/**
	 * Update a subscriptions status
	 *
	 * @param int $subscriptionId
	 * @param \DateTime $endDate
	 */
	public function updateSubscriptionState($subscriptionId, $status) {
		$conn = Application::instance ()->getConnection ();
		$conn->update ( 'dfl_users_subscriptions', array (
			'status' => $status 
		), array (
			'subscriptionId' => $subscriptionId 
		), array (
			\PDO::PARAM_STR,
			\PDO::PARAM_INT 
		) );
	}

	/**
	 * Update a subscriptions payment profile
	 *
	 * @param int $subscriptionId
	 * @param int $profileId
	 * @param boolean $recurring
	 * @return int
	 */
	public function updateSubscriptionPaymentProfile($subscriptionId, $profileId, $recurring) {
		$conn = Application::instance ()->getConnection ();
		$conn->update ( 'dfl_users_subscriptions', array (
			'paymentProfileId' => $profileId,
			'recurring' => $recurring 
		), array (
			'subscriptionId' => $subscriptionId 
		), array (
			\PDO::PARAM_STR,
			\PDO::PARAM_BOOL,
			\PDO::PARAM_INT 
		) );
	}

	/**
	 * Get a list of subscriptions
	 *
	 * @todo this needs to be refactored when proper sub types are implemented
	 * @param string $subscriptionType
	 * @return array<array>
	 */
	public function getSubscriptionsByType($subscriptionType) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT u.userId,u.username,u.email,o.description,s.createdDate,s.endDate,s.subscriptionSource,s.recurring,s.status FROM dfl_orders AS o
			INNER JOIN dfl_users_subscriptions AS s ON (s.userId = o.userId AND s.status = :subscriptionStatus AND s.subscriptionSource = :subscriptionSource)
			INNER JOIN dfl_users AS u ON (u.userId = s.userId)
			WHERE o.description LIKE \'%' . $subscriptionType . '%\'
			AND o.state = :orderStatus
			GROUP BY o.userId
			ORDER BY u.username
		' );
		$stmt->bindValue ( 'orderStatus', OrderStatus::COMPLETED, \PDO::PARAM_STR );
		$stmt->bindValue ( 'subscriptionStatus', SubscriptionStatus::ACTIVE, \PDO::PARAM_STR );
		$stmt->bindValue ( 'subscriptionSource', 'destiny.gg', \PDO::PARAM_STR );
		$stmt->execute ();
		return $stmt->fetchAll ();
	}

}