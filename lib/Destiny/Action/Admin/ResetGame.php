<?php

namespace Destiny\Action\Admin;

use Destiny\Application;
use Destiny\Service\Fantasy\Db\Aggregate;

class ResetGame {

	public function execute(array $params) {
		if (! isset ( $params ['gameId'] ) || empty ( $params ['gameId'] )) {
			throw new \Exception ( 'gameId required.' );
		}
		$log = Application::getInstance ()->getLogger ();
		$log->notice ( sprintf ( 'Game %s reset', $params ['gameId'] ) );
		Aggregate::getInstance ()->resetGame ( $params ['gameId'] );
	}

}