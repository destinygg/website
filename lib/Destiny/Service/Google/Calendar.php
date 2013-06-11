<?php

namespace Destiny\Service\Google;

use Destiny\Service;
use Destiny\Config;
use Destiny\Api\Consumer;
use Destiny\Utils\String;
use Destiny\Mimetype;

class Calendar extends Service {
	
	/**
	 *
	 * @var Service
	 */
	protected static $instance = null;

	/**
	 *
	 * @return Service\Google\Calendar
	 */
	public static function getInstance() {
		return parent::getInstance ();
	}

	/**
	 * Return calendar events
	 *
	 * @param array $options
	 */
	public function getEventsInRange(array $options = array()) {
		return new Consumer ( array_merge ( array (
				'url' => new String ( 'http://www.google.com/calendar/feeds/{calendar.id}/public/full?alt=jsonc&max-results={limit}&singleevents=true&orderby=starttime&sortorder=ascending&start-min={start}&start-max={end}&ctz={tz}', array (
						'calendar.id' => Config::$a ['google'] ['calendar'] ['id'],
						'limit' => $options ['limit'],
						'start' => urlencode ( $options ['start'] ),
						'end' => urlencode ( $options ['end'] ),
						'tz' => urlencode ( $options ['tz'] ) 
				) ),
				'contentType' => Mimetype::JSON 
		), $options ) );
	}

}