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
        $cache = Application::instance ()->getCache ();
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
     * @Route ("/ping")
     *
     * @param Response $response
     */
    public function ping(Response $response) {
        $response->addHeader ( 'X-Pong', Config::$a['meta']['shortName'] );
    }

    /**
     * @Route ("/api/info/stream")
     * @param Response $response
     * @ResponseBody
     * @return array|false|mixed
     */
    public function stream(Response $response) {
        $cache = Application::instance()->getCache();
        $streaminfo = $cache->contains('streamstatus') ? $cache->fetch('streamstatus') : TwitchApiService::$STREAM_INFO;
        $response->addHeader(Http::HEADER_CACHE_CONTROL, 'private');
        $response->addHeader(Http::HEADER_PRAGMA, 'public');
        $response->addHeader(Http::HEADER_ETAG, md5(var_export($streaminfo, true)));
        return $streaminfo;
    }

    /**
     * @Route ("/embed/stream")
     *
     * @return string
     */
    public function embedStream(){
        return 'redirect: ' . Config::$a['embed']['stream'];
    }

    /**
     * @Route ("/embed/chat")
     *
     * @param ViewModel $model
     * @return string
     */
    public function embedChat(ViewModel $model) {
        $model->title = 'Chat';
        return 'chat';
    }

    /**
     * @Route ("/embed/onstreamchat")
     *
     * @param ViewModel $model
     * @return string
     */
    public function chatstreamed(ViewModel $model) {
        $model->title = 'Chat';
        return 'chatstreamed';
    }

    /**
     * @Route ("/agreement")
     *
     * @param ViewModel $model
     * @return string
     */
    public function agreement(ViewModel $model) {
        $model->title = 'User agreement';
        return 'agreement';
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


}
