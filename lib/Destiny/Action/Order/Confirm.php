<?php

namespace Destiny\Action\Order;

use Destiny\Application;
use Destiny\Service\Subscriptions;
use Destiny\Session;
use Destiny\ViewModel;

class Confirm {
	
	/**
	 * Unique checkout token
	 *
	 * @var string
	 */
	protected $checkoutId = '';

	/**
	 * Create and send the order
	 *
	 * @param array $params
	 * @throws \Exception
	 */
	public function execute(array $params, ViewModel $model) {
		$this->checkoutId = Session::get ( 'checkoutId' );
		// Make sure our checkoutId is valid
		if (! isset ( $params ['checkoutId'] ) || empty ( $this->checkoutId ) || $this->checkoutId != $params ['checkoutId']) {
			throw new \Exception ( 'Invalid checkout token' );
		}
		if (! isset ( $params ['subscription'] ) || empty ( $params ['subscription'] )) {
			throw new \Exception ( 'Empty subscription type' );
		}
		$recurringSubscription = (isset ( $params ['renew'] ) && $params ['renew'] == '1') ? true : false;
		$subscription = Subscriptions::getInstance ()->getSubscriptionType ( $params ['subscription'] );
		
		$model->subscription = $subscription;
		$model->renew = $recurringSubscription;
		$model->checkoutId = $this->checkoutId;
		return 'orderconfirm';
	}

}