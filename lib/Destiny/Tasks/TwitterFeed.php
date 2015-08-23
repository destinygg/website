<?php
namespace Destiny\Tasks;

use Destiny\Common\Config;
use Destiny\Common\Application;

class TwitterFeed implements TaskInterface {

    public function execute() {
        $app = Application::instance ();
        $cacheDriver = $app->getCacheDriver ();
        $twitterOAuthConf = Config::$a ['oauth'] ['providers'] ['twitter'];
        $tmhOAuth = new \tmhOAuth ( array (
            'consumer_key' => $twitterOAuthConf ['clientId'],
            'consumer_secret' => $twitterOAuthConf ['clientSecret'],
            'token' => $twitterOAuthConf ['token'],
            'secret' => $twitterOAuthConf ['secret'],
            'curl_connecttimeout' => Config::$a ['curl'] ['connecttimeout'],
            'curl_timeout' => Config::$a ['curl'] ['timeout'],
            'curl_ssl_verifypeer' => Config::$a ['curl'] ['verifypeer'] 
        ) );
        $code = $tmhOAuth->user_request ( array (
            'url' => $tmhOAuth->url ( '1.1/statuses/user_timeline.json' ),
            'params' => array (
                'screen_name' => Config::$a ['twitter'] ['user'],
                'count' => 3,
                'trim_user' => true 
            ) 
        ) );
        if ($code == 200) {
            $result = json_decode($tmhOAuth->response ['response'], true);
            $tweets = array();
            foreach ($result as $tweet) {
                $html = $tweet ['text'];
                if (isset ($tweet ['entities'] ['user_mentions'])) {
                    foreach ($tweet ['entities'] ['user_mentions'] as $ment) {
                        $l = '<a href="http://twitter.com/' . $ment ['screen_name'] . '">' . $ment ['name'] . '</a>';
                        $html = str_replace('@' . $ment ['screen_name'], $l, $html);
                    }
                }
                if (isset ($tweet ['entities']) && isset ($tweet ['entities'] ['urls'])) {
                    foreach ($tweet ['entities'] ['urls'] as $url) {
                        $l = '<a href="' . $url ['url'] . '" rev="' . $url ['expanded_url'] . '">' . $url ['display_url'] . '</a>';
                        $html = str_replace($url ['url'], $l, $html);
                    }
                }
                $tweet ['user'] ['screen_name'] = Config::$a ['twitter'] ['user'];
                $tweet ['html'] = $html;
                $tweets [] = $tweet;
            }
            $cacheDriver->save('twitter', $tweets);
        }
    }

}