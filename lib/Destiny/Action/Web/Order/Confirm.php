<?php
namespace Destiny\Action\Web\Order;

use Destiny\Common\Application;
use Destiny\Common\Service\SubscriptionsService;
use Destiny\Common\Session;
use Destiny\Common\ViewModel;
use Destiny\Common\AppException;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;

/**
 * @Action
 */
class Confirm {
	
	/**
	 * Unique checkout token
	 *
	 * @var string
	 */
	protected $checkoutId = '';

	/**
	 * @Route ("/order/confirm")
	 * @Secure ({"USER"})
	 *
	 * Create and send the order
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