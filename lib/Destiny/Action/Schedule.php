<?php

namespace Destiny\Action;

use Destiny\ViewModel;

class Schedule {

	public function execute(array $params, ViewModel $model) {
		$model->title = 'Schedule';
		return 'schedule';
	}

}
