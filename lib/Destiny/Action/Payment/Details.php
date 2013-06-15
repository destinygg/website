<?php

namespace Destiny\Action\Payment;

use Destiny\Application;
use Destiny\AppException;
use Destiny\ViewModel;
use Destiny\Session;
use Destiny\Service\OrdersService;

class Details {

	public function execute(array $params, ViewModel $model) {
		if (isset ( $params ['id'] )) {
			$ordersService = OrdersService::instance ();
			
			$payment = $ordersService->getPaymentById ( intval ( $params ['id'] ) );
			$order = $ordersService->getOrderById ( intval ( $payment ['orderId'] ) );
			
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
			$model->paymentProfile = $ordersService->getPaymentProfileByOrderId ( $order ['orderId'] );
			$model->payment = $payment;
		} else {
			throw new AppException ( 'Invalid paymentId' );
		}
		return 'payment';
	}

}