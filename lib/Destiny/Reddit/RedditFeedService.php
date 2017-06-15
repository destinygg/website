<?php
namespace Destiny\Reddit;

use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\Log;
use Destiny\Common\Service;
use Destiny\Common\Utils\Http;
use GuzzleHttp;

/**
 * @method static RedditFeedService instance()
 *
 * t1_ Comment
 * t2_ Account
 * t3_ Link
 * t4_ Message
 * t5_ Subreddit
 * t6_ Award
 * t8_ PromoCampaign
 */
class RedditFeedService extends Service {

    /**
     * @return null|array
     * @throws Exception
     */
    public function getHotThreads() {
        $client = new GuzzleHttp\Client(['timeout' => 10, 'connect_timeout' => 5, 'http_errors' => false]);
        $response = $client->get(Config::$a['reddit']['threads'], [
            'headers' => ['User-Agent' => Config::userAgent()],
            'query' => ['limit' => 6, 'sort' => 'new']
        ]);
        try {
            if ($response->getStatusCode() == Http::STATUS_OK) {
                $json = GuzzleHttp\json_decode($response->getBody(), true);
                if (isset($json['data']) && !empty($json['data']) && isset($json['data']['children']) && !empty($json['data']['children'])) {
                    $data = [];
                    foreach ($json['data']['children'] as $child) {
                        if (isset($child['data'])) {
                            $c = $child['data'];
                            array_push($data, [
                                'id' => $c['id'],
                                'title' => $c['title'],
                                'created' => $c['created_utc'],
                                'score' => $c['score'],
                                'stickied' => $c['stickied'],
                                'locked' => $c['locked'],
                                'spoiler' => $c['spoiler'],
                                'archived' => $c['archived'],
                                'permalink' => 'https://www.reddit.com' . $c['permalink'],
                                'thumbnail' => $c['thumbnail'],
                                'num_comments' => $c['num_comments'],
                                'author' => $c['author'],
                                'downs' => $c['downs'],
                                'ups' => $c['ups']
                            ]);
                        }
                    }
                    return $data;
                }
            }
        } catch (\InvalidArgumentException $e) {
            $n = new Exception("Failed to parse reddit threads", $e);
            Log::error($n);
        }
        return null;
    }

}