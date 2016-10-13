<?php

use Destiny\Blog\BlogApiService;
use Destiny\Common\Config;
use Destiny\Common\Utils\ImageDownload;
use Destiny\LastFm\LastFMApiService;
use Destiny\Twitch\TwitchApiService;
use Destiny\Twitter\TwitterApiService;
use Destiny\Youtube\YoutubeApiService;

class GuzzleTest extends PHPUnit_Framework_TestCase {

    public function testDownloadImage(){
        $base = Config::$a["images"]["path"];
        $path = ImageDownload::download("https://static-cdn.jtvnw.net/v1/AUTH_system/vods_94ff/destiny_22634040736_494441835/thumb/thumb0-320x240.jpg", $base);
        //print $path . PHP_EOL;
        $path = ImageDownload::download("https://i.ytimg.com/vi/H9aSGPimnac/default.jpg", $base);
        //print $path . PHP_EOL;
        $path = ImageDownload::download("https://lastfm-img2.akamaized.net/i/u/64s/7835874030a04069c015d6af92121c84.png", $base);
        //print $path . PHP_EOL;
        $path = ImageDownload::download("https://vod-storyboards.twitch.tv/v1/AUTH_system/vods_94ff/destiny_22634040736_494441835/storyboards/81448366-gif.gif", $base);
        //print $path . PHP_EOL;
        $path = ImageDownload::download("http://i.ytimg.com/vi/pKvw87dQg0Y/default.jpg", $base);
        //print $path . PHP_EOL;
    }

    public function testBlog() {
        $apiService = BlogApiService::instance();
        $json = $apiService->getBlogPosts();
        //print json_encode($json, JSON_PRETTY_PRINT);
        $this->assertTrue($json != null);
    }

    public function testLastFM() {
        $apiService = LastFMApiService::instance();
        $json = $apiService->getLastFMTracks()->getResponse();
        //print json_encode($json, JSON_PRETTY_PRINT);
        $this->assertTrue($json != null && isset($json["recenttracks"]));
    }

    public function testTwitchApi() {
        $apiService = TwitchApiService::instance();
        $json = $apiService->getPastBroadcasts();
        //print json_encode($json, JSON_PRETTY_PRINT);
        $this->assertTrue($json != null && isset($json["videos"]));
    }

    public function testTwitchApi2() {
        $apiService = TwitchApiService::instance();
        $json = $apiService->getStreamInfo(Config::$a ['twitch']['user']);
        //print json_encode($json, JSON_PRETTY_PRINT);
        $this->assertTrue($json !== null);
    }

    public function testTwitchApi3() {
        $apiService = TwitchApiService::instance();
        $json = $apiService->getChannelHost(Config::$a['twitch']['id']);
        //print json_encode($json, JSON_PRETTY_PRINT);
        $this->assertTrue($json !== null);
    }

    public function testTwitchApi4() {
        $apiService = TwitchApiService::instance();
        $json = $apiService->getChannel(Config::$a['twitch']['user']);
        //print json_encode($json, JSON_PRETTY_PRINT);
        $this->assertTrue($json !== null);
    }

    public function testTwitchApi5() {
        $apiService = TwitchApiService::instance();
        $json = $apiService->getChannel(Config::$a['twitch']['user']);
        //print json_encode($json, JSON_PRETTY_PRINT);
        $this->assertTrue($json !== null);
    }

    public function testTwitchApi6() {
        $apiService = TwitchApiService::instance();
        $json = $apiService->getChannelHostWithInfo(Config::$a['twitch']['id']);
        //print json_encode($json, JSON_PRETTY_PRINT);
        $this->assertTrue($json !== null);
    }

    public function testTwitchApi7() {
        $this->assertTrue(TwitchApiService::checkForHostingChange(['id' => 1], ['id' => 1]) === TwitchApiService::$HOST_UNCHANGED);
        $this->assertTrue(TwitchApiService::checkForHostingChange([], ['id' => 1]) === TwitchApiService::$HOST_NOW_HOSTING);
        $this->assertTrue(TwitchApiService::checkForHostingChange(['id' => 1], []) === TwitchApiService::$HOST_STOPPED);
    }

    public function testYoutubeApi() {
        $apiService = YoutubeApiService::instance();
        $json = $apiService->getYoutubePlaylist()->getResponse();
        //print json_encode($json, JSON_PRETTY_PRINT);
        $this->assertTrue($json != null);
    }

    public function testTwitterApi() {
        $apiService = TwitterApiService::instance();
        $json = $apiService->getTweets();
        //print json_encode($json, JSON_PRETTY_PRINT);
        $this->assertTrue($json != null);
    }
}
