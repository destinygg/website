<?php
namespace Destiny\Action\Web\Subscription;

use Destiny\Common\Service\AuthenticationService;
use Destiny\Common\Commerce\PaymentProfileStatus;
use Destiny\Common\Commerce\SubscriptionStatus;
use Destiny\Common\ViewModel;
use Destiny\Common\Session;
use Destiny\Common\Service\OrdersService;
use Destiny\Common\Service\SubscriptionsService;
use Destiny\Common\Exception;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Annotation\Transactional;

/**
 * @Action
 */
class Cancel {

	/**
	 * @Route ("/subscription/cancel")
	 * @Secure ({"USER"})
	 * @HttpMethod ({"POST"})
	 * @Transactional
	 *
	 * @param array $params
	 * @param ViewModel $model
	 * @throws Exception
	 * @return string
	 */
	public function executePost(array $params, ViewModel $model) {
		$userId = Session::getCredentials ()->getUserId ();
		$subscription = SubscriptionsService::instance ()->getUserActiveSubscription ( $userId );
		if (! empty ( $subscription )) {
			
			if (! empty ( $subscription ['paymentProfileId'] )) {
				$paymentProfile = OrdersService::instance ()->getPaymentProfileById ( $subscription ['paymentProfileId'] );
				if (strcasecmp ( $paymentProfile ['state'], PaymentProfileStatus::ACTIVEPROFILE ) === 0) {
					throw new Exception ( 'Please first cancel the attached payment profile.' );
				}
			}
			
			$subscription ['status'] = SubscriptionStatus::CANCELLED;
			SubscriptionsService::instance ()->updateSubscriptionState ( $subscription ['subscriptionId'], $subscription ['status'] );
			AuthenticationService::instance ()->flagUserForUpdate ( $userId );
			
			$model->subscription = $subscription;
			$model->subscriptionCancelled = true;
			return 'profile/cancelsubscription';
		}
		return 'redirect: /profile';
	}

	/**
	 * @Route ("/subscription/cancel")
	 * @Secure ({"USER"})
	 * @HttpMethod ({"GET"})
	 *
	 * @param array $params
	 * @param ViewModel $model
	 * @throws Exception
	 * @return string
	 */
	public function executeGet(array $params, ViewModel $model) {
		$subscription = SubscriptionsService::instance ()->getUserActiveSubscription ( Session::getCredentials ()->getUserId () );
		if (empty ( $subscription )) {
			throw new Exception ( 'Must have an active subscription' );
		}
		if (! empty ( $subscription ['paymentProfileId'] )) {
			$paymentProfile = OrdersService::instance ()->getPaymentProfileById ( $subscription ['paymentProfileId'] );
			if (strcasecmp ( $paymentProfile ['state'], PaymentProfileStatus::ACTIVEPROFILE ) === 0) {
				throw new Exception ( 'Please first cancel the attached payment profile.' );
			}
		}
		$model->subscription = $subscription;
		return 'profile/cancelsubscription';
	}

}