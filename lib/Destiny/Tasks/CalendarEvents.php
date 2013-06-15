<?php

namespace Destiny\Tasks;

use Destiny\Cache\File;
use Destiny\Application;
use Destiny\Config;
use Psr\Log\LoggerInterface;
use Destiny\Service\Google\CalendarService;

class CalendarEvents {

	public function execute(LoggerInterface $log) {
		$start = new \DateTime ();
		$start->setTime ( date ( 'H' ), 0, 0 );
		$end = new \DateTime ();
		$end->setDate ( date ( 'Y', strtotime ( '+1 year' ) ), 1, 1 );
		$end->setTime ( date ( 'H' ), 0, 0 );
		$response = CalendarService::instance ()->getEventsInRange ( array (
				'start' => $start->format ( DATE_RFC3339 ),
				'end' => $end->format ( DATE_RFC3339 ),
				'limit' => 3,
				'tz' => 'UTC' 
		) )->getResponse ();
		$app = Application::instance ();
		$cache = $app->getMemoryCache ( 'calendarevents' );
		$cache->write ( $response );
	}

}