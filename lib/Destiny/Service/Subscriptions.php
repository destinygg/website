<?php

namespace Destiny\Service;

use Destiny\Service;
use Destiny\Application;
use Destiny\Config;
use Destiny\Utils\Date;

class Subscriptions extends Service {
	
	/**
	 * var Service\Subscriptions
	 */
	protected static $instance = null;

	/**
	 *
	 * @return Service\Subscriptions
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
		throw new \Exception ( 'Subscription type not found' );
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
	 *
	 * @param int $userId
	 * @return array
	 */
	public function getUserActiveSubscription($userId) {
		$db = Application::getInstance ()->getDb ();
		return $db->select ( 'SELECT * FROM dfl_users_subscriptions WHERE endDate > NOW() AND userId = \'{userId}\' AND status = \'Active\' ORDER BY createdDate DESC LIMIT 0,1', array (
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
			WHERE recurring = 1 AND status = 'Active' AND endDate <= NOW() + INTERVAL 1 DAY
		" );
	}

}