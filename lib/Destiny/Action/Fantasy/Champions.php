<?php
namespace Destiny\Action\Fantasy;

use Destiny\Service\Fantasy\Cache;
use Destiny\Utils\Http;
use Destiny\Mimetype;
use Destiny\Session;
use Destiny\Config;

class Champions {

	public function execute(array $params) {
		$champions = Cache::getInstance()->getChampions ();
		Http::header ( Http::HEADER_CONTENTTYPE, Mimetype::JSON );
		Http::sendString ( json_encode ( $champions ) );
	}
}