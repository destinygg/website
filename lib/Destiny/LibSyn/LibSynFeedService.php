<?php
namespace Destiny\LibSyn;

use Destiny\Common\Config;
use Destiny\Common\Service;
use Destiny\Common\Utils\Http;
use GuzzleHttp\Client;

/**
 * @method static LibSynFeedService instance()
 */
class LibSynFeedService extends Service {

    /**
     * @param $name
     * @return array|null
     */
    function getFeed($name) {
        $client = new Client(['timeout' => 10, 'connect_timeout' => 5, 'http_errors' => false]);
        $response = $client->get("http://$name.libsyn.com/render-type/json", [
            'headers' => ['User-Agent' => Config::userAgent()]
        ]);
        if ($response->getStatusCode() == Http::STATUS_OK) {
            $json = \GuzzleHttp\json_decode($response->getBody(), true);
            return $json;
        }
        return null;
    }

}