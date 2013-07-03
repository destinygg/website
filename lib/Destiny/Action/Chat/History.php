<?php
namespace Destiny\Action\Chat;

use Destiny\AppException;
use Destiny\Utils\Http;
use Destiny\ViewModel;
use Destiny\Application;
use Destiny\Session;
use Destiny\MimeType;
use Destiny\Config;
use Destiny\Service\ChatlogService;

class History {

	public function execute(array $params, ViewModel $model) {
		// TODO parse the data, transform userids to nicks, cache it
		// possibly not rely on the chat backend to generate all this shit
		// but pull it out of the database events, would be more efficient
		// only question is how to notice if it changes? have a background
		// php job that listens on a redis pub/sub channel and whenever
		// it gets a signal, it regenerates the cache/invalidates varnish cache
		// long-term, this should simply be done by the chat backend, but it cannot
		// speak php serialized data yet
		$chatLog = ChatlogService::instance ()->getChatLog ( Config::$a ['chat'] ['backlog'] );
		$log = array ();
		foreach ( $chatLog as $i => $line ) {
			$log [] = $line;
		}
		
		Http::header ( Http::HEADER_CONTENTTYPE, MimeType::JAVASCRIPT );
		Http::sendString ( 'var backlog = ' . json_encode ( $log ) );
		exit ();
	}

}
