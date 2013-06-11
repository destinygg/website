<?php

namespace Destiny\Action;

use Destiny\Application;
use Destiny\ViewModel;
use Destiny\Session;
use Destiny\Service\Subscriptions;

class Subscribe {
	
	/**
	 * Unique checkout token
	 *
	 * @var string
	 */
	protected $checkoutId = '';

	/**
	 * Build subscribe checkout form
	 *
	 * @param array $params
	 */
	public function execute(array $params, ViewModel $model) {
		// Setup the initial checkout token, the value is checked in each step, to make sure the user actually used the checkout process
		$this->checkoutId = md5 ( microtime ( true ) . Session::get ( 'userId' ) );
		Session::set ( 'checkoutId', $this->checkoutId );
		$model->title = 'Subscribe';
		$model->subscription = Subscriptions::getInstance ()->getUserActiveSubscription ( Session::get ( 'userId' ) );
		$model->checkoutId = $this->checkoutId;
		return 'subscribe';
	}

}