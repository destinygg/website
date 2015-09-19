<?php
namespace Destiny\Blog;

use Destiny\Common\Service;
use Destiny\Common\CurlBrowser;
use Destiny\Common\MimeType;
use Destiny\Common\Exception;

/**
 * @method static BlogApiService instance()
 */
class BlogApiService extends Service {
    
    /**
     * @param array $options
     * @return CurlBrowser
     */
    public function getBlogPosts(array $options = array()) {
        return new CurlBrowser ( array_merge ( array (
            'timeout' => 25,
            'url' => 'http://blog.destiny.gg/feed/json',
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