<?php
namespace Destiny\Action\Web\Profile;

use Destiny\Common\Commerce\SubscriptionStatus;
use Destiny\Common\Service\UserService;
use Destiny\Common\Session;
use Destiny\Common\ViewModel;
use Destiny\Common\Service\OrdersService;
use Destiny\Common\Service\SubscriptionsService;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;

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
		
		$subscription = $subsService->getUserActiveSubscription ( $userId );
		if (empty ( $subscription )) {
			$subscription = $subsService->getUserPendingSubscription ( $userId );
		}
		
		$paymentProfile = null;
		// Add the subscriptions payment profile, if it has one
		// a little dirty
		if (! empty ( $subscription ) && ! empty ( $subscription ['paymentProfileId'] )) {
			$paymentProfile = $orderService->getPaymentProfileById ( $subscription ['paymentProfileId'] );
			if (! empty ( $paymentProfile )) {
				$paymentProfile ['billingCycle'] = $orderService->buildBillingCycleString ( $paymentProfile ['billingFrequency'], $paymentProfile ['billingPeriod'] );
			}
		}
		
		$model->title = 'Subscription';
		$model->user = $userService->getUserById ( $userId );
		$model->payments = $orderService->getPaymentsByUser ( $userId, 10, 0 );
		$model->paymentProfile = $paymentProfile;
		$model->subscription = $subscription;
		return 'profile/subscription';
	}

}