<?php

namespace Destiny\Action\Subscription;

use Destiny\Session;
use Destiny\Service\OrdersService;
use Destiny\Service\SubscriptionsService;
use Destiny\Utils\Http;
use Destiny\AppException;

class Cancel {

	public function execute(array $params) {
		if (! Session::hasRole ( \Destiny\UserRole::ADMIN )) {
			throw new AppException ( 'Must be an admin' );
		}
		$subscription = SubscriptionsService::instance ()->getUserActiveSubscription ( Session::get ( 'userId' ) );
		if (! empty ( $subscription )) {
			if (! empty ( $subscription ['paymentProfileId'] )) {
				$paymentProfile = OrdersService::instance ( OrdersService )->getPaymentProfileById ( $subscription ['paymentProfileId'] );
				if (strcasecmp ( $paymentProfile ['state'], 'ActiveProfile' ) === 0) {
					throw new AppException ( 'Please cancel the attached payment profile.' );
				}
			}
			SubscriptionsService::instance ()->updateSubscriptionState ( $subscription ['subscriptionId'], 'Cancelled' );
			
			// Update the credentials
			$credentials = Session::getCredentials ();
			Session::updateCredentials ( $credentials );
		}
		Http::header ( Http::HEADER_LOCATION, '/profile/subscription' );
		die ();
	}

}