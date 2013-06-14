<?php

namespace Destiny\Action;

use Destiny\Application;
use Destiny\ViewModel;
use Destiny\Session;
use Destiny\Config;
use Destiny\Service\SubscriptionsService;

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
		$subsService = SubscriptionsService::instance ();
		$subsService->getUserActiveSubscription ( Session::get ( 'userId' ) );
		
		// Setup the initial checkout token, the value is checked in each step, to make sure the user actually used the checkout process
		$this->checkoutId = md5 ( microtime ( true ) . Session::get ( 'userId' ) );
		Session::set ( 'checkoutId', $this->checkoutId );
		$model->title = 'Subscribe';
		$model->subscriptions = Config::$a ['commerce'] ['subscriptions'];
		$model->subscription = $subsService->getUserActiveSubscription ( Session::get ( 'userId' ) );
		$model->checkoutId = $this->checkoutId;
		return 'subscribe';
	}

}