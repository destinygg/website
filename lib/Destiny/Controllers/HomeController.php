<?php
namespace Destiny\Controllers;

use Destiny\Common\Annotation\ResponseBody;
use Destiny\Common\ViewModel;
use Destiny\Common\Application;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Response;
use Destiny\Common\Utils\Http;
use Destiny\Common\Config;
use Destiny\Twitch\TwitchApiService;

/**
 * @Controller
 */
class HomeController {

    /**
     * @Route ("/")
     * @Route ("/home")
     *
     * @param ViewModel $model
     * @return string
     */
    public function home(ViewModel $model) {
        $cache = Application::instance ()->getCacheDriver ();
        $model->posts = $cache->fetch ( 'recentposts' );
        $model->articles = $cache->fetch ( 'recentblog' );
        $model->tweets = $cache->fetch ( 'twitter' );
        $model->recenttracks = $cache->fetch ( 'recenttracks' );
        $model->toptracks = $cache->fetch ( 'toptracks' );
        $model->playlist = $cache->fetch ( 'youtubeplaylist' );
        $model->broadcasts = $cache->fetch ( 'pastbroadcasts' );
        return 'home';
    }

    /**
     * @Route ("/api/info/stream")
     * @param Response $response
     * @ResponseBody
     * @return array|false|mixed
     */
    public function stream(Response $response) {
        $cache = Application::instance()->getCacheDriver();
        $streaminfo = $cache->contains('streamstatus') ? $cache->fetch('streamstatus') : TwitchApiService::$STREAM_INFO;
        $response->addHeader(Http::HEADER_CACHE_CONTROL, 'private');
        $response->addHeader(Http::HEADER_PRAGMA, 'public');
        $response->addHeader(Http::HEADER_ETAG, md5(var_export($streaminfo, true)));
        return $streaminfo;
    }

    /**
     * @Route ("/agreement")
     *
     * @param ViewModel $model
     * @return string
     */
    public function helpAgreement(ViewModel $model) {
        $model->title = 'User agreement';
        return 'agreement';
    }

    /**
     * @Route ("/ping")
     *
     * @param Response $response
     */
    public function ping(Response $response) {
        $response->addHeader ( 'X-Pong', Config::$a['meta']['shortName'] );
    }

    /**
     * @Route ("/bigscreen")
     *
     * @param ViewModel $model
     * @return string
     */
    public function bigscreen(ViewModel $model) {
        $model->title = 'Bigscreen';
        return 'bigscreen';
    }

    /**
     * @Route ("/amazon")
     *
     * @param ViewModel $model
     * @return string
     */
    public function amazon(ViewModel $model) {
        $model->title = 'Amazon';
        return 'amazon';
    }


}
