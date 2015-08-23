<?php 
namespace Destiny\Controllers;

use Destiny\Common\Application;
use Destiny\Common\Response;
use Destiny\Common\Utils\Http;
use Destiny\Common\MimeType;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;

/**
 * @Controller
 */
class ApiController {

    /**
     * @Route ("/youtube.json")
     */
    public function youtube() {
        $app = Application::instance ();
        $playlist = $app->getCacheDriver ()->fetch ( 'youtubeplaylist' );
        $response = new Response ( Http::STATUS_OK, json_encode ( $playlist ) );
        $response->addHeader ( Http::HEADER_CACHE_CONTROL, 'private' );
        $response->addHeader ( Http::HEADER_PRAGMA, 'public' );
        $response->addHeader ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
        return $response;
    }

    /**
     * @Route ("/twitter.json")
     */
    public function twitter() {
        $app = Application::instance ();
        $tweets = $app->getCacheDriver ()->fetch ( 'twitter' );
        $response = new Response ( Http::STATUS_OK, json_encode ( $tweets ) );
        $response->addHeader ( Http::HEADER_CACHE_CONTROL, 'private' );
        $response->addHeader ( Http::HEADER_PRAGMA, 'public' );
        $response->addHeader ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
        return $response;
    }

    /**
     * @Route ("/stream.json")
     */
    public function stream() {
        $app = Application::instance ();
        $info = $app->getCacheDriver ()->fetch ( 'streaminfo' );
        $response = new Response ( Http::STATUS_OK, json_encode ( $info ) );
        $response->addHeader ( Http::HEADER_CACHE_CONTROL, 'private' );
        $response->addHeader ( Http::HEADER_PRAGMA, 'public' );
        $response->addHeader ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
        return $response;
    }

    /**
     * @Route ("/lastfm.json")
     */
    public function lastfm() {
        $app = Application::instance ();
        $tracks = $app->getCacheDriver ()->fetch ( 'recenttracks' );
        $response = new Response ( Http::STATUS_OK, json_encode ( $tracks ) );
        $response->addHeader ( Http::HEADER_CACHE_CONTROL, 'private' );
        $response->addHeader ( Http::HEADER_PRAGMA, 'public' );
        $response->addHeader ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
        return $response;
    }

    /**
     * @Route ("/broadcasts.json")
     */
    public function broadcasts() {
        $app = Application::instance ();
        $broadcasts = $app->getCacheDriver ()->fetch ( 'pastbroadcasts' );
        $response = new Response ( Http::STATUS_OK, json_encode ( $broadcasts ) );
        $response->addHeader ( Http::HEADER_CACHE_CONTROL, 'private' );
        $response->addHeader ( Http::HEADER_PRAGMA, 'public' );
        $response->addHeader ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
        return $response;
    }
}