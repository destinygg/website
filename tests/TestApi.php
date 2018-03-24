<?php
use Destiny\Blog\BlogApiService;
use Destiny\Common\Config;
use Destiny\Common\Utils\ImageDownload;
use Destiny\LastFm\LastFMApiService;
use Destiny\Reddit\RedditFeedService;
use Destiny\Twitch\TwitchApiService;
use Destiny\Twitter\TwitterApiService;
use Destiny\Twitter\TwitterAuthHandler;
use Destiny\Youtube\YoutubeApiService;

class TestApi extends PHPUnit\Framework\TestCase {

    public function testDownloadImage(){
        $base = Config::$a['images']['path'];
        ImageDownload::download('https://i.ytimg.com/vi/H9aSGPimnac/default.jpg', true, $base);
        ImageDownload::download('https://lastfm-img2.akamaized.net/i/u/64s/7835874030a04069c015d6af92121c84.png', true, $base);
        ImageDownload::download('http://i.ytimg.com/vi/pKvw87dQg0Y/default.jpg', true, $base);
        ImageDownload::download('http://www.404.com/404.jpg', true, $base);
        $this->assertTrue(true);
    }

    public function testBlog() {
        $apiService = BlogApiService::instance();
        $json = $apiService->getBlogPosts();
        //print json_encode($json, JSON_PRETTY_PRINT);
        $this->assertNotEmpty($json);
    }

    public function testLastFM() {
        $apiService = LastFMApiService::instance();
        $json = $apiService->getLastPlayedTracks();
        //print json_encode($json, JSON_PRETTY_PRINT);
        $this->assertTrue($json != null && isset($json['recenttracks']));
    }

    public function testTwitchApiBroadcasts() {
        $apiService = TwitchApiService::instance();
        $json = $apiService->getPastBroadcasts();
        //print json_encode($json, JSON_PRETTY_PRINT);
        $this->assertTrue($json != null && isset($json['videos']));
    }

    public function testTwitchApiStreamInfo() {
        $apiService = TwitchApiService::instance();
        $json = $apiService->getStreamInfo(Config::$a ['twitch']['user']);
        //print json_encode($json, JSON_PRETTY_PRINT);
        $this->assertNotEmpty($json);
    }

    public function testTwitchApiChannelHost() {
        $apiService = TwitchApiService::instance();
        $json = $apiService->getChannelHost(Config::$a['twitch']['id']);
        //print json_encode($json, JSON_PRETTY_PRINT);
        $this->assertNotEmpty($json);
    }

    public function testTwitchApiChannel() {
        $apiService = TwitchApiService::instance();
        $json = $apiService->getChannel(Config::$a['twitch']['user']);
        //print json_encode($json, JSON_PRETTY_PRINT);
        $this->assertNotEmpty($json);
    }

    public function testTwitchApiHostWithInfo() {
        $apiService = TwitchApiService::instance();
        $json = $apiService->getChannelHostWithInfo(Config::$a['twitch']['id']);
        //print json_encode($json, JSON_PRETTY_PRINT);
        $this->assertNotEmpty($json);
    }

    public function testTwitchApiHosting() {
        $this->assertTrue(TwitchApiService::checkForHostingChange(['id' => 1], ['id' => 1]) === TwitchApiService::$HOST_UNCHANGED);
        $this->assertTrue(TwitchApiService::checkForHostingChange([], ['id' => 1]) === TwitchApiService::$HOST_NOW_HOSTING);
        $this->assertTrue(TwitchApiService::checkForHostingChange(['id' => 1], []) === TwitchApiService::$HOST_STOPPED);
    }

    public function testYoutubeApi() {
        $apiService = YoutubeApiService::instance();
        $json = $apiService->getYoutubePlaylist();
        //print json_encode($json, JSON_PRETTY_PRINT);
        $this->assertTrue($json != null);
    }

    public function testTwitterApi() {
        $apiService = TwitterApiService::instance();
        $json = $apiService->getTweets();
        //print json_encode($json, JSON_PRETTY_PRINT);
        $this->assertTrue($json != null);
    }

    public function testRedditThreads() {
        $apiService = RedditFeedService::instance();
        $json = $apiService->getHotThreads();
        //print json_encode($json, JSON_PRETTY_PRINT);
        $this->assertTrue($json != null);
    }

    /**
     * @throws \Destiny\Common\Exception
     */
    public function testTwitterAuth() {
        $authHandler = new TwitterAuthHandler();
        $url = $authHandler->getAuthenticationUrl();
        $this->assertNotEmpty($url);
    }
}
