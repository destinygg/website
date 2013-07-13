<?php
namespace Destiny\Action;

use Destiny\Application;
use Destiny\ViewModel;
use Destiny\Annotation\Action;
use Destiny\Annotation\Route;
use Destiny\Annotation\HttpMethod;
use Destiny\Annotation\Secure;

/**
 * @Action
 */
class Bigscreen {

	/**
	 * @Route ("/bigscreen")
	 *
	 * @param array $params
	 * @param ViewModel $model
	 * @return string
	 */
	public function execute(array $params, ViewModel $model) {
		$model->streamInfo = Application::instance ()->getCacheDriver ()->fetch ( 'streaminfo' );
		return 'bigscreen';
	}

}