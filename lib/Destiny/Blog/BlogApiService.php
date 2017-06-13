<?php
namespace Destiny\Blog;

use Destiny\Common\Config;
use Destiny\Common\Log;
use Destiny\Common\Service;
use Destiny\Common\Exception;
use Destiny\Common\Utils\Http;
use GuzzleHttp;

/**
 * @method static BlogApiService instance()
 */
class BlogApiService extends Service {
    
    /**
     * @return null|array
     */
    public function getBlogPosts() {
        $client = new GuzzleHttp\Client(['timeout' => 10, 'connect_timeout' => 5, 'http_errors' => false]);
        $response = $client->get(Config::$a['blog']['feed'], [
            'headers' => ['User-Agent' => Config::userAgent()]
        ]);
        if ($response->getStatusCode() == Http::STATUS_OK) {
            try {
                $json = GuzzleHttp\json_decode($response->getBody(), true);
                if (empty($json) || !is_array($json)) {
                    throw new Exception('Invalid blog API response');
                }
                return array_slice($json, 0, 6);
            } catch (\Exception $e) {
                Log::error("Invalid blog response");
            }
        }
        return null;
    }

}