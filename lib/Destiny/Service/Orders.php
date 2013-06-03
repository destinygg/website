<?php
namespace Destiny\Service;

use Destiny\Service;
use Destiny\Application;

class Orders extends Service {
	
	/**
	 * Used to temp store a users order
	 * @var array
	 */
	public $order = null;

	/**
	 * var Service\Orders
	 */
	protected static $instance = null;

	/**
	 * @return Service\Orders
	 */
	public static function getInstance() {
		return parent::getInstance ();
	}

	/**
	 * Create a new order
	 *
	 * @param array $order
	 * @throws Exception
	 */
	public function addOrder(array $order) {
		$db = Application::getInstance ()->getDb ();
		$order ['orderId'] = $db->insert ( "
			INSERT INTO dfl_orders(userId, state, amount, currency, description, createdDate)
			VALUES('{userId}', 'new', '{amount}', '{currency}', '{description}', NOW())
		", $order );
		return $order ['orderId'];
	}

	/**
	 * Update a previously created order.
	 *
	 * @param int $orderId
	 * @param string $state
	 * @param string $paymentId
	 * @throws Exception
	 * @return number
	 */
	public function updateOrder($orderId, $state, $paymentId = NULL) {
		$db = Application::getInstance ()->getDb ();
		$params = array (
				'orderId' => $orderId,
				'state' => $state,
				'paymentId' => $paymentId 
		);
		if ($paymentId == NULL) {
			$db->update ( 'UPDATE dfl_orders SET state = \'{state}\' WHERE orderId={orderId}', $params );
		} else {
			$db->update ( 'UPDATE dfl_orders SET state = \'{state}\', paymentId = \'{paymentId}\' WHERE orderId={orderId}', $params );
		}
		return true;
	}

	/**
	 * @param int $orderId
	 * @return array
	 */
	public function getOrderById($orderId) {
		$db = Application::getInstance ()->getDb ();
		$result = $db->select ( '
			SELECT * FROM dfl_orders WHERE orderId = \'{orderId}\'		
		', array (
				'orderId' => $orderId
		) );
		return $result->fetchRow ();
	}

	/**
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
	 * @param array $items
	 */
	public function addOrderItems(array $items){
		$db = Application::getInstance ()->getDb ();
		foreach ( $items as $item ) {
			$db->insert ( "
				INSERT INTO dfl_orders_items(orderId, itemSku, itemPrice)
				VALUES('{orderId}', '{itemSku}', '{itemPrice}')
			", $item );
		}
	}

	/**
	 * @param array $order
	 * @return string
	 */
	public function buildOrderRef(array $order) {
		return '#ORDER-' . $order['userId'] . '-' . $order ['orderId'];
	}

	/**
	 * @return array
	 */
	public function getOrder() {
		return $this->order;
	}

	/**
	 * @param array $order
	 */
	public function setOrder(array $order) {
		$this->order = $order;
	}

}