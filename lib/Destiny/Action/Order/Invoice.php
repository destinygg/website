<?php

namespace Destiny\Action\Order;

use Destiny\Utils\Http;
use Destiny\ViewModel;
use Destiny\Session;
use Destiny\Service\OrdersService;
use Destiny\AppException;

class Invoice {

	public function execute(array $params, ViewModel $model) {
		if (isset ( $params ['orderId'] )) {
			$ordersService = OrdersService::instance ();
			$order = $ordersService->getOrderById ( $params ['orderId'] );
			
			if (empty ( $order )) {
				throw new AppException ( 'Order not found' );
			}
			if ($order ['userId'] != Session::get ( 'userId' )) {
				throw new AppException ( 'Permission denied' );
			}
			if (strcasecmp ( $order ['state'], 'New' ) === 0) {
				throw new AppException ( 'Invalid order status' );
			}
			
			// Make sure the order is for this user
			$model->order = $order;
			$model->orderItems = $ordersService->getOrderItems ( $order ['orderId'] );
			$model->orderReference = $ordersService->buildOrderRef ( $order );
			$model->paymentProfile = $ordersService->getPaymentProfileByOrderId ( $order ['orderId'] );
			$model->payments = $ordersService->getPaymentsByOrderId ( $order ['orderId'] );
			if (isset ( $model->payments [0] )) {
				$model->payment = $model->payments [0];
			}
		} else {
			throw new AppException ( 'Invalid orderId' );
		}
		return 'invoice';
	}

}
