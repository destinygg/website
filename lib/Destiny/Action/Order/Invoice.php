<?php

namespace Destiny\Action\Order;

use Destiny\Utils\Http;
use Destiny\ViewModel;
use Destiny\Session;
use Destiny\Service\Orders;

class Invoice {

	public function execute(array $params, ViewModel $model) {
		if (isset ( $params ['orderId'] )) {
			$ordersService = Orders::getInstance ();
			$order = $ordersService->getOrderById ( $params ['orderId'] );
			// Make sure the order is for this user
			if (! empty ( $order ) && $order ['userId'] == Session::get ( 'userId' ) && strcasecmp ( $order ['state'], 'New' ) !== 0) {
				$model->order = $order;
				$model->orderItems = $ordersService->getOrderItems ( $order ['orderId'] );
				$model->orderReference = $ordersService->buildOrderRef ( $order );
				$model->paymentProfile = $ordersService->getPaymentProfileByOrderId ( $order ['orderId'] );
				$model->payments = $ordersService->getPaymentsByOrderId ( $order ['orderId'] );
				if (isset ( $model->payments [0] )) {
					$model->payment = $model->payments [0];
				}
			} else {
				throw new \Exception ( 'Order not owned' );
			}
		} else {
			throw new \Exception ( 'Invalid orderId' );
		}
		return 'invoice';
	}

}
