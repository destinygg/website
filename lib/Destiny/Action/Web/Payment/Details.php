<?php
namespace Destiny\Action\Web\Payment;

use Destiny\Common\Exception;
use Destiny\Common\ViewModel;
use Destiny\Common\Session;
use Destiny\Common\Service\OrdersService;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\Secure;

/**
 * @Action
 */
class Details {

	/**
	 * @Route ("/payment/{id}/details")
	 * @Secure ({"USER"})
	 *
	 * @param array $params
	 * @param ViewModel $model
	 * @throws Exception
	 * @return string
	 */
	public function execute(array $params, ViewModel $model) {
		if (isset ( $params ['id'] )) {
			$ordersService = OrdersService::instance ();
			
			$payment = $ordersService->getPaymentById ( intval ( $params ['id'] ) );
			$order = $ordersService->getOrderById ( intval ( $payment ['orderId'] ) );
			
			if (empty ( $order )) {
				throw new Exception ( 'Order not found' );
			}
			if ($order ['userId'] != Session::getCredentials ()->getUserId ()) {
				throw new Exception ( 'Permission denied' );
			}
			if (strcasecmp ( $order ['state'], 'New' ) === 0) {
				throw new Exception ( 'Invalid order status' );
			}
			
			// Make sure the order is for this user
			$model->order = $order;
			$model->orderItems = $ordersService->getOrderItems ( $order ['orderId'] );
			$model->paymentProfile = $ordersService->getPaymentProfileByOrderId ( $order ['orderId'] );
			$model->payment = $payment;
		} else {
			throw new Exception ( 'Invalid paymentId' );
		}
		return 'payment';
	}

}