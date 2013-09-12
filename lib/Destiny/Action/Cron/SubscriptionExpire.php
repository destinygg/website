<?php
namespace Destiny\Action\Cron;

use Destiny\Common\Service\RememberMeService;
use Destiny\Common\Service\SubscriptionsService;
use Psr\Log\LoggerInterface;

class SubscriptionExpire {

	public function execute(LoggerInterface $log) {
		RememberMeService::instance ()->clearExpiredRememberMe ();
		$expiredSubscriptionCount = SubscriptionsService::instance ()->expiredSubscriptions ();
		$log->debug ( sprintf ( 'Expired (%s)', $expiredSubscriptionCount ) );
	}

}