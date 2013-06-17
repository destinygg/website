<?php

namespace Destiny\Action;

use Destiny\Application;
use Destiny\ViewModel;

class Bigscreen {

	public function execute(array $params, ViewModel $model) {
		$model->title = 'Theater';
		$model->streamInfo = Application::instance ()->getCacheDriver ()->fetch ( 'streaminfo' );
		return 'bigscreen';
	}

}