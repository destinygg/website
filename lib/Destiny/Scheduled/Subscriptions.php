<?php

namespace Destiny\Scheduled;

use Destiny\Application;
use Destiny\Config;
use Destiny\Service\TwitchApiService;
use Destiny\Utils\Date;
use Psr\Log\LoggerInterface;
use Destiny\AppException;

class Subscriptions {

	public function execute(LoggerInterface $log) {
		set_time_limit ( 480 );
		$i = 0;
		$total = 1;
		$increments = 50;
		$conn = Application::instance ()->getConnection ();
		$isSubsCleared = false;
		while ( $i < $total ) {
			set_time_limit ( 20 );
			$subscriptions = TwitchApiService::instance ()->getChannelSubscriptions ( Config::$a ['twitch'] ['broadcaster'] ['user'], $increments, $i );
			if ($subscriptions == null) {
				throw new AppException ( 'Error requesting subscriptions' );
				break;
			}
			if ($isSubsCleared == false) {
				$isSubsCleared = true;
				$conn->update ( 'dfl_users_twitch_subscribers', array (
						'validated' => false 
				) );
			}
			if (! isset ( $subscriptions ['_total'] ) || ! is_numeric ( $subscriptions ['_total'] )) {
				throw new AppException ( 'Error requesting subscriptions. Total: 0' );
			}
			$total = intval ( $subscriptions ['_total'] );
			if ($total == 0) {
				throw new AppException ( 'Error requesting subscriptions. Total: 0' );
				break;
			}
			$log->info ( 'Checked subscriptions [' . $i . ' out of ' . $total . ']' );
			foreach ( $subscriptions ['subscriptions'] as $sub ) {
				$stmt = $conn->prepare ( '
					INSERT INTO dfl_users_twitch_subscribers SET 
						externalId = :externalId,
						username = :username,
						displayName = :displayName,
						staff = :staff,
						subscribeDate = :subscribeDate,
						createdDate = :createdDate,
						validated = :validated
					ON DUPLICATE KEY UPDATE displayName=:displayName, validated = :validated
				' );
				$stmt->bindValue ( 'externalId', $sub->user->_id, \PDO::PARAM_STR );
				$stmt->bindValue ( 'username', $sub->user->name, \PDO::PARAM_STR );
				$stmt->bindValue ( 'displayName', $sub->user->display_name, \PDO::PARAM_STR );
				$stmt->bindValue ( 'staff', (! empty ( $sub->user->staff ) && $sub->user->staff == 1), \PDO::PARAM_BOOL );
				$stmt->bindValue ( 'subscribeDate', Date::getDateTime ( $sub->created_at, 'Y-m-d H:i:s' ), \PDO::PARAM_STR );
				$stmt->bindValue ( 'createdDate', Date::getDateTime ( time (), 'Y-m-d H:i:s' ), \PDO::PARAM_STR );
				$stmt->bindValue ( 'validated', true, \PDO::PARAM_BOOL );
				$stmt->execute ();
				$i ++;
			}
			sleep ( 3 );
			continue;
		}
		$log->info ( 'Subscription check complete' );
	}

}