<?php
namespace Destiny\Action\Web;

use Destiny\Common\Application;
use Destiny\Common\ViewModel;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;

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