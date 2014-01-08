<?php
namespace Destiny\Commerce;

use Destiny\Common\Service;
use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\Authentication\AuthenticationService;
use Destiny\Commerce\SubscriptionStatus;
use Destiny\Common\Utils\Date;

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
		$authenticationService = AuthenticationService::instance ();
		
		// Expire recurring subs with a 24 hour grace period
		$stmt = $conn->prepare ( 'SELECT subscriptionId,userId FROM dfl_users_subscriptions WHERE recurring = 1 AND status = :status AND endDate + INTERVAL 24 HOUR <= NOW()' );
		$stmt->bindValue ( 'status', SubscriptionStatus::ACTIVE, \PDO::PARAM_STR );
		$stmt->execute ();
		$subscriptions = $stmt->fetchAll ();
		if (! empty ( $subscriptions )) {
			foreach ( $subscriptions as $sub ) {
				$authenticationService->flagUserForUpdate ( $sub ['userId'] );
				$conn->update ( 'dfl_users_subscriptions', 
						array ('status' => SubscriptionStatus::EXPIRED), 
						array ('subscriptionId' => $sub ['subscriptionId']) 
				);
			}
		}
		
		// Expire NONE recurring subs immediately
		$stmt = $conn->prepare ( 'SELECT subscriptionId,userId FROM dfl_users_subscriptions WHERE recurring = 0 AND status = :status AND endDate <= NOW()' );
		$stmt->bindValue ( 'status', SubscriptionStatus::ACTIVE, \PDO::PARAM_STR );
		$stmt->execute ();
		$subscriptions = $stmt->fetchAll ();
		if (! empty ( $subscriptions )) {
			foreach ( $subscriptions as $sub ) {
				$authenticationService->flagUserForUpdate ( $sub ['userId'] );
				$conn->update ( 'dfl_users_subscriptions',
						array ('status' => SubscriptionStatus::EXPIRED),
						array ('subscriptionId' => $sub ['subscriptionId'])
				);
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
		throw new Exception ( sprintf('Subscription type [%s] not found', $subscriptionId) );
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
		$stmt->bindValue ( 'subscriptionSource', Config::$a ['subscriptionType'], \PDO::PARAM_STR );
		$stmt->execute ();
		return $stmt->fetchAll ();
	}
	
	/**
	 * Get a subscription by the order and user id
	 * 
	 * @param number $orderId
	 * @param number $userId
	 * @return array
	 */
	public function getSubscriptionByOrderIdAndUserId($orderId, $userId){
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( 'SELECT * FROM dfl_users_subscriptions WHERE userId = :userId AND orderId = :orderId ORDER BY createdDate DESC LIMIT 0,1' );
		$stmt->bindValue ( 'orderId', $orderId, \PDO::PARAM_INT );
		$stmt->bindValue ( 'userId', $userId, \PDO::PARAM_INT );
		$stmt->execute ();
		return $stmt->fetch ();
	}
	
	/**
	 * Create a user subscription based on an order
	 * Subscription is in the NEW state
	 * 
	 * @param array $order
	 * @param array $subscriptionType
	 * @param bool $recurring
	 * @return number $subscriptionId
	 */
	public function createSubscriptionFromOrder(array $order, array $subscriptionType) {
		$start = Date::getDateTime ();
		$end = Date::getDateTime ();
		$end->modify ( '+' . $subscriptionType ['billingFrequency'] . ' ' . strtolower ( $subscriptionType ['billingPeriod'] ) );
		$conn = Application::instance ()->getConnection ();
		$conn->insert ( 'dfl_users_subscriptions', 
			array (
				'userId'             => $order ['userId'],
				'orderId'            => $order ['orderId'],
				'subscriptionSource' => Config::$a ['subscriptionType'],
				'subscriptionType'   => $subscriptionType ['id'],
				'subscriptionTier'   => $subscriptionType ['tier'],
				'createdDate'        => $start->format ( 'Y-m-d H:i:s' ),
				'endDate'            => $end->format ( 'Y-m-d H:i:s' ),
				'recurring'          => 0,
				'status'             => SubscriptionStatus::_NEW 
			), 
			array (
				\PDO::PARAM_INT,
				\PDO::PARAM_INT,
				\PDO::PARAM_STR,
				\PDO::PARAM_STR,
				\PDO::PARAM_INT,
				\PDO::PARAM_STR,
				\PDO::PARAM_STR,
				\PDO::PARAM_BOOL,
				\PDO::PARAM_STR 
			) 
		);
		return $conn->lastInsertId ();
	}

	/**
	 * Add subscription
	 * @param array $subscription
	 */
	public function addSubscription(array $subscription){
		$conn = Application::instance ()->getConnection ();
		$conn->insert ( 'dfl_users_subscriptions', $subscription);
	}
	
	/**
	 * Update subscription
	 * @param array $subscription
	 */
	public function updateSubscription(array $subscription) {
		$conn = Application::instance ()->getConnection ();
		$conn->update ( 'dfl_users_subscriptions', $subscription, array (
				'subscriptionId' => $subscription ['subscriptionId'] 
		) );
	}
	
	/**
	 * Get all user subscriptions
	 *
	 * @param number $userId        	
	 * @return array
	 */
	public function getUserSubscriptions($userId, $limit = 100, $start = 0) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( 'SELECT * FROM dfl_users_subscriptions WHERE userId = :userId AND subscriptionSource = :subscriptionSource ORDER BY createdDate DESC LIMIT :start,:limit' );
		$stmt->bindValue ( 'userId', $userId, \PDO::PARAM_INT );
		$stmt->bindValue ( 'limit', $limit, \PDO::PARAM_INT );
		$stmt->bindValue ( 'start', $start, \PDO::PARAM_INT );
		$stmt->bindValue ( 'subscriptionSource', Config::$a ['subscriptionType'], \PDO::PARAM_STR );
		$stmt->execute ();
		$subscriptions = $stmt->fetchAll ();
		for($i = 0; $i < count ( $subscriptions ); $i ++) {
			$subType = $this->getSubscriptionType ( $subscriptions [$i] ['subscriptionType'] );
			$subscriptions [$i] ['tierItemLabel'] = $subType ['tierItemLabel'];
		}
		return $subscriptions;
	}
	
	/**
	 * Get a subscription by Id
	 *
	 * @param number $id        	
	 * @return array
	 */
	public function getSubscriptionById($subscriptionId) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( 'SELECT * FROM dfl_users_subscriptions WHERE subscriptionId = :subscriptionId' );
		$stmt->bindValue ( 'subscriptionId', $subscriptionId, \PDO::PARAM_INT );
		$stmt->execute ();
		$subscription = $stmt->fetch ();
		if (! empty ( $subscription )) {
			$subType = $this->getSubscriptionType ( $subscription ['subscriptionType'] );
			$subscription ['tierItemLabel'] = $subType ['tierItemLabel'];
		}
		return $subscription;
	}
}