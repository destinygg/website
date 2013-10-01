<?php
namespace Destiny\Tasks;

use Psr\Log\LoggerInterface;
use Destiny\Common\Authentication\RememberMeService;
use Destiny\Commerce\SubscriptionsService;

class SubscriptionExpire {

	public function execute(LoggerInterface $log) {
		RememberMeService::instance ()->clearExpiredRememberMe ();
		$expiredSubscriptionCount = SubscriptionsService::instance ()->expiredSubscriptions ();
		$log->debug ( sprintf ( 'Expired (%s)', $expiredSubscriptionCount ) );
	}

}