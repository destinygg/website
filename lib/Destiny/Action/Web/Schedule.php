<?php
namespace Destiny\Action\Web;

use Destiny\Common\ViewModel;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;

/**
 * @Action
 */
class Schedule {

	/**
	 * @Route ("/schedule")
	 *
	 * @param array $params
	 * @param ViewModel $model
	 * @return string
	 */
	public function execute(array $params, ViewModel $model) {
		$model->title = 'Schedule';
		return 'schedule';
	}

}