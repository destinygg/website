<?php
namespace Destiny\Action\Cron;

use Psr\Log\LoggerInterface;
use Destiny\Authentication\Service\RememberMeService;
use Destiny\Commerce\Service\SubscriptionsService;

class SubscriptionExpire {

	public function execute(LoggerInterface $log) {
		RememberMeService::instance ()->clearExpiredRememberMe ();
		$expiredSubscriptionCount = SubscriptionsService::instance ()->expiredSubscriptions ();
		$log->debug ( sprintf ( 'Expired (%s)', $expiredSubscriptionCount ) );
	}

}