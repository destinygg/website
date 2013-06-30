<?php
namespace Destiny\Action\Embed;

use Destiny\AppException;
use Destiny\Utils\Http;
use Destiny\ViewModel;
use Destiny\Application;
use Destiny\Session;
use Destiny\Service\Fantasy\TeamService;
use Destiny\Config;

class Chat {

	public function execute(array $params, ViewModel $model) {
		$app = Application::instance ();
		$model->chatOptions = $this->getChatOptionParams ( $params );
		$cache = $app->getRedis();
		
		$chatlog = $cache->lrange('chatlog', 0, Config::$a['chatbacklog']);
		// TODO parse the data, transform userids to nicks, cache it
		// possibly not rely on the chat backend to generate all this shit
		// but pull it out of the database events, would be more efficient
		// only question is how to notice if it changes? have a background
		// php job that listens on a redis pub/sub channel and whenever
		// it gets a signal, it regenerates the cache/invalidates varnish cache
		// long-term, this should simply be done by the chat backend, but it cannot
		// speak php serialized data yet
		//$model->chatlog = $chatlog
		
		$user = Session::getCredentials()->getData();
		if ( $user['userId'] )
			$model->user = array(
				'nick' => $user['username'],
				'features' => $user['features']
			);
		
		return 'embed/chat';
	}

	/**
	 * Get the chat params from the get request
	 * Make sure they are all valid
	 *
	 * @param array $params
	 */
	private function getChatOptionParams(array $params) {
		$options = array ();
		if (! isset ( $params ['theme'] ) || empty ( $params ['theme'] ) || $params ['theme'] != 'light' && $params ['theme'] != 'dark') {
			$params ['theme'] = 'dark';
		}
		$options ['theme'] = $params ['theme'];
		return $options;
	}

}
