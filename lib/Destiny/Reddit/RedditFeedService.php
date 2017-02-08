<?php
namespace Destiny\Reddit;

use Destiny\Common\CurlBrowser;
use Destiny\Common\Exception;
use Destiny\Common\MimeType;
use Destiny\Common\Service;

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
     * @throws Exception
     * @return CurlBrowser
     */
    public function getHotThreads() {
        $url = 'https://www.reddit.com/r/destiny/hot.json?sort=new&limit=6';
        return new CurlBrowser ([
            'url' => $url,
            'contentType' => MimeType::JSON,
            'onfetch' => function ($json) {
                $data = [];
                if (isset($json['data']) && !empty($json['data']) && isset($json['data']['children']) && !empty($json['data']['children'])) {
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
                }
                return $data;
            }
        ]);
    }

}