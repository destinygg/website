<?php

namespace Destiny\Service;

use Destiny\Service;
use Destiny\Application;
use Destiny\Utils\Date;

class Orders extends Service {
	
	/**
	 * var Service\Orders
	 */
	protected static $instance = null;

	/**
	 * Get the singleton instance
	 *
	 * @return Service\Orders
	 */
	public static function getInstance() {
		return parent::getInstance ();
	}

	/**
	 * Add a 'New' order
	 *
	 * @param array $order
	 * @return int
	 */
	public function addOrder(array $order) {
		$db = Application::getInstance ()->getDb ();
		$order ['orderId'] = $db->insert ( "
			INSERT INTO dfl_orders(userId, state, amount, currency, description, createdDate)
			VALUES('{userId}', 'New', '{amount}', '{currency}', '{description}', NOW())
		", $order );
		return $order ['orderId'];
	}

	/**
	 * Update an existing orders status
	 *
	 * @param int $id
	 * @param string $state
	 */
	public function updateOrderState($id, $state) {
		$db = Application::getInstance ()->getDb ();
		$db->update ( 'UPDATE dfl_orders SET state = \'{state}\' WHERE orderId={orderId}', array (
				'orderId' => $id,
				'state' => $state 
		) );
	}

	/**
	 * Get an order by Id
	 *
	 * @param int $orderId
	 */
	public function getOrderById($orderId) {
		$db = Application::getInstance ()->getDb ();
		$result = $db->select ( 'SELECT * FROM dfl_orders WHERE orderId = \'{orderId}\'', array (
				'orderId' => $orderId 
		) );
		return $result->fetchRow ();
	}

	/**
	 * Get a list of order items
	 *
	 * @param int $orderId
	 * @param int $limit
	 * @param int $start
	 */
	public function getOrderItems($orderId, $limit = 10, $start = 0) {
		$db = Application::getInstance ()->getDb ();
		$result = $db->select ( '
			SELECT * FROM dfl_orders_items WHERE orderId = \'{orderId}\'
			LIMIT {start},{limit}		
		', array (
				'orderId' => $orderId,
				'start' => $start,
				'limit' => $limit 
		) );
		return $result->fetchRows ();
	}

	/**
	 * Get a list of order items by the userId
	 *
	 * @param int $userId
	 * @param int $limit
	 * @param int $start
	 */
	public function getOrdersByUserId($userId, $limit = 10, $start = 0, $order = 'ASC') {
		$db = Application::getInstance ()->getDb ();
		$result = $db->select ( 'SELECT * FROM dfl_orders WHERE userId = \'{userId}\' ORDER BY createdDate {order}  LIMIT {start},{limit}', array (
				'userId' => $userId,
				'start' => $start,
				'limit' => $limit,
				'order' => $order 
		) );
		return $result->fetchRows ();
	}

	/**
	 * Get a list of order items by the userId
	 *
	 * @param int $userId
	 * @param int $limit
	 * @param int $start
	 */
	public function getCompletedOrdersByUserId($userId, $limit = 10, $start = 0, $order = 'ASC') {
		$db = Application::getInstance ()->getDb ();
		$result = $db->select ( 'SELECT * FROM dfl_orders WHERE userId = \'{userId}\' AND state != \'New\' ORDER BY createdDate {order}  LIMIT {start},{limit}', array (
				'userId' => $userId,
				'start' => $start,
				'limit' => $limit,
				'order' => $order 
		) );
		return $result->fetchRows ();
	}

	/**
	 * Add a list of items to an order
	 *
	 * @param array $items
	 */
	public function addOrderItems(array $items) {
		$db = Application::getInstance ()->getDb ();
		foreach ( $items as $item ) {
			$db->insert ( "INSERT INTO dfl_orders_items(orderId, itemSku, itemPrice) VALUES ('{orderId}', '{itemSku}', '{itemPrice}')", $item );
		}
	}

	/**
	 * Add a recurring payment profile
	 *
	 * @param array $profile
	 * @return int
	 */
	public function addPaymentProfile(array $profile) {
		$db = Application::getInstance ()->getDb ();
		$profileId = $db->insert ( "
			INSERT INTO dfl_orders_payment_profiles (userId, orderId, paymentProfileId, state, amount, currency, billingFrequency, billingPeriod, billingStartDate, billingNextDate)
			VALUES('{userId}', '{orderId}', '{paymentProfileId}', '{state}', '{amount}', '{currency}', '{billingFrequency}', '{billingPeriod}', '{billingStartDate}', '{billingNextDate}')
		", $profile );
		return $profileId;
	}

	/**
	 * Insert an ipn record
	 *
	 * @return void
	 */
	public function addIpnRecord($ipn) {
		$db = Application::getInstance ()->getDb ();
		$db->insert ( "INSERT INTO dfl_orders_ipn (ipnTrackId, ipnTransactionId, ipnTransactionType, ipnData)VALUES('{ipnTrackId}', '{ipnTransactionId}', '{ipnTransactionType}', '{ipnData}')", $ipn );
	}

	/**
	 * This assumes there is only one profile per order
	 * - this wont be the case other than when you are in the process of making an order
	 *
	 * @todo dirty
	 * @param int $orderId
	 */
	public function getPaymentProfileByOrderId($orderId) {
		$db = Application::getInstance ()->getDb ();
		$result = $db->select ( "SELECT * FROM dfl_orders_payment_profiles WHERE orderId = '{orderId}' LIMIT 0,1", array (
				'orderId' => $orderId 
		) );
		return $result->fetchRow ();
	}

	/**
	 * This uses the PP paymentProfileId, not the autoincrement local Id
	 *
	 * @todo dirty
	 * @param int $orderId
	 */
	public function getPaymentProfileByPaymentProfileId($paymentProfileId) {
		$db = Application::getInstance ()->getDb ();
		$result = $db->select ( "SELECT * FROM dfl_orders_payment_profiles WHERE paymentProfileId = '{paymentProfileId}' LIMIT 0,1", array (
				'paymentProfileId' => $paymentProfileId 
		) );
		return $result->fetchRow ();
	}

	/**
	 * This uses the PP paymentProfileId, not the autoincrement local Id
	 *
	 * @todo dirty
	 * @param int $orderId
	 */
	public function getPaymentProfileById($profileId) {
		$db = Application::getInstance ()->getDb ();
		$result = $db->select ( "SELECT * FROM dfl_orders_payment_profiles WHERE profileId = '{profileId}' LIMIT 0,1", array (
				'profileId' => $profileId 
		) );
		return $result->fetchRow ();
	}

	/**
	 * Set a payment profile state to cancelled
	 *
	 * @param int $paymentProfile
	 * @param string $state
	 */
	public function updatePaymentProfileState($paymentProfileId, $state) {
		$db = Application::getInstance ()->getDb ();
		$db->insert ( "UPDATE dfl_orders_payment_profiles SET state = '{state}' WHERE profileId = '{profileId}'", array (
				'profileId' => $paymentProfileId,
				'state' => $state 
		) );
	}

	/**
	 * Update a payment profile next payment date
	 *
	 * @param int $paymentProfileId
	 * @param \DateTime $billingNextDate
	 */
	public function updatePaymentProfileNextPayment($paymentProfileId,\DateTime $billingNextDate) {
		$db = Application::getInstance ()->getDb ();
		$db->insert ( "UPDATE dfl_orders_payment_profiles SET billingNextDate = '{billingNextDate}' WHERE profileId = '{profileId}'", array (
				'profileId' => $paymentProfileId,
				'billingNextDate' => $billingNextDate->format ( 'Y-m-d H:i:s' ) 
		) );
	}

	/**
	 * Set the paymentProfileId, and state to "Active"
	 *
	 * @todo dirty
	 * @param int $profileId
	 * @param int $paymentProfileId
	 * @param string $status
	 */
	public function updatePaymentProfileStatus($profileId, $paymentProfileId, $status) {
		$db = Application::getInstance ()->getDb ();
		$db->insert ( "
			UPDATE dfl_orders_payment_profiles
			SET paymentProfileId = '{paymentProfileId}', state = '{state}'
			WHERE profileId = '{profileId}'
		", array (
				'profileId' => $profileId,
				'paymentProfileId' => $paymentProfileId,
				'state' => $status 
		) );
	}

	/**
	 * Get a payment by the transaction Id
	 *
	 * @param string $transactionId
	 */
	public function getPaymentByTransactionId($transactionId) {
		$db = Application::getInstance ()->getDb ();
		$result = $db->select ( 'SELECT * FROM dfl_orders_payments WHERE transactionId = \'{transactionId}\' LIMIT 0,1', array (
				'transactionId' => $transactionId 
		) );
		return $result->fetchRow ();
	}

	/**
	 * Get a users payments
	 *
	 * @param int $userId
	 * @param int $limit
	 * @param int $start
	 */
	public function getPaymentsByUser($userId, $limit = 10, $start = 0) {
		$db = Application::getInstance ()->getDb ();
		$result = $db->select ( '
				SELECT payments.* FROM dfl_orders_payments AS `payments`
				INNER JOIN dfl_orders AS `orders` ON (orders.orderId = payments.orderId)
				WHERE orders.userId = \'{userId}\'
				ORDER BY payments.paymentDate DESC
				LIMIT {start},{limit}', array (
				'userId' => $userId,
				'limit' => $limit,
				'start' => $start 
		) );
		return $result->fetchRows ();
	}

	/**
	 * Return payments by orderId
	 *
	 * @todo this returns payments in ASC order, the getPaymentsByUser returns them in DESC order
	 *      
	 * @param int $orderId
	 * @param int $limit
	 * @param int $start
	 */
	public function getPaymentsByOrderId($orderId, $limit = 1, $start = 0, $order = 'ASC') {
		$db = Application::getInstance ()->getDb ();
		$result = $db->select ( '
				SELECT payments.* FROM dfl_orders_payments AS `payments`
				INNER JOIN dfl_orders AS `orders` ON (orders.orderId = payments.orderId)
				WHERE orders.orderId = \'{orderId}\'
				ORDER BY payments.paymentDate {sortOrder}
				LIMIT {start},{limit}', array (
				'orderId' => $orderId,
				'limit' => $limit,
				'start' => $start,
				'sortOrder' => $order 
		) );
		return $result->fetchRows ();
	}

	/**
	 * Add an order payment
	 *
	 * @param array $payment
	 * @return int paymentId
	 */
	public function addOrderPayment(array $payment) {
		$db = Application::getInstance ()->getDb ();
		$paymentId = $db->insert ( "
				INSERT INTO dfl_orders_payments (orderId,amount,currency,transactionId,transactionType,paymentType,payerId,paymentStatus,paymentDate,createdDate)
				VALUES('{orderId}','{amount}','{currency}','{transactionId}','{transactionType}','{paymentType}','{payerId}','{paymentStatus}','{paymentDate}',NOW())
		", $payment );
		return $paymentId;
	}

	/**
	 * Update an existing payments status
	 *
	 * @param array $payment
	 * @param string $state
	 */
	public function updatePaymentStatus(array $payment, $state) {
		$db = Application::getInstance ()->getDb ();
		$db->insert ( "UPDATE dfl_orders_payments SET state = '{state}' WHERE profileId = '{profileId}'", array (
				'paymentId' => $payment ['paymentId'],
				'state' => $state 
		) );
	}

	/**
	 * Build an order reference string
	 *
	 * @param array $order
	 * @return string
	 */
	public function buildOrderRef(array $order) {
		return strtoupper ( base_convert ( strtotime ( $order ['createdDate'] ), 10, 36 ) ) . '-' . strlen ( $order ['userId'] ) . $order ['userId'] . strlen ( $order ['orderId'] ) . $order ['orderId'];
	}

	/**
	 * Returns an easier way to read a billing cycle
	 *
	 * @param int $frequency
	 * @param string $period
	 * @return string
	 */
	public function buildBillingCycleString($frequency, $period) {
		if ($frequency < 1) {
			return 'Never';
		}
		if ($frequency == 1) {
			return 'Once a ' . strtolower ( $period );
		}
		if ($frequency > 1) {
			return 'Every ' . $frequency . ' ' . strtolower ( $period ) . 's';
		}
		return '';
	}

}