<?php
namespace Destiny\Action\Profile;

use Destiny\Service\UserService;
use Destiny\Session;
use Destiny\ViewModel;
use Destiny\Service\OrdersService;
use Destiny\Service\SubscriptionsService;
use Destiny\Annotation\Action;
use Destiny\Annotation\Route;
use Destiny\Annotation\HttpMethod;
use Destiny\Annotation\Secure;

/**
 * @Action
 */
class Subscription {

	/**
	 * @Route ("/profile/subscription")
	 * @Secure ({"USER"})
	 *
	 * @param array $params
	 * @param ViewModel $model
	 * @return string
	 */
	public function execute(array $params, ViewModel $model) {
		$subsService = SubscriptionsService::instance ();
		$orderService = OrdersService::instance ();
		$userService = UserService::instance ();
		$userId = Session::get ( 'userId' );
		$model->title = 'Subscription';
		$model->user = $userService->getUserById ( $userId );
		$model->payments = $orderService->getPaymentsByUser ( $userId, 10, 0 );
		$subscription = $subsService->getUserActiveSubscription ( $userId );
		$paymentProfile = null;
		// Add the subscriptions payment profile, if it has one
		// a little dirty
		if (! empty ( $subscription ) && ! empty ( $subscription ['paymentProfileId'] )) {
			$paymentProfile = $orderService->getPaymentProfileById ( $subscription ['paymentProfileId'] );
			if (! empty ( $paymentProfile )) {
				$paymentProfile ['billingCycle'] = $orderService->buildBillingCycleString ( $paymentProfile ['billingFrequency'], $paymentProfile ['billingPeriod'] );
			}
		}
		$model->paymentProfile = $paymentProfile;
		$model->subscription = $subscription;
		return 'profile/subscription';
	}

}