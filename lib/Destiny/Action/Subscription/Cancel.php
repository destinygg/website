<?php
namespace Destiny\Action\Subscription;

use Destiny\ViewModel;
use Destiny\Session;
use Destiny\Service\OrdersService;
use Destiny\Service\SubscriptionsService;
use Destiny\Utils\Http;
use Destiny\AppException;

class Cancel {

	public function executePost(array $params, ViewModel $model) {
		if (! Session::hasRole ( \Destiny\UserRole::ADMIN )) {
			throw new AppException ( 'Must be an admin' );
		}
		$subscription = SubscriptionsService::instance ()->getUserActiveSubscription ( Session::get ( 'userId' ) );
		if (! empty ( $subscription )) {
			
			if (! empty ( $subscription ['paymentProfileId'] )) {
				$paymentProfile = OrdersService::instance ( OrdersService )->getPaymentProfileById ( $subscription ['paymentProfileId'] );
				if (strcasecmp ( $paymentProfile ['state'], 'ActiveProfile' ) === 0) {
					throw new AppException ( 'Please first cancel the attached payment profile.' );
				}
			}
			// Update the credentials
			$credentials = Session::getCredentials ();
			$credentials->removeRole ( \Destiny\UserRole::SUBSCRIBER );
			$credentials->removeFeature ( \Destiny\UserFeature::SUBSCRIBER );
			Session::updateCredentials ( $credentials );
			
			$subscription ['status'] = 'Cancelled';
			SubscriptionsService::instance ()->updateSubscriptionState ( $subscription ['subscriptionId'], $subscription ['status'] );
			
			$model->subscription = $subscription;
			$model->subscriptionCancelled = true;
			return 'profile/cancelsubscription';
		}
		Http::header ( Http::HEADER_LOCATION, '/profile' );
		die ();
	}

	public function executeGet(array $params, ViewModel $model) {
		$subscription = SubscriptionsService::instance ()->getUserActiveSubscription ( Session::get ( 'userId' ) );
		if (empty ( $subscription )) {
			throw new AppException ( 'Must have an active subscription' );
		}
		if (! empty ( $subscription ['paymentProfileId'] )) {
			$paymentProfile = OrdersService::instance ( OrdersService )->getPaymentProfileById ( $subscription ['paymentProfileId'] );
			if (strcasecmp ( $paymentProfile ['state'], 'ActiveProfile' ) === 0) {
				throw new AppException ( 'Please first cancel the attached payment profile.' );
			}
		}
		$model->subscription = $subscription;
		return 'profile/cancelsubscription';
	}

}