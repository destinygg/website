<?php

namespace Destiny\Action;

use Destiny\Session;
use Destiny\ViewModel;
use Destiny\Service\OrdersService;
use Destiny\Service\SubscriptionsService;

class Profile {

	public function execute(array $params, ViewModel $model) {
		$model->title = 'Profile';
		$model->user = Session::getAuthCreds ()->getCredentials ();
		$orderService = OrdersService::instance ();
		$subsService = SubscriptionsService::instance ();
		$model->payments = $orderService->getPaymentsByUser ( Session::get ( 'userId' ), 10, 0 );
		$subscription = $subsService->getUserActiveSubscription ( Session::get ( 'userId' ) );
		$paymentProfile = null;
		if (! empty ( $subscription ) && ! empty ( $subscription ['paymentProfileId'] )) {
			$paymentProfile = $orderService->getPaymentProfileById ( $subscription ['paymentProfileId'] );
			if (! empty ( $paymentProfile )) {
				$paymentProfile ['billingCycle'] = $orderService->buildBillingCycleString ( $paymentProfile ['billingFrequency'], $paymentProfile ['billingPeriod'] );
			}
		}
		$model->paymentProfile = $paymentProfile;
		$model->subscription = $subscription;
		return 'profile';
	}

}
