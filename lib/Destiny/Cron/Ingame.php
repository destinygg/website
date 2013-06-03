<?php
namespace Destiny\Cron;

use Destiny\Config;
use Destiny\Service\Leagueapi;
use Destiny\Service\Fantasy\Db\Tracking;
use Destiny\Logger;

class Ingame {

	public function execute(Logger $log) {
		$log->log ( 'Tracking ingame progress' );
		$ftrackService = Tracking::getInstance ();
		$leagueApiService = Leagueapi::getInstance ();
		foreach ( Config::$a ['lol'] ['summoners'] as $rSummoner ) {
			if ($rSummoner ['track'] == false) {
				continue;
			}
			$log->log ( 'Summoner [' . $rSummoner ['name'] . ']: Checking...' );
			$ingame = $leagueApiService->getInGameProgress ( $rSummoner );
			if ($ingame != null && $ingame->success == true && $ingame->data != null) {
				$log->log ( 'Summoner [' . $rSummoner ['name'] . ']: Currently Ingame: ' . $ingame->data->gameId );
				$ftrackService->trackIngameProgress ( $rSummoner, $ingame->data );
			}
		}
		$log->log ( 'Complete' );
	}

}