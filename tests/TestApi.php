<?php
use Destiny\Blog\BlogApiService;
use Destiny\Common\Config;
use Destiny\Common\Images\ImageDownloadUtil;
use Destiny\Common\Log;
use Destiny\LastFm\LastFMApiService;
use Destiny\LibSyn\LibSynFeedService;
use Destiny\Reddit\RedditFeedService;
use Destiny\Twitch\TwitchApiService;
use Destiny\Twitter\TwitterAuthHandler;
use Destiny\Youtube\YoutubeApiService;

class TestApi extends PHPUnit\Framework\TestCase {

    public function testDownloadImage(){
        $base = Config::$a['images']['path'];
        ImageDownloadUtil::download('http://i.ytimg.com/vi/pKvw87dQg0Y/default.jpg', false, $base);
        ImageDownloadUtil::download('http://i.ytimg.com/vi/pKvw87dQg0Y/default.jpg', false, $base);
        ImageDownloadUtil::download('http://i.ytimg.com/vi/pKvw87dQg0Y/default.jpg', true, $base);
        ImageDownloadUtil::download('http://i.ytimg.com/vi/pKvw87dQg0Y/default.jpg', true, $base);
        ImageDownloadUtil::download('https://i.ytimg.com/vi/H9aSGPimnac/default.jpg', true, $base);
        ImageDownloadUtil::download('https://lastfm-img2.akamaized.net/i/u/64s/7835874030a04069c015d6af92121c84.png', true, $base);
        ImageDownloadUtil::download('http://i.ytimg.com/vi/pKvw87dQg0Y/default.jpg', true, $base);
        ImageDownloadUtil::download('http://www.404.com/404.jpg', true, $base);
        self::assertTrue(true);
    }

    public function testBlog() {
        $apiService = BlogApiService::instance();
        $json = $apiService->getBlogPosts();
        echo json_encode($json, JSON_PRETTY_PRINT);
        self::assertNotEmpty($json);
    }

    public function testLastFM() {
        $apiService = LastFMApiService::instance();
        $json = $apiService->getLastPlayedTracks();
        echo json_encode($json, JSON_PRETTY_PRINT);
        self::assertTrue($json != null && isset($json['recenttracks']));
    }

    public function testTwitchApiBroadcasts() {
        $apiService = TwitchApiService::instance();
        $json = $apiService->getPastBroadcasts(Config::$a['twitch']['user']);
        echo json_encode($json, JSON_PRETTY_PRINT);
        self::assertTrue($json != null && isset($json['videos']));
    }

    public function testTwitchApiStreamInfo() {
        $apiService = TwitchApiService::instance();
        $json = $apiService->getStreamStatus(Config::$a ['twitch']['user']);
        echo json_encode($json, JSON_PRETTY_PRINT);
        self::assertNotEmpty($json);
    }

    public function testTwitchApiChannelHost() {
        $apiService = TwitchApiService::instance();
        $json = $apiService->getChannelHost(Config::$a['twitch']['id']);
        echo json_encode($json, JSON_PRETTY_PRINT);
        self::assertNotEmpty($json);
    }

    public function testTwitchApiChannel() {
        $apiService = TwitchApiService::instance();
        $json = $apiService->getChannel(Config::$a['twitch']['user']);
        echo json_encode($json, JSON_PRETTY_PRINT);
        self::assertNotEmpty($json);
    }

    public function testTwitchApiLive() {
        $apiService = TwitchApiService::instance();
        $json = $apiService->getStreamLiveDetails(Config::$a['twitch']['id']);
        echo json_encode($json, JSON_PRETTY_PRINT);
        self::assertNotEmpty($json);
    }

    public function testTwitchApiHostWithInfo() {
        $apiService = TwitchApiService::instance();
        $json = $apiService->getChannelHostWithInfo(Config::$a['twitch']['id']);
        echo json_encode($json, JSON_PRETTY_PRINT);
        self::assertNotEmpty($json);
    }

    public function testTwitchLibSyn() {
        $libSynService = LibSynFeedService::instance();
        $json = $libSynService->getFeed(Config::$a['libsyn']['user']);
        echo json_encode($json, JSON_PRETTY_PRINT);
        self::assertNotEmpty($json);
    }

    public function testTwitchApiHosting() {
        self::assertTrue(TwitchApiService::checkForHostingChange(['id' => 1], ['id' => 1]) === TwitchApiService::$HOST_UNCHANGED);
        self::assertTrue(TwitchApiService::checkForHostingChange([], ['id' => 1]) === TwitchApiService::$HOST_NOW_HOSTING);
        self::assertTrue(TwitchApiService::checkForHostingChange(['id' => 1], []) === TwitchApiService::$HOST_STOPPED);
    }

    public function testYoutubeApi() {
        $apiService = YoutubeApiService::instance();
        $json = $apiService->getYoutubePlaylist();
        echo json_encode($json, JSON_PRETTY_PRINT);
        self::assertTrue($json != null);
    }

    public function testRedditThreads() {
        $apiService = RedditFeedService::instance();
        $json = $apiService->getHotThreads();
        echo json_encode($json, JSON_PRETTY_PRINT);
        self::assertTrue($json != null);
    }

    /**
     * @throws \Destiny\Common\Exception
     */
    public function testTwitterAuth() {
        $authHandler = new TwitterAuthHandler();
        $url = $authHandler->getAuthenticationUrl();
        self::assertNotEmpty($url);
    }
}
