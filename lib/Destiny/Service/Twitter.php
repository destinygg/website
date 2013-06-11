<?php

namespace Destiny\Service;

use Destiny\Service;
use Destiny\Config;
use Destiny\Mimetype;
use Destiny\Api\Consumer;
use Destiny\Utils\String;
use Destiny\Utils\Date;

class Twitter extends Service {
	protected static $instance = null;

	/**
	 *
	 * @return Service\Twitter
	 */
	public static function getInstance() {
		return parent::getInstance ();
	}

	public function getTimeline(array $options = array()) {
		return new Consumer ( array_merge ( array (
				'url' => new String ( 'http://api.twitter.com/1/statuses/user_timeline.json?count={limit}&screen_name={user}', array (
						'user' => Config::$a ['twitter'] ['user'],
						'limit' => 3 
				) ),
				'contentType' => Mimetype::JSON,
				'onfetch' => function ($json) {
					if (is_array ( $json ) && count ( $json ) > 0) {
						foreach ( $json as $tweetIndex => $tweet ) {
							$json [$tweetIndex] ['created_at'] = Date::getDateTime ( $tweet ['created_at'], Date::FORMAT );
						}
					} else {
						throw new \Exception ( 'Twitter API down' );
					}
					return $json;
				} 
		), $options ) );
	}

	/**
	 * Adds links, users and hashtags
	 *
	 * @param string $str
	 * @return mixed
	 */
	public static function getTweetHtml($str) {
		$str = preg_replace ( '@(https?://([-\w\.]+)+(/([\w/_\.]*(\?\S+)?(#\S+)?)?)?)@', '<a href="$1">$1</a>', $str );
		$str = preg_replace ( '/@(\w+)/', '<a href="http://twitter.com/$1">@$1</a>', $str );
		$str = preg_replace ( '/\s#(\w+)/', ' <a href="http://search.twitter.com/search?q=%23$1">#$1</a>', $str );
		return $str;
	}

}