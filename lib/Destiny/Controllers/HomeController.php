<?php
namespace Destiny\Controllers;

use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\ResponseBody;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\Response;
use Destiny\Common\Utils\Http;
use Destiny\Common\ViewModel;
use Destiny\Tasks\YouTubeTasks;
use Destiny\Twitch\TwitchWebHookService;

/**
 * @Controller
 */
class HomeController {

    /**
     * @Route ("/")
     * @Route ("/home")
     */
    public function home(ViewModel $model): string {
        $cache = Application::getNsCache();
        $model->posts = $cache->fetch ( 'recentposts' );
        $model->recenttracks = $cache->fetch ( 'recenttracks' );
        $model->toptracks = $cache->fetch ( 'toptracks' );
        $model->playlist = $cache->fetch(YouTubeTasks::RECENT_YOUTUBE_UPLOADS_CACHE_KEY);
        $model->broadcasts = $cache->fetch(YouTubeTasks::RECENT_YOUTUBE_LIVESTREAM_VODS_CACHE_KEY);
        $model->libsynfeed = $cache->fetch ( 'libsynfeed' );
        $model->merchandise = Config::$a['merch'];
        return 'home';
    }

    /**
     * @Route ("/ping")
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
    public function streamInfo(Response $response) {
        $cache = Application::getNsCache();
        $liveStatus = $cache->fetch(TwitchWebHookService::CACHE_KEY_PREFIX . Config::$a['twitch']['id']);
        $streaminfo = $cache->fetch(TwitchWebHookService::CACHE_KEY_STREAM_STATUS);
        // We try use the response from the webhook as a live indicator, otherwise fall back to the stream info from the http api
        $streaminfo['live'] = ($liveStatus === false) ? $streaminfo['live'] : $liveStatus['live'];
        $response->addHeader(Http::HEADER_CACHE_CONTROL, 'private');
        $response->addHeader(Http::HEADER_PRAGMA, 'public');
        $response->addHeader(Http::HEADER_ETAG, md5(var_export($streaminfo, true)));
        return $streaminfo;
    }

    /**
     * @Route ("/embed/chat")
     */
    public function embedChat(ViewModel $model): string {
        $cache = Application::getNsCache();
        $model->title = 'Chat';
        $model->cacheKey = $cache->fetch('chatCacheKey');
        return 'chat';
    }

    /**
     * @Route ("/embed/onstreamchat")
     */
    public function streamChat(ViewModel $model): string {
        $cache = Application::getNsCache();
        $model->title = 'Chat';
        $model->cacheKey = $cache->fetch('chatCacheKey');
        return 'streamchat';
    }

    /**
     * @Route ("/embed/votechat")
     */
    public function embedVote(ViewModel $model): string {
        $cache = Application::getNsCache();
        $model->title = 'Vote';
        $model->cacheKey = $cache->fetch('chatCacheKey');
        return 'votechat';
    }

    /**
     * @Route ("/agreement")
     */
    public function agreement(ViewModel $model): string {
        $model->title = 'User agreement';
        return 'agreement';
    }

    /**
     * @Route ("/bigscreen")
     */
    public function bigscreen(ViewModel $model): string {
        $model->title = 'Bigscreen';
        return 'bigscreen';
    }

}
