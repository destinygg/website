<?php

namespace Destiny\Service;

use Destiny\Service;
use Destiny\Application;
use Destiny\Utils\Date;

class OrdersService extends Service {
	protected static $instance = null;

	/**
	 * Get the singleton instance
	 *
	 * @return OrdersService
	 */
	public static function instance() {
		return parent::instance ();
	}

	/**
	 * Add a 'New' order
	 *
	 * @param array $order
	 * @return int
	 */
	public function addOrder(array $order) {
		$conn = Application::instance ()->getConnection ();
		$conn->insert ( 'dfl_orders', array (
				'userId' => $order ['userId'],
				'amount' => $order ['amount'],
				'currency' => $order ['currency'],
				'description' => $order ['description'],
				'state' => 'New',
				'createdDate' => Date::getDateTime ( 'NOW' )->format ( 'Y-m-d H:i:s' ) 
		) );
		$order ['orderId'] = $conn->lastInsertId ();
		return $order ['orderId'];
	}

	/**
	 * Update an existing orders status
	 *
	 * @param int $id
	 * @param string $state
	 */
	public function updateOrderState($id, $state) {
		$conn = Application::instance ()->getConnection ();
		$conn->update ( 'dfl_orders', array (
				'state' => $state 
		), array (
				'orderId' => $id 
		) );
	}

	/**
	 * Get an order by Id
	 *
	 * @param int $orderId
	 */
	public function getOrderById($orderId) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( 'SELECT * FROM dfl_orders WHERE orderId = :orderId LIMIT 0,1' );
		$stmt->bindValue ( 'orderId', $orderId, \PDO::PARAM_INT );
		$stmt->execute ();
		return $stmt->fetch ();
	}

	/**
	 * Get a list of order items
	 *
	 * @param int $orderId
	 * @param int $limit
	 * @param int $start
	 */
	public function getOrderItems($orderId, $limit = 10, $start = 0) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT * FROM dfl_orders_items WHERE orderId = :orderId
			LIMIT :start,:limit
		' );
		$stmt->bindValue ( 'orderId', $orderId, \PDO::PARAM_INT );
		$stmt->bindValue ( 'start', $start, \PDO::PARAM_INT );
		$stmt->bindValue ( 'limit', $limit, \PDO::PARAM_INT );
		$stmt->execute ();
		return $stmt->fetchAll ();
	}

	/**
	 * Get a list of order items by the userId
	 *
	 * @param int $userId
	 * @param int $limit
	 * @param int $start
	 */
	public function getOrdersByUserId($userId, $limit = 10, $start = 0, $order = 'ASC') {
		if ($order != 'ASC' && $order != 'DESC') {
			$order = 'ASC';
		}
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT * FROM dfl_orders WHERE userId = :userId 
			ORDER BY createdDate ' . $order . '
			LIMIT :start,:limit
		' );
		$stmt->bindValue ( 'userId', $userId, \PDO::PARAM_INT );
		$stmt->bindValue ( 'start', $start, \PDO::PARAM_INT );
		$stmt->bindValue ( 'limit', $limit, \PDO::PARAM_INT );
		$stmt->execute ();
		return $stmt->fetchAll ();
	}

	/**
	 * Get a list of order items by the userId
	 *
	 * @param int $userId
	 * @param int $limit
	 * @param int $start
	 */
	public function getCompletedOrdersByUserId($userId, $limit = 10, $start = 0, $order = 'ASC') {
		if ($order != 'ASC' && $order != 'DESC') {
			$order = 'ASC';
		}
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT * FROM dfl_orders WHERE userId = :userId AND state != \'New\'
			ORDER BY createdDate ' . $order . '
			LIMIT :start,:limit
		' );
		$stmt->bindValue ( 'userId', $userId, \PDO::PARAM_INT );
		$stmt->bindValue ( 'start', $start, \PDO::PARAM_INT );
		$stmt->bindValue ( 'limit', $limit, \PDO::PARAM_INT );
		$stmt->execute ();
		return $stmt->fetchAll ();
	}

	/**
	 * Add a list of items to an order
	 *
	 * @param array $items
	 */
	public function addOrderItems(array $items) {
		$conn = Application::instance ()->getConnection ();
		foreach ( $items as $item ) {
			$conn->insert ( 'dfl_orders_items', array (
					'orderId' => $item ['orderId'],
					'itemSku' => $item ['itemSku'],
					'itemPrice' => $item ['itemPrice'] 
			) );
		}
	}

	/**
	 * Add a recurring payment profile
	 *
	 * @param array $profile
	 * @return int
	 */
	public function addPaymentProfile(array $profile) {
		$conn = Application::instance ()->getConnection ();
		$conn->insert ( 'dfl_orders_payment_profiles', array (
				'userId' => $profile ['userId'],
				'orderId' => $profile ['orderId'],
				'paymentProfileId' => $profile ['paymentProfileId'],
				'state' => $profile ['state'],
				'amount' => $profile ['amount'],
				'currency' => $profile ['currency'],
				'billingFrequency' => $profile ['billingFrequency'],
				'billingPeriod' => $profile ['billingPeriod'],
				'billingStartDate' => $profile ['billingStartDate'],
				'billingNextDate' => $profile ['billingNextDate'] 
		) );
		return $conn->lastInsertId ();
	}

	/**
	 * Insert an ipn record
	 *
	 * @return void
	 */
	public function addIpnRecord($ipn) {
		$conn = Application::instance ()->getConnection ();
		$conn->insert ( 'dfl_orders_ipn', array (
				'ipnTrackId' => $ipn ['ipnTrackId'],
				'ipnTransactionId' => $ipn ['ipnTransactionId'],
				'ipnTransactionType' => $ipn ['ipnTransactionType'],
				'ipnData' => $ipn ['ipnData'] 
		) );
	}

	/**
	 * This assumes there is only one profile per order
	 * - this wont be the case other than when you are in the process of making an order
	 *
	 * @todo dirty
	 * @param int $orderId
	 */
	public function getPaymentProfileByOrderId($orderId) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( 'SELECT * FROM dfl_orders_payment_profiles WHERE orderId = :orderId LIMIT 0,1' );
		$stmt->bindValue ( 'orderId', $orderId, \PDO::PARAM_INT );
		$stmt->execute ();
		return $stmt->fetch ();
	}

	/**
	 * This uses the PP paymentProfileId, not the autoincrement local Id
	 *
	 * @todo dirty
	 * @param int $orderId
	 */
	public function getPaymentProfileByPaymentProfileId($paymentProfileId) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( 'SELECT * FROM dfl_orders_payment_profiles WHERE paymentProfileId = :paymentProfileId LIMIT 0,1' );
		$stmt->bindValue ( 'paymentProfileId', $paymentProfileId, \PDO::PARAM_STR );
		$stmt->execute ();
		return $stmt->fetch ();
	}

	/**
	 * This uses the PP paymentProfileId, not the autoincrement local Id
	 *
	 * @todo dirty
	 * @param int $orderId
	 */
	public function getPaymentProfileById($profileId) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( 'SELECT * FROM dfl_orders_payment_profiles WHERE profileId = :profileId LIMIT 0,1' );
		$stmt->bindValue ( 'profileId', $profileId, \PDO::PARAM_INT );
		$stmt->execute ();
		return $stmt->fetch ();
	}

	/**
	 * Set a payment profile state to cancelled
	 *
	 * @param int $paymentProfile
	 * @param string $state
	 */
	public function updatePaymentProfileState($paymentProfileId, $state) {
		$conn = Application::instance ()->getConnection ();
		$conn->update ( 'dfl_orders_payment_profiles', array (
				'state' => $state 
		), array (
				'profileId' => $paymentProfileId 
		) );
	}

	/**
	 * Update a payment profile next payment date
	 *
	 * @param int $paymentProfileId
	 * @param \DateTime $billingNextDate
	 */
	public function updatePaymentProfileNextPayment($paymentProfileId, \DateTime $billingNextDate) {
		$conn = Application::instance ()->getConnection ();
		$conn->update ( 'dfl_orders_payment_profiles', array (
				'billingNextDate' => $billingNextDate->format ( 'Y-m-d H:i:s' ) 
		), array (
				'profileId' => $paymentProfileId 
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
	public function updatePaymentProfileStatus($profileId, $paymentProfileId, $state) {
		$conn = Application::instance ()->getConnection ();
		$conn->update ( 'dfl_orders_payment_profiles', array (
				'paymentProfileId' => $paymentProfileId,
				'state' => $state 
		), array (
				'profileId' => $profileId 
		) );
	}

	/**
	 * Get a payment by the transaction Id
	 *
	 * @param string $transactionId
	 */
	public function getPaymentByTransactionId($transactionId) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( 'SELECT * FROM dfl_orders_payments WHERE transactionId = :transactionId LIMIT 0,1' );
		$stmt->bindValue ( 'transactionId', $transactionId, \PDO::PARAM_STR );
		$stmt->execute ();
		return $stmt->fetch ();
	}

	/**
	 * Get a users payments
	 *
	 * @param int $userId
	 * @param int $limit
	 * @param int $start
	 */
	public function getPaymentsByUser($userId, $limit = 10, $start = 0) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT payments.* FROM dfl_orders_payments AS `payments`
			INNER JOIN dfl_orders AS `orders` ON (orders.orderId = payments.orderId)
			WHERE orders.userId = :userId
			ORDER BY payments.paymentDate DESC
			LIMIT :start,:limit
		' );
		$stmt->bindValue ( 'userId', $userId, \PDO::PARAM_INT );
		$stmt->bindValue ( 'start', $start, \PDO::PARAM_INT );
		$stmt->bindValue ( 'limit', $limit, \PDO::PARAM_INT );
		$stmt->execute ();
		return $stmt->fetchAll ();
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
		if ($order != 'ASC' && $order != 'DESC') {
			$order = 'ASC';
		}
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT payments.* FROM dfl_orders_payments AS `payments`
			INNER JOIN dfl_orders AS `orders` ON (orders.orderId = payments.orderId)
			WHERE orders.orderId = :orderId
			ORDER BY payments.paymentDate ' . $order . '
			LIMIT :start,:limit
		' );
		$stmt->bindValue ( 'orderId', $orderId, \PDO::PARAM_INT );
		$stmt->bindValue ( 'start', $start, \PDO::PARAM_INT );
		$stmt->bindValue ( 'limit', $limit, \PDO::PARAM_INT );
		$stmt->execute ();
		return $stmt->fetchAll ();
	}

	/**
	 * Return a payment by paymentId
	 *
	 * @param int $paymentId
	 * @return array
	 */
	public function getPaymentById($paymentId) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT payments.* FROM dfl_orders_payments AS `payments`
			WHERE payments.paymentId = :paymentId
			LIMIT 0,1
		' );
		$stmt->bindValue ( 'paymentId', $paymentId, \PDO::PARAM_INT );
		$stmt->execute ();
		return $stmt->fetch ();
	}

	/**
	 * Add an order payment
	 *
	 * @param array $payment
	 * @return int paymentId
	 */
	public function addOrderPayment(array $payment) {
		$conn = Application::instance ()->getConnection ();
		$conn->insert ( 'dfl_orders_payments', array (
				'orderId' => $payment ['orderId'],
				'amount' => $payment ['amount'],
				'currency' => $payment ['currency'],
				'transactionId' => $payment ['transactionId'],
				'transactionType' => $payment ['transactionType'],
				'paymentType' => $payment ['paymentType'],
				'payerId' => $payment ['payerId'],
				'paymentStatus' => $payment ['paymentStatus'],
				'paymentDate' => $payment ['paymentDate'],
				'createdDate' => Date::getDateTime ( 'NOW' )->format ( 'Y-m-d H:i:s' ) 
		) );
		return $conn->lastInsertId ();
	}

	/**
	 * Update an existing payments status
	 *
	 * @param array $payment
	 * @param string $state
	 */
	public function updatePaymentStatus(array $payment, $state) {
		$conn = Application::instance ()->getConnection ();
		$conn->update ( 'dfl_orders_payments', array (
				'state' => $state 
		), array (
				'profileId' => $payment ['paymentId'] 
		) );
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