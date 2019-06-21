<?php
namespace Destiny\Youtube;

use Destiny\Common\Config;
use Destiny\Common\HttpClient;
use Destiny\Common\Log;
use Destiny\Common\Service;
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\Http;
use InvalidArgumentException;

/**
 * @method static YoutubeApiService instance()
 */
class YoutubeApiService extends Service {

    /**
     * @return array|null
     */
    public function getYoutubePlaylist(array $params = []) {
        // Get the channel ID's from a specific person
        // GET https://www.googleapis.com/youtube/v3/channels?part=contentDetails&forUsername=Destiny&key={1}
        // GET https://www.googleapis.com/youtube/v3/search?order=date&part=snippet&channelId={0}&key={1}
        $params ['limit'] = (isset ($params ['limit'])) ? intval($params ['limit']) : 4;
        $client = HttpClient::instance();
        $response = $client->get('https://www.googleapis.com/youtube/v3/search', [
            'headers' => ['User-Agent' => Config::userAgent()],
            'query' => [
                'order' => 'date',
                'type' => 'video',
                'part' => 'snippet',
                'key' => Config::$a ['youtube'] ['apikey'],
                'channelId' => Config::$a ['youtube'] ['playlistId'],
                'maxResults' => intval($params ['limit']),
            ]
        ]);
        if ($response->getStatusCode() == Http::STATUS_OK) {
            try {
                $json = \GuzzleHttp\json_decode($response->getBody(), true);
                if (is_array($json ['items'])) {
                    foreach ($json ['items'] as $i => $item) {
                        $item ['snippet'] ['publishedAt'] = Date::getDateTime($item ['snippet'] ['publishedAt']);
                    }
                    return $json;
                }
            } catch (InvalidArgumentException $e) {
                Log::error("Failed to parse youtube playlist. " . $e->getMessage());
            }
        }
        return null;
    }

}