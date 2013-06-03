<?php
namespace Destiny\Action\Order;

use Destiny\Application;
use Destiny\Session;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Amount;
use PayPal\Api\CreditCard;
use PayPal\Api\CreditCardToken;
use PayPal\Api\FundingInstrument;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\Transaction;
use PayPal\Api\RedirectUrls;

class Complete {

	/**
	 * We were redirected here from PayPal after the buyer approved/cancelled the payment
	 *
	 * @param array $params
	 * @throws \Exception
	 */
	public function execute(array $params) {
		if (! Session::getAuthorized ()) {
			throw new \Exception ( 'User required' );
		}
		$app = Application::getInstance ();
		if (isset ( $params ['success'] )) {
			if ($params ['success'] == 'true' && isset ( $params ['PayerID'] ) && isset ( $params ['orderId'] )) {
				$ordersService = \Destiny\Service\Orders::getInstance ();
				$order = $ordersService->getOrderById ( $params ['orderId'] );
				$order ['items'] = $ordersService->getOrderItems ( $order ['orderId'] );
				$ordersService->setOrder ( $order );
				try {
					// Order not found
					if (! isset ( $order ['orderId'] ) || empty ( $order ['orderId'] )) {
						throw new \Exception ( 'Order not found' );
					}
					// If this order is already approved
					if (strcasecmp ( $order ['state'], 'approved' ) === 0) {
						$app->template ( './tpl/order.php' );
					}
					// If this order is already not "created" and not approved
					if (strcasecmp ( $order ['state'], 'created' ) != 0) {
						$e = new \Exception ( 'Payment state error' );
						$app->error ( 500, $e );
					}
					
					// Execute the payment
					$payment = Payment::get ( $order ['paymentId'] );
					$paymentExecution = new PaymentExecution ();
					$paymentExecution->setPayer_id ( $params ['PayerID'] );
					$payment->execute ( $paymentExecution );
					
					// Update order
					$ordersService->updateOrder ( $order ['orderId'], $payment->getState () );
					// If we have an approved order, create / extend the subscription
					if (strcasecmp ( $payment->getState (), 'approved' ) === 0) {
						$subsService = \Destiny\Service\Subscriptions::getInstance ();
						foreach ( $order ['items'] as $item ) {
							$subscription = $subsService->getSubscriptionType ( $item ['itemSku'] );
							if (! empty ( $subscription )) {
								$subsService->addUserSubscription ( Session::$userId, $subscription );
							}
						}
					}
					// We show the success screen regardless of state at this point
					$app->template ( './tpl/order.php' );
					
				} catch ( \Exception $e ) {
					// Update order
					$ordersService->updateOrder ( $order ['orderId'], 'error' );
					$app->error ( 500, $e );
				}
			} else {
				$e = new \Exception ( 'Your payment was cancelled.' );
				$app->error ( 500, $e );
			}
		}
		$e = new \Exception ( 'Payment error' );
		$app->error ( 500, $e );
	}

	/**
	 * Utility function to pretty print API error data
	 *
	 * @param string $errorJson
	 * @return string
	 */
	protected function parseApiError(\Exception $ex) {
		$errorJson = $ex->getData ();
		$msg = '';
		$data = json_decode ( $errorJson, true );
		if (isset ( $data ['name'] ) && isset ( $data ['message'] )) {
			$msg .= $data ['name'] . " : " . $data ['message'] . "<br/>";
		}
		if (isset ( $data ['details'] )) {
			$msg .= "<ul>";
			foreach ( $data ['details'] as $detail ) {
				$msg .= "<li>" . $detail ['field'] . " : " . $detail ['issue'] . "</li>";
			}
			$msg .= "</ul>";
		}
		if ($msg == '') {
			$msg = $errorJson;
		}
		return $msg;
	}

}