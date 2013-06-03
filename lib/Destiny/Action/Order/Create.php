<?php
namespace Destiny\Action\Order;

use Destiny\Service\Twitch\Subscription;

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
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use Destiny\Session;
use Destiny\Utils\Http;
use Destiny\Service\Orders;
use Destiny\Service\Subscriptions;
use Destiny\Config;

class Create {

	public function execute(array $params) {
		if (! Session::getAuthorized ()) {
			throw new \Exception ( 'User required' );
		}
		$ordersService = Orders::getInstance ();
		$subsService = Subscriptions::getInstance ();
		
		$subscriptionId = $params ['subscription'];
		$subscription = $subsService->getSubscriptionType ( $subscriptionId );
		
		if($subscription == null){
			throw new \Exception ( 'Subscription type not supported' );
		}
		
		$currency = 'USD';
		$order = array (
			'userId' => Session::$userId,
			'description' => $subscription ['label'],
			'amount' => $subscription ['amount'],
			'currency' => $currency 
		);
		$order ['orderId'] = $ordersService->addOrder ( $order );
		$order ['items'] = array (
			0 => array(
				'orderId' => $order ['orderId'],
				'itemSku' => $subscriptionId,
				'itemPrice' => $subscription ['amount'] 
			)
		);
		$ordersService->addOrderItems ( $order ['items'] );
		$baseUrl = Http::getBaseUrl () . '/Order/Complete?orderId=' . $order ['orderId'];
		
		$payer = new Payer ();
		$payer->setPayment_method ( 'paypal' );
		
		$amount = new Amount ();
		$amount->setCurrency ( $currency );
		$amount->setTotal ( $order ['amount'] );
			
		$item = new Item ();
		$item->setSku ( $subscriptionId );
		$item->setCurrency ( $currency );
		$item->setName ( $subscription ['label'] );
		$item->setPrice ( $order ['amount'] );
		$item->setQuantity ( 1 );
		
		$itemlist = new ItemList ();
		$itemlist->setItems ( array ($item) );
		
		$transaction = new Transaction ();
		$transaction->setAmount ( $amount );
		$transaction->setDescription ( $order ['description'] );
		$transaction->setItem_list ( $itemlist );
		
		$redirectUrls = new RedirectUrls ();
		$redirectUrls->setReturn_url ( "$baseUrl&success=true" );
		$redirectUrls->setCancel_url ( "$baseUrl&success=false" );
		
		$payment = new Payment ();
		$payment->setRedirect_urls ( $redirectUrls );
		$payment->setIntent ( 'sale' );
		$payment->setPayer ( $payer );
		$payment->setTransactions ( array ($transaction) );
		
		try {
			$payment->create ();
			$ordersService->updateOrder ( $order ['orderId'], $payment->getState (), $payment->getId () );
			// Forward to Paypal website
			Http::header ( Http::HEADER_LOCATION, $this->getLink ( $payment->getLinks (), 'approval_url' ) . '&useraction=commit' );
		} catch ( \Exception $e ) {
			$ordersService->updateOrder ( $order ['orderId'], 'error', '' );
			throw $e;
		}
		exit ();
	}

	/**
	 * Utility method that returns the first url of a certain
	 * type.
	 * Returns empty string if no match is found
	 *
	 * @param array $links
	 * @param string $type
	 * @return string
	 */
	protected function getLink(array $links, $type) {
		foreach ( $links as $link ) {
			if ($link->getRel () == $type) {
				return $link->getHref ();
			}
		}
		return '';
	}

}