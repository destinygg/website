<?php
namespace Destiny\Controllers;

use Destiny\Common\ViewModel;
use Destiny\Common\Application;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Response;
use Destiny\Common\Utils\Http;
use Destiny\Common\Config;

/**
 * @Controller
 */
class HomeController {

    /**
     * @Route ("/")
     * @Route ("/i")
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     */
    public function tournament(array $params, ViewModel $model) {
        $model->title = 'Destiny I | Starcraft 2: Heart of the Swarm tournament';
        return 'tournament/home';
    }

    /**
     * @Route ("/ping")
     *
     * @param array $params
     */
    public function ping(array $params) {
        $response = new Response ( Http::STATUS_OK );
        $response->addHeader ( 'X-Pong', 'Destiny' );
        return $response;
    }
    
}
