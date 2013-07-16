<?php
namespace Destiny\Action\Cron;

use Destiny\Common\Service\UserService;
use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\Service\TwitchApiService;
use Destiny\Common\Service\SubscriptionsService;
use Destiny\Common\Utils\Date;
use Destiny\Common\AppException;
use Psr\Log\LoggerInterface;

class TwitchSubscriptions {

	public function execute(LoggerInterface $log) {
		set_time_limit ( 480 );
		$i = 0;
		$total = 1;
		$increments = 50;
		$conn = Application::instance ()->getConnection ();
		$subService = SubscriptionsService::instance ();
		$userService = UserService::instance ();
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
			$log->debug ( 'Checking subscriptions [' . $i . ' out of ' . $total . ']' );
			foreach ( $subscriptions ['subscriptions'] as $sub ) {
				
				// check if this a user
				$user = $userService->getUserByAuthId ( $sub ['user'] ['_id'], 'twitch' );
				if (empty ( $user )) {
					$i ++;
					continue;
				}
				// check if this user has a subscription
				$subscription = $subService->getUserActiveSubscription ( $user ['userId'] );
				if (empty ( $subscription )) {
					$start = Date::getDateTime ( $sub ['created_at'] );
					$end = Date::getDateTime ();
					$end->modify ( 'first day of next month' );
					SubscriptionsService::instance ()->addSubscription ( $user ['userId'], $start->format ( 'Y-m-d H:i:s' ), $end->format ( 'Y-m-d H:i:s' ), 'Active', true, 'twitch.tv' );
					$i ++;
					continue;
				}
				$i ++;
			}
			sleep ( 1 );
			continue;
		}
		$log->debug ( 'Subscription check complete' );
	}

}