<?php

namespace Destiny\Action;

use Destiny\ViewModel;
use Destiny\Service\Fantasy\GameService;

class Admin {

	public function execute(array $params, ViewModel $model) {
		$model->title = 'Administration';
		$gameService = GameService::getInstance ();
		$model->games = $gameService->getGames ( 10, 0 );
		$model->tracks = $gameService->getTrackedProgress ( 10, 0 );
		return 'admin';
	}

}
