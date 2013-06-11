<?php

namespace Destiny\Action;

use Destiny\ViewModel;
use Destiny\Service\Fantasy\Db\Game;

class Admin {

	public function execute(array $params, ViewModel $model) {
		$model->title = 'Administration';
		$gameService = Game::getInstance ();
		$model->games = $gameService->getGames ( 10, 0 );
		$model->tracks = $gameService->getTrackedProgress ( 10, 0 );
		return 'admin';
	}

}
