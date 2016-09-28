<?php
namespace Destiny\Controllers;

use Destiny\Common\MimeType;
use Destiny\Common\ViewModel;
use Destiny\Common\Application;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Response;
use Destiny\Common\Utils\Http;
use Destiny\Common\Config;
use Destiny\Common\Session;
use Destiny\Common\User\UserRole;
use Destiny\Messages\PrivateMessageService;
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
        if (Session::hasRole(UserRole::USER))
            $model->unreadMessageCount = PrivateMessageService::instance()->getUnreadMessageCount(Session::getCredentials()->getUserId());

        $cache = Application::instance ()->getCacheDriver ();
        $model->articles = $cache->fetch ( 'recentblog' );
        $model->summoners = $cache->fetch ( 'summoners' );
        $model->tweets = $cache->fetch ( 'twitter' );
        $model->music = $cache->fetch ( 'recenttracks' );
        $model->playlist = $cache->fetch ( 'youtubeplaylist' );
        $model->broadcasts = $cache->fetch ( 'pastbroadcasts' );
        return 'home';
    }

    /**
     * @Route ("/stream.json")
     * @return Response
     */
    public function stream() {
        $cache = Application::instance()->getCacheDriver();
        $streaminfo = $cache->contains('streamstatus') ? $cache->fetch('streamstatus') : TwitchApiService::$STREAM_INFO;
        $json = json_encode($streaminfo);
        $response = new Response (Http::STATUS_OK, json_encode($streaminfo));
        $response->addHeader(Http::HEADER_CACHE_CONTROL, 'private');
        $response->addHeader(Http::HEADER_PRAGMA, 'public');
        $response->addHeader(Http::HEADER_CONTENTTYPE, MimeType::JSON);
        $response->addHeader(Http::HEADER_ETAG, md5($json));
        return $response;
    }

    /**
     * @Route ("/help/agreement")
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
     * @return Response
     */
    public function ping() {
        $response = new Response ( Http::STATUS_OK );
        $response->addHeader ( 'X-Pong', 'Destiny' );
        return $response;
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
     * @Route ("/emotes")
     *
     * @param ViewModel $model
     * @return string
     */
    public function emoticons(ViewModel $model) {
        $emotes = Config::$a['chat'] ['customemotes'];
        natcasesort( $emotes );
        $model->emoticons = $emotes;

        $twemotes = Config::$a['chat'] ['twitchemotes'];
        natcasesort( $twemotes );
        $model->twitchemotes = $twemotes;

        $model->title = 'Emoticons';
        return 'chat/emoticons';
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

    /**
     * @Route ("/ting")
     *
     * @return string
     */
    public function ting() {
        return 'redirect: https://ting.7eer.net/c/72409/87559/2020';
    }

    /**
     * @Route ("/eve")
     *
     * @return string
     */
    public function eve() {
        return 'redirect: https://secure.eveonline.com/trial/?invc=7a8cfcda-5915-4297-9cf9-ed898d984ff2&action=buddy';
    }

    /**
     * @Route ("/schedule")
     *
     * @return string
     */
    public function schedule() {
        return 'redirect: https://www.google.com/calendar/embed?src=i54j4cu9pl4270asok3mqgdrhk%40group.calendar.google.com';
    }

    /**
     * @Route ("/shirt")
     *
     * @return string
     */
    public function shirts() {
        return 'redirect: https://teespring.com/twitch/desteeny';
    }

    /**
     * @Route ("/chair")
     *
     * @return string
     */
    public function chair() {
        return 'redirect: http://www.4gamergear.com#oid=1027_1';
    }

    /**
     * @Route ("/forge")
     *
     * @return string
     */
    public function forge() {
        return 'redirect: https://bit.ly/ForgeDestiny';
    }

    /**
     * @Route ("/twitter")
     *
     * @return string
     */
    public function twitter() {
        return 'redirect: https://twitter.com/OmniDestiny';
    }

    /**
     * @Route ("/facebook")
     *
     * @return string
     */
    public function facebook() {
        return 'redirect: https://www.facebook.com/Steven.Bonnell.II';
    }

    /**
     * @Route ("/youtube")
     *
     * @return string
     */
    public function youtube() {
        return 'redirect: https://www.youtube.com/user/Destiny';
    }

    /**
     * @Route ("/reddit")
     *
     * @return string
     */
    public function reddit() {
        return 'redirect: https://www.reddit.com/r/Destiny';
    }

    /**
     * @Route ("/github")
     *
     * @return string
     */
    public function github() {
        return 'redirect: https://github.com/destinygg';
    }

    /**
     * @Route ("/twitch")
     *
     * @return string
     */
    public function twitch() {
        return 'redirect: https://www.twitch.tv/destiny';
    }

    /**
     * @Route ("/lastfm")
     *
     * @return string
     */
    public function lastfm() {
        return 'redirect: http://www.last.fm/user/StevenBonnellII';
    }

    /**
     * @Route ("/donate")
     *
     * @return string
     */
    public function donate() {
        return 'redirect: https://www.twitchalerts.com/donate/destiny';
    }

    /**
     * @Route ("/blog")
     *
     * @return string
     */
    public function blog() {
        return 'redirect: http://blog.destiny.gg';
    }

    /**
     * @Route ("/loots")
     * @Route ("/loot")
     *
     * @return string
     */
    public function loots() {
        return 'redirect: https://loots.com/destiny';
    }

    /**
     * @Route ("/gmg")
     * @Route ("/greenmangaming")
     *
     * @return string
     */
    public function gmg() {
        return 'redirect: https://www.greenmangaming.com/?tap_a=1964-996bbb&tap_s=55177-fd1979';
    }

}
