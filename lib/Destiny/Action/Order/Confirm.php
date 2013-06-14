<?php

namespace Destiny\Action\Order;

use Destiny\Application;
use Destiny\Service\SubscriptionsService;
use Destiny\Session;
use Destiny\ViewModel;
use Destiny\AppException;

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
	 */
	public function execute(array $params, ViewModel $model) {
		$this->checkoutId = Session::get ( 'checkoutId' );
		// Make sure our checkoutId is valid
		if (! isset ( $params ['checkoutId'] ) || empty ( $this->checkoutId ) || $this->checkoutId != $params ['checkoutId']) {
			$model->error = new AppException ( 'Invalid checkout token' );
			return 'ordererror';
		}
		if (! isset ( $params ['subscription'] ) || empty ( $params ['subscription'] )) {
			$model->error = new AppException ( 'Empty subscription type' );
			return 'ordererror';
		}
		$recurringSubscription = (isset ( $params ['renew'] ) && $params ['renew'] == '1') ? true : false;
		$subscription = SubscriptionsService::instance ()->getSubscriptionType ( $params ['subscription'] );
		
		$model->subscription = $subscription;
		$model->renew = $recurringSubscription;
		$model->checkoutId = $this->checkoutId;
		return 'orderconfirm';
	}

}