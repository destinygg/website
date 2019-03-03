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

/**
 * @Controller
 */
class HomeController {

    private $merchandise = [
        [
            'title' => 'DuckerZ Shirt',
            'image' => 'dbh-duckerz',
            'url' => 'https://www.designbyhumans.com/shop/t-shirt/men/duckerz-shirt/1101862/',
        ],
        [
            'title' => 'Hot Cocoa Shirt!',
            'image' => 'dbh-cocoa',
            'url' => 'https://www.designbyhumans.com/shop/t-shirt/men/hot-cocoa-shirt/1081128/',
        ],
        [
            'title' => 'The CinnaBonnelli Hoodie',
            'image' => 'dbh-hood1',
            'url' => 'https://www.designbyhumans.com/shop/pullover-hoodie/the-cinnabonnelli-hoodie/1052252/',
        ],
        [
            'title' => 'The Original Memer Shirt',
            'image' => 'dbh-shirt2',
            'url' => 'https://www.designbyhumans.com/shop/t-shirt/men/the-original-memer-shirt/1046704/',
        ],
        [
            'title' => 'CinnaBonnelli Shirt!',
            'image' => 'dbh-shirt1',
            'url' => 'https://www.designbyhumans.com/shop/t-shirt/men/cinnabonnelli-shirt/1051248/',
        ],
        [
            'title' => 'The Leruse',
            'image' => 'dbh-shirt3',
            'url' => 'https://www.designbyhumans.com/shop/t-shirt/men/the-leruse/1046720/',
        ],
    ];

    /**
     * @Route ("/")
     * @Route ("/home")
     *
     * @param ViewModel $model
     * @return string
     */
    public function home(ViewModel $model) {
        $cache = Application::getNsCache();
        $model->posts = $cache->fetch ( 'recentposts' );
        $model->articles = $cache->fetch ( 'recentblog' );
        $model->tweets = $cache->fetch ( 'twitter' );
        $model->recenttracks = $cache->fetch ( 'recenttracks' );
        $model->toptracks = $cache->fetch ( 'toptracks' );
        $model->playlist = $cache->fetch ( 'youtubeplaylist' );
        $model->broadcasts = $cache->fetch ( 'pastbroadcasts' );
        $model->libsynfeed = $cache->fetch ( 'libsynfeed' );
        $model->merchandise = $this->merchandise;
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
        $cache = Application::getNsCache();
        $streaminfo = $cache->fetch('streamstatus');
        $response->addHeader(Http::HEADER_CACHE_CONTROL, 'private');
        $response->addHeader(Http::HEADER_PRAGMA, 'public');
        $response->addHeader(Http::HEADER_ETAG, md5(var_export($streaminfo, true)));
        return $streaminfo;
    }

    /**
     * @Route ("/embed/chat")
     *
     * @param ViewModel $model
     * @return string
     */
    public function embedChat(ViewModel $model) {
        $cache = Application::getNsCache();
        $model->title = 'Chat';
        $model->cacheKey = $cache->fetch('chatCacheKey');
        return 'chat';
    }

    /**
     * @Route ("/embed/onstreamchat")
     *
     * @param ViewModel $model
     * @return string
     */
    public function streamChat(ViewModel $model) {
        $cache = Application::getNsCache();
        $model->title = 'Chat';
        $model->cacheKey = $cache->fetch('chatCacheKey');
        return 'streamchat';
    }

    /**
     * @Route ("/embed/votechat")
     *
     * @param ViewModel $model
     * @return string
     */
    public function embedVote(ViewModel $model) {
        $cache = Application::getNsCache();
        $model->title = 'Vote';
        $model->cacheKey = $cache->fetch('chatCacheKey');
        return 'votechat';
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
