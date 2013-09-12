<?php
namespace Destiny\Common\Service;

use Destiny\Common\Commerce\SubscriptionStatus;
use Destiny\Common\Service;
use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\Exception;

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
	 * Expires subscritions based on their end date
	 *
	 * @return int the number of expired subscriptions
	 */
	public function expiredSubscriptions() {
		$conn = Application::instance ()->getConnection ();
		
		// Expire recurring subs with a 24 hour grace period
		$stmt = $conn->prepare ( 'SELECT subscriptionId,userId FROM dfl_users_subscriptions WHERE recurring = 1 AND status = \'Active\' AND endDate + INTERVAL 24 HOUR <= NOW()' );
		$stmt->execute ();
		$subscriptions = $stmt->fetchAll ();
		if (! empty ( $subscriptions )) {
			foreach ( $subscriptions as $sub ) {
				AuthenticationService::instance ()->flagUserForUpdate ( $sub ['userId'] );
				$conn->executeQuery ( 'UPDATE dfl_users_subscriptions SET `status` = \'Expired\' WHERE subscriptionId = \'' . $sub ['subscriptionId'] . '\'' );
			}
		}
		
		// Expire NONE recurring subs immediately
		$stmt = $conn->prepare ( 'SELECT subscriptionId,userId FROM dfl_users_subscriptions WHERE recurring = 0 AND status = \'Active\' AND endDate <= NOW()' );
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
		throw new Exception ( 'Subscription type not found' );
	}

	/**
	 * Adds a new subscription
	 *
	 * @param int $userId
	 * @param string $startDate
	 * @param string $endDate
	 * @param string $status
	 * @param boolean $recurring
	 * @param string $source
	 * @param string $type
	 * @param int $tier
	 * @return string
	 */
	public function addSubscription($userId, $startDate, $endDate, $status, $recurring, $source, $type, $tier) {
		$conn = Application::instance ()->getConnection ();
		$conn->insert ( 'dfl_users_subscriptions', array (
			'userId' => $userId,
			'subscriptionSource' => $source,
			'subscriptionType' => $type,
			'subscriptionTier' => $tier,
			'createdDate' => $startDate,
			'endDate' => $endDate,
			'recurring' => $recurring,
			'status' => $status 
		), array (
			\PDO::PARAM_INT,
			\PDO::PARAM_STR,
			\PDO::PARAM_STR,
			\PDO::PARAM_INT,
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
	public function updateSubscriptionDateEnd($subscriptionId, \DateTime $endDate) {
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
	 * Get a list of subscriptions by tier
	 *
	 * @param tinyint $tier
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
		$stmt->bindValue ( 'subscriptionSource', 'destiny.gg', \PDO::PARAM_STR );
		$stmt->execute ();
		return $stmt->fetchAll ();
	}

}