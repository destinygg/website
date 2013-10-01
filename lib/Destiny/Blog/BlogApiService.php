<?php
namespace Destiny\Blog;

use Destiny\Common\Service;
use Destiny\Common\CurlBrowser;
use Destiny\Common\Utils\String;
use Destiny\Common\MimeType;

class BlogApiService extends Service {
	
	/**
	 * Singleton
	 *
	 * @return BlogApiService
	 */
	protected static $instance = null;

	/**
	 * Singleton
	 *
	 * @return BlogApiService
	 */
	public static function instance() {
		return parent::instance ();
	}

	/**
	 * Get the most recent blog posts
	 *
	 * @param array $options
	 * @return \Destiny\CurlBrowser
	 */
	public function getBlogPosts(array $options = array()) {
		return new CurlBrowser ( array_merge ( array (
			'timeout' => 25,
			'url' => new String ( 'http://blog.destiny.gg/?feed=json&limit={limit}', array (
				'limit' => 3 
			) ),
			'contentType' => MimeType::JSON,
			'onfetch' => function ($json) {
				if ($json != null) {
					$json = array_slice ( $json, 0, 3 );
				}
				return $json;
			} 
		), $options ) );
	}

}