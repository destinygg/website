<?php

namespace Destiny\Service\Google;

use Destiny\Service;
use Destiny\Config;
use Destiny\HttpApiConsumer;
use Destiny\Utils\String;
use Destiny\MimeType;

class CalendarService extends Service {
	
	protected static $instance = null;

	/**
	 * @return Service\CalendarServicer
	 */
	public static function instance() {
		return parent::instance ();
	}

	/**
	 * Return calendar events
	 *
	 * @param array $options
	 */
	public function getEventsInRange(array $options = array()) {
		return new HttpApiConsumer ( array_merge ( array (
				'url' => new String ( 'http://www.google.com/calendar/feeds/{calendar.id}/public/full?alt=jsonc&max-results={limit}&singleevents=true&orderby=starttime&sortorder=ascending&start-min={start}&start-max={end}&ctz={tz}', array (
						'calendar.id' => Config::$a ['google'] ['calendar'] ['id'],
						'limit' => $options ['limit'],
						'start' => urlencode ( $options ['start'] ),
						'end' => urlencode ( $options ['end'] ),
						'tz' => urlencode ( $options ['tz'] ) 
				) ),
				'contentType' => MimeType::JSON 
		), $options ) );
	}

}