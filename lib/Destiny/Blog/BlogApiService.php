<?php
namespace Destiny\Blog;

use Destiny\Common\Config;
use Destiny\Common\Log;
use Destiny\Common\Service;
use Destiny\Common\Utils\Http;
use GuzzleHttp\Client;
use function GuzzleHttp\json_decode;
use InvalidArgumentException;

/**
 * @method static BlogApiService instance()
 */
class BlogApiService extends Service {

    /**
     * @return array|null
     */
    public function getBlogPosts() {
        $client = new Client(['timeout' => 10, 'connect_timeout' => 5, 'http_errors' => false]);
        $response = $client->get(Config::$a['blog']['feed'], [
            'headers' => ['User-Agent' => Config::userAgent()]
        ]);
        if ($response->getStatusCode() == Http::STATUS_OK) {
            try {
                $json = json_decode($response->getBody(), true);
                if (empty($json) || !is_array($json)) {
                    throw new InvalidArgumentException('Invalid blog API response');
                }
                return array_slice($json, 0, 6);
            } catch (InvalidArgumentException $e) {
                Log::error("Invalid endpoint configuration");
            }
        }
        return null;
    }

}