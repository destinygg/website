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
	 */
	public function getSubscriptionType($subscriptionId) {
		$subscriptions = Config::$a ['commerce'] ['subscriptions'];
		if (! empty ( $subscriptionId ) && isset ( $subscriptions [$subscriptionId] )) {
			return $subscriptions [$subscriptionId];
		}
		return null;
	}

	/**
	 * @param int $userId
	 * @param array $subscription
	 */
	public function addUserSubscription($userId, $subscription) {
		$db = Application::getInstance ()->getDb ();
		$activeSubs = $this->getUserActiveSubscription ( $userId );
		if (empty ( $activeSubs )) {
			// new sub
			$now = time ();
			$end = strtotime ( $subscription ['length'], $now );
			$db->insert ( "INSERT INTO dfl_users_subscriptions(subscriptionId, userId, createdDate, endDate) VALUES(NULL, '{userId}', '{createdDate}', '{endDate}')", array (
					'userId' => $userId,
					'createdDate' => Date::getDateTime ( $now, 'Y-m-d H:i:s' ),
					'endDate' => Date::getDateTime ( $end, 'Y-m-d H:i:s' ) 
			) );
		} else {
			// extend sub
			$endDate = Date::getDateTime ( $activeSubs ['endDate'] );
			$endDate->modify ( $subscription ['length'] );
			$db->update ( "UPDATE dfl_users_subscriptions SET endDate = '{endDate}' WHERE userId = '{userId}' AND subscriptionId = '{subscriptionId}'", array (
					'userId' => $userId,
					'subscriptionId' => $activeSubs ['subscriptionId'],
					'endDate' => $endDate->format ( 'Y-m-d H:i:s' ) 
			) );
		}
	}

	/**
	 * @param int $userId
	 * @return array
	 */
	public function getUserActiveSubscription($userId) {
		$db = Application::getInstance ()->getDb ();
		return $db->select ( 'SELECT * FROM dfl_users_subscriptions WHERE endDate > NOW() AND userId = \'{userId}\' ORDER BY createdDate DESC LIMIT 0,1', array('userId'=>$userId) )->fetchRow ();
	}
}