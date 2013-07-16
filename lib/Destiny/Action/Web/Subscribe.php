<?php
namespace Destiny\Action\Web;

use Destiny\Common\Application;
use Destiny\Common\ViewModel;
use Destiny\Common\Session;
use Destiny\Common\Config;
use Destiny\Common\Service\SubscriptionsService;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;

/**
 * @Action
 */
class Subscribe {
	
	/**
	 * Unique checkout token
	 *
	 * @var string
	 */
	protected $checkoutId = '';

	/**
	 * @Route ("/subscribe")
	 *
	 * Build subscribe checkout form
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