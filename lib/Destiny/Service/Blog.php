<?php

namespace Destiny\Service;

use Destiny\Service;
use Destiny\Mimetype;
use Destiny\Api\Consumer;
use Destiny\Utils\String;

class Blog extends Service {
	protected static $instance = null;

	/**
	 *
	 * @return Service\Blog
	 */
	public static function getInstance() {
		return parent::getInstance ();
	}

	public function getRecent(array $options = array()) {
		return new Consumer ( array_merge ( array (
				'timeout' => 25,
				'url' => new String ( 'http://www.destiny.gg/n/?feed=json&limit={limit}', array (
						'limit' => 3 
				) ),
				'contentType' => Mimetype::JSON,
				'onfetch' => function ($json) {
					if ($json != null) {
						$json = array_slice ( $json, 0, 3 );
					}
					return $json;
				} 
		), $options ) );
	}

}