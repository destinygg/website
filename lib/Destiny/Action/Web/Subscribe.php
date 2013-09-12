<?php
namespace Destiny\Action\Web;

use Destiny\Common\Exception;
use Destiny\Common\ViewModel;
use Destiny\Common\Session;
use Destiny\Common\Config;
use Destiny\Common\Service\SubscriptionsService;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;

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
		$subscription = $subsService->getUserActiveSubscription ( Session::getCredentials ()->getUserId () );
		
		if (empty ( $subscription )) {
			$subscription = $subsService->getUserPendingSubscription ( Session::getCredentials ()->getUserId () );
			if (! empty ( $subscription )) {
				throw new Exception ( 'You already have a subscription in the "pending" state. Please cancel this first.' );
			}
		}
		
		// Setup the initial checkout token, the value is checked in each step, to make sure the user actually used the checkout process
		$this->checkoutId = md5 ( microtime ( true ) . Session::getCredentials ()->getUserId () );
		Session::set ( 'checkoutId', $this->checkoutId );
		$model->title = 'Subscribe';
		$model->subscriptions = Config::$a ['commerce'] ['subscriptions'];
		$model->subscription = $subscription;
		$model->checkoutId = $this->checkoutId;
		return 'subscribe';
	}

}