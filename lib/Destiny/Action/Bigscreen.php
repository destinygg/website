<?php

namespace Destiny\Action;

use Destiny\Application;
use Destiny\ViewModel;

class Bigscreen {

	public function execute(array $params, ViewModel $model) {
		$model->title = 'Theater';
		$cache = Application::instance()->getMemoryCache ( 'streaminfo' );
		$model->streamInfo = $cache->read ();
		return 'bigscreen';
	}

}