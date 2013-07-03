<?php
namespace Destiny\Action;

use Destiny\Utils\Http;

class Ping {

	public function execute(array $params) {
		Http::header ( 'X-Ping', 'Destiny' );
		Http::status ( Http::STATUS_OK );
		exit ();
	}

}