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
	public static function getInstance() {
		return parent::getInstance ();
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
		$db = Application::getInstance ()->getDb ();
		$now = time ();
		$end = strtotime ( '+' . $subscription ['billingFrequency'] . ' ' . strtolower ( $subscription ['billingPeriod'] ), $now );
		$db->insert ( "
				INSERT INTO dfl_users_subscriptions
					(subscriptionId, userId, createdDate, endDate, status, recurring, paymentProfileId)
				VALUES
					(NULL, '{userId}', '{createdDate}', '{endDate}', '{status}', '{recurring}', '{paymentProfileId}')
		", array (
				'userId' => $userId,
				'createdDate' => Date::getDateTime ( $now, 'Y-m-d H:i:s' ),
				'endDate' => Date::getDateTime ( $end, 'Y-m-d H:i:s' ),
				'status' => $status,
				'recurring' => (empty ( $paymentProfile )) ? 0 : 1,
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
		$db = Application::getInstance ()->getDb ();
		return $db->select ( 'SELECT * FROM dfl_users_subscriptions WHERE userId = \'{userId}\' AND status = \'Active\' ORDER BY createdDate DESC LIMIT 0,1', array (
				'userId' => $userId 
		) )->fetchRow ();
	}

	/**
	 * Update a subscriptions end date
	 *
	 * @param int $userId
	 * @param \DateTime $endDate
	 */
	public function updateUserSubscriptionDateEnd($userId,\DateTime $endDate) {
		$db = Application::getInstance ()->getDb ();
		$db->insert ( "UPDATE dfl_users_subscriptions SET endDate = '{billingNextDate}' WHERE userId = '{userId}'", array (
				'userId' => $userId,
				'endDate' => $endDate->format ( 'Y-m-d H:i:s' ) 
		) );
	}

	/**
	 * Update a subscriptions recurring field
	 *
	 * @param int $userId
	 * @param \DateTime $endDate
	 */
	public function updateUserSubscriptionRecurring($userId, $recurring) {
		$db = Application::getInstance ()->getDb ();
		$db->insert ( "UPDATE dfl_users_subscriptions SET recurring = '{recurring}' WHERE userId = '{userId}'", array (
				'userId' => $userId,
				'recurring' => ($recurring) ? 1 : 0 
		) );
	}

	/**
	 * Update a subscriptions status
	 *
	 * @param int $userId
	 * @param \DateTime $endDate
	 */
	public function updateUserSubscriptionState($userId, $status) {
		$db = Application::getInstance ()->getDb ();
		$db->insert ( "UPDATE dfl_users_subscriptions SET status = '{status}' WHERE userId = '{userId}'", array (
				'userId' => $userId,
				'status' => $status 
		) );
	}

	/**
	 * Sets subscription status to 'Expired' where the end date is smaller than NOW + 1 DAY
	 *
	 * @return int the number of expired subscriptions
	 */
	public function expiredSubscriptions() {
		$db = Application::getInstance ()->getDb ();
		return $db->update ( "
			UPDATE dfl_users_subscriptions SET `status` = 'Expired'
			WHERE status = 'Active' AND endDate + INTERVAL 24 HOUR <= NOW()
		" );
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
		$db = Application::getInstance ()->getDb ();
		return $db->update ( "
			UPDATE dfl_users_subscriptions 
			SET `paymentProfileId` = '{paymentProfileId}', `recurring` = '{recurring}'
			WHERE subscriptionId = '{subscriptionId}'
		", array (
				'subscriptionId' => $subscriptionId,
				'paymentProfileId' => $profileId,
				'recurring' => ($recurring) ? '1' : '0' 
		) );
	}

}