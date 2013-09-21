<?php
namespace Destiny\Google\Service;

use Destiny\Common\Service;
use Destiny\Common\Config;
use Destiny\Common\CurlBrowser;
use Destiny\Common\Utils\String;
use Destiny\Common\MimeType;

class CalendarService extends Service {
	
	/**
	 * var CalendarService
	 */
	protected static $instance = null;

	/**
	 *
	 * @return CalendarService
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
		return new CurlBrowser ( array_merge ( array (
			'url' => new String ( 'http://www.google.com/calendar/feeds/{calendar.id}/public/full?alt=jsonc&max-results={limit}&singleevents=true&orderby=starttime&sortorder=ascending&start-min={start}&start-max={end}&ctz={tz}', array (
				'calendar.id' => Config::$a ['calendar'],
				'limit' => $options ['limit'],
				'start' => urlencode ( $options ['start'] ),
				'end' => urlencode ( $options ['end'] ),
				'tz' => urlencode ( $options ['tz'] ) 
			) ),
			'contentType' => MimeType::JSON 
		), $options ) );
	}

}