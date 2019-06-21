<?php
namespace Destiny\LibSyn;

use Destiny\Common\Config;
use Destiny\Common\HttpClient;
use Destiny\Common\Service;
use Destiny\Common\Utils\Http;

/**
 * @method static LibSynFeedService instance()
 */
class LibSynFeedService extends Service {

    /**
     * @return array|null
     */
    function getFeed(string $name) {
        $client = HttpClient::instance();
        $response = $client->get("http://$name.libsyn.com/render-type/json", [
            'headers' => ['User-Agent' => Config::userAgent()]
        ]);
        if ($response->getStatusCode() == Http::STATUS_OK) {
            return \GuzzleHttp\json_decode($response->getBody(), true);
        }
        return null;
    }

}