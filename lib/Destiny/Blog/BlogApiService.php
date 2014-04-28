<?php
namespace Destiny\Blog;

use Destiny\Common\Service;
use Destiny\Common\CurlBrowser;
use Destiny\Common\Utils\String;
use Destiny\Common\MimeType;
use Destiny\Common\Exception;

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
                'limit' => 6 
            ) ),
            'contentType' => MimeType::JSON,
            'onfetch' => function ($json) {
                if (empty($json) || !is_array($json)) {
                    throw new Exception('Invalid blog API response');
                }
                return array_slice ( $json, 0, 6 );
            } 
        ), $options ) );
    }

}