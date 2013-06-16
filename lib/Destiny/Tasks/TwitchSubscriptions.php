<?php

namespace Destiny\Tasks;

use Destiny\Service\UsersService;
use Destiny\Application;
use Destiny\Config;
use Destiny\Service\TwitchApiService;
use Destiny\Service\SubscriptionsService;
use Destiny\Utils\Date;
use Psr\Log\LoggerInterface;
use Destiny\AppException;

class TwitchSubscriptions {

	public function execute(LoggerInterface $log) {
		set_time_limit ( 480 );
		$i = 0;
		$total = 1;
		$increments = 50;
		$conn = Application::instance ()->getConnection ();
		$subService = SubscriptionsService::instance ();
		$userService = UsersService::instance ();
		while ( $i < $total ) {
			set_time_limit ( 20 );
			$subscriptions = TwitchApiService::instance ()->getChannelSubscriptions ( Config::$a ['twitch'] ['broadcaster'] ['user'], $increments, $i );
			if (empty ( $subscriptions )) {
				throw new AppException ( 'Error requesting subscriptions' );
				break;
			}
			if (! isset ( $subscriptions ['_total'] ) || ! is_numeric ( $subscriptions ['_total'] )) {
				throw new AppException ( 'Error requesting subscriptions. Total: 0' );
			}
			$total = intval ( $subscriptions ['_total'] );
			if ($total == 0) {
				throw new AppException ( 'Error requesting subscriptions. Total: 0' );
				break;
			}
			$log->info ( 'Checking subscriptions [' . $i . ' out of ' . $total . ']' );
			foreach ( $subscriptions ['subscriptions'] as $sub ) {
				
				// check if this a user
				$user = $userService->getUserByExternalId ( $sub ['user'] ['_id'] );
				if (empty ( $user )) {
					$i ++;
					continue;
				}
				// check if this user has a subscription
				$subscription = $subService->getUserActiveSubscription ( $user ['userId'] );
				if (empty ( $subscription )) {
					$start = Date::getDateTime ( $sub ['created_at'], 'Y-m-d H:i:s' );
					$end = strtotime ( 'next month', mktime ( 0, 0, 0, date ( 'm' ), 1, date ( 'y' ) ) );
					SubscriptionsService::instance ()->addSubscription ( $user ['userId'], Date::getDateTime ( $start, 'Y-m-d H:i:s' ), Date::getDateTime ( $end, 'Y-m-d H:i:s' ), 'Active', true, 'twitch.tv' );
					$i ++;
					continue;
				}
				$i ++;
			}
			sleep ( 1 );
			continue;
		}
		$log->info ( 'Subscription check complete' );
	}

}