<?php

namespace Destiny\Tasks;

use Destiny\Service\RememberMeService;
use Destiny\Application;
use Destiny\Config;
use Destiny\Utils\Date;
use Psr\Log\LoggerInterface;
use Destiny\Service\SubscriptionsService;

class SubscriptionExpire {

	public function execute(LoggerInterface $log) {
		RememberMeService::instance ()->clearExpiredRememberMe ();
		$expiredSubscriptionCount = SubscriptionsService::instance ()->expiredSubscriptions ();
		$log->debug ( sprintf ( 'Expired (%s)', $expiredSubscriptionCount ) );
	}

}