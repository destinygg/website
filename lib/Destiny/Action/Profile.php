<?php

namespace Destiny\Action;

use Destiny\Session;
use Destiny\ViewModel;
use Destiny\Service\Orders;
use Destiny\Service\Subscriptions;

class Profile {

	public function execute(array $params, ViewModel $model) {
		$model->title = 'Profile';
		$orderService = Orders::getInstance ();
		$orders = $orderService->getCompletedOrdersByUserId ( Session::get ( 'userId' ), 10, 0, 'DESC' );
		for($i = 0; $i < count ( $orders ); ++ $i) {
			$orders [$i] ['orderReference'] = $orderService->buildOrderRef ( $orders [$i] );
			$payments = $orderService->getPaymentsByOrderId ( $orders [$i] ['orderId'], 10, 0, 'DESC' );
			if (empty ( $payments )) {
				$payments = array ();
			}
			$orders [$i] ['payments'] = $payments;
		}
		$model->orders = $orders;
		$model->subscription = Subscriptions::getInstance ()->getUserActiveSubscription ( Session::get ( 'userId' ) );
		return 'profile';
	}

}
