<?php

namespace Destiny\Scheduled;

use Destiny\Application;
use Destiny\Config;
use Destiny\Utils\Date;
use Psr\Log\LoggerInterface;
use Destiny\Service\Subscriptions;

class SubscriptionExpire {

	public function execute(LoggerInterface $log) {
		$expiredSubscriptionCount = Subscriptions::getInstance ()->expiredSubscriptions ();
		$log->info ( sprintf ( 'Expired (%s)', $expiredSubscriptionCount ) );
	}

}