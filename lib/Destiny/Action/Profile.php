<?php

namespace Destiny\Action;

use Destiny\Session;
use Destiny\ViewModel;
use Destiny\Service\OrdersService;
use Destiny\Service\SubscriptionsService;

class Profile {

	public function execute(array $params, ViewModel $model) {
		$model->title = 'Profile';
		$orderService = OrdersService::instance ();
		$subsService = SubscriptionsService::instance ();
		$orders = $orderService->getCompletedOrdersByUserId ( Session::get ( 'userId' ), 5, 0, 'DESC' );
		for($i = 0; $i < count ( $orders ); ++ $i) {
			$orders [$i] ['orderReference'] = $orderService->buildOrderRef ( $orders [$i] );
			$payments = $orderService->getPaymentsByOrderId ( $orders [$i] ['orderId'], 10, 0, 'DESC' );
			if (empty ( $payments )) {
				$payments = array ();
			}
			$orders [$i] ['payments'] = $payments;
		}
		$model->orders = $orders;
		$model->subscription = $subsService->getUserActiveSubscription ( Session::get ( 'userId' ) );
		$model->paymentProfile = null;
		if (! empty ( $model->subscription ['paymentProfileId'] )) {
			$paymentProfile = $orderService->getPaymentProfileById ( $model->subscription ['paymentProfileId'] );
			if (! empty ( $paymentProfile )) {
				$paymentProfile ['billingCycle'] = $orderService->buildBillingCycleString ( $paymentProfile ['billingFrequency'], $paymentProfile ['billingPeriod'] );
				$model->paymentProfile = $paymentProfile;
			}
		}
		
		return 'profile';
	}

}
