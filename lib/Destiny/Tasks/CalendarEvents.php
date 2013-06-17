<?php

namespace Destiny\Tasks;

use Destiny\Application;
use Destiny\Utils\Date;
use Destiny\Config;
use Psr\Log\LoggerInterface;
use Destiny\Service\Google\CalendarService;

class CalendarEvents {

	public function execute(LoggerInterface $log) {
		$start = Date::getDateTime ();
		$start->setTime ( date ( 'H' ), 0, 0 );
		$end = Date::getDateTime ();
		$end->modify ( '+1 year' );
		$response = CalendarService::instance ()->getEventsInRange ( array (
				'start' => $start->format ( DATE_RFC3339 ),
				'end' => $end->format ( DATE_RFC3339 ),
				'limit' => 3,
				'tz' => 'UTC' 
		) )->getResponse ();
		$app = Application::instance ();
		$app->getCacheDriver ()->save ( 'calendarevents', $response );
	}

}