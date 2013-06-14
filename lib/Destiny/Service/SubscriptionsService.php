<?php

namespace Destiny\Service;

use Destiny\Service;
use Destiny\Application;
use Destiny\Config;
use Destiny\Utils\Date;
use Destiny\AppException;

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
	public function addUserSubscription($userId, $subscription, $status, $paymentProfile) {
		$now = time ();
		$end = strtotime ( '+' . $subscription ['billingFrequency'] . ' ' . strtolower ( $subscription ['billingPeriod'] ), $now );
		$conn = Application::instance ()->getConnection ();
		$conn->insert ( 'dfl_users_subscriptions', array (
				'userId' => $userId,
				'createdDate' => Date::getDateTime ( $now, 'Y-m-d H:i:s' ),
				'endDate' => Date::getDateTime ( $end, 'Y-m-d H:i:s' ),
				'status' => $status,
				'recurring' => (empty ( $paymentProfile )),
				'paymentProfileId' => $paymentProfile ['profileId'] 
		) );
	}

	/**
	 * Get the first active subscription
	 * Note: This does not take into account end date.
	 *
	 * @param int $userId
	 * @return array
	 */
	public function getUserActiveSubscription($userId) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( 'SELECT * FROM dfl_users_subscriptions WHERE userId = :userId AND status = \'Active\' ORDER BY createdDate DESC LIMIT 0,1' );
		$stmt->bindValue ( 'userId', $userId, \PDO::PARAM_INT );
		$stmt->execute ();
		return $stmt->fetch ();
	}

	/**
	 * Update a subscriptions end date
	 *
	 * @param int $userId
	 * @param \DateTime $endDate
	 */
	public function updateUserSubscriptionDateEnd($userId, \DateTime $endDate) {
		$conn = Application::instance ()->getConnection ();
		$conn->update ( 'dfl_users_subscriptions', array (
				'endDate' => $endDate->format ( 'Y-m-d H:i:s' ) 
		), array (
				'userId' => $userId 
		) );
	}

	/**
	 * Update a subscriptions recurring field
	 *
	 * @param int $userId
	 * @param \DateTime $endDate
	 */
	public function updateUserSubscriptionRecurring($userId, $recurring) {
		$conn = Application::instance ()->getConnection ();
		$conn->update ( 'dfl_users_subscriptions', array (
				'recurring' => $recurring 
		), array (
				'userId' => $userId 
		) );
	}

	/**
	 * Update a subscriptions status
	 *
	 * @param int $userId
	 * @param \DateTime $endDate
	 */
	public function updateUserSubscriptionState($userId, $status) {
		$conn = Application::instance ()->getConnection ();
		$conn->update ( 'dfl_users_subscriptions', array (
				'status' => $status 
		), array (
				'userId' => $userId 
		) );
	}

	/**
	 * Sets subscription status to 'Expired' where the end date is smaller than NOW + 1 DAY
	 *
	 * @return int the number of expired subscriptions
	 */
	public function expiredSubscriptions() {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			UPDATE dfl_users_subscriptions SET `status` = \'Expired\'
			WHERE status = \'Active\' AND endDate + INTERVAL 24 HOUR <= NOW()
		' );
		$stmt->execute ();
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
		) );
	}

}