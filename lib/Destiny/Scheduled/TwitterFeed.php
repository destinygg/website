<?php

namespace Destiny\Scheduled;

use Destiny\Config;
use Psr\Log\LoggerInterface;
use Destiny\Service\CommonApiService;
use Destiny\Application;
use Destiny\Utils\Tpl;

class TwitterFeed {

	public function execute(LoggerInterface $log) {
		$app = Application::instance ();
		$cache = $app->getMemoryCache ( 'twitter' );
		
		$curl = new \Buzz\Client\Curl ();
		$curl->setVerifyPeer ( false );
		$client = new \Buzz\Browser ( $curl );
		$consumer = new \Twitter\OAuth2\Consumer ( $client, Config::$a ['twitter'] ['consumer_key'], Config::$a ['twitter'] ['consumer_secret'] );
		$query = $consumer->prepare ( '/1.1/statuses/user_timeline.json', 'GET', array (
				'screen_name' => Config::$a ['twitter'] ['user'],
				'count' => 3,
				'trim_user' => true 
		) );
		$result = $consumer->execute ( $query );
		$tweets = array ();
		foreach ( $result as $tweet ) {
			$html = Tpl::out ( $tweet ['text'] );
			if (isset ( $tweet ['entities'] ['user_mentions'] )) {
				foreach ( $tweet ['entities'] ['user_mentions'] as $ment ) {
					$l = '<a href="http://twitter.com/' . $ment ['screen_name'] . '">' . $ment ['name'] . '</a>';
					$html = str_replace ( '@' . $ment ['screen_name'], $l, $html );
				}
			}
			if (isset ( $tweet ['entities'] ) && isset ( $tweet ['entities'] ['urls'] )) {
				foreach ( $tweet ['entities'] ['urls'] as $url ) {
					$l = '<a href="' . $url ['url'] . '" rev="' . $url ['expanded_url'] . '">' . $url ['display_url'] . '</a>';
					$html = str_replace ( $url ['url'], $l, $html );
				}
			}
			$tweet ['user']['screen_name'] = Config::$a ['twitter'] ['user'];
			$tweet ['html'] = $html;
			$tweets [] = $tweet;
		}
		$cache->write ( $tweets );
	}

}