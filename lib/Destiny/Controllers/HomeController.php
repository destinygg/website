<?php
namespace Destiny\Controllers;

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
        if (Session::hasRole(UserRole::USER)) {
            $userid = $userId = Session::getCredentials ()->getUserId ();
            $privateMessageService = PrivateMessageService::instance();
            $model->unreadMessageCount = $privateMessageService->getUnreadMessageCount($userid);
        }

        $app = Application::instance ();
        $cacheDriver = $app->getCacheDriver ();
        $model->articles = $cacheDriver->fetch ( 'recentblog' );
        $model->summoners = $cacheDriver->fetch ( 'summoners' );
        $model->tweets = $cacheDriver->fetch ( 'twitter' );
        $model->music = $cacheDriver->fetch ( 'recenttracks' );
        $model->playlist = $cacheDriver->fetch ( 'youtubeplaylist' );
        $model->broadcasts = $cacheDriver->fetch ( 'pastbroadcasts' );
        $model->streamInfo = $cacheDriver->fetch ( 'streaminfo' );
        return 'home';
    }

    /**
     * @Route ("/help/agreement")
     *
     * @param ViewModel $model
     * @return string
     */
    public function helpAgreement(ViewModel $model) {
        $model->title = 'User agreement';
        return 'help/agreement';
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
     * @Route ("/screen")
     *
     * @return Response
     */
    public function screen() {
        $response = new Response ( Http::STATUS_MOVED_PERMANENTLY );
        $response->setLocation ( '/bigscreen' );
        return $response;
    }

    /**
     * @Route ("/bigscreen")
     *
     * @param ViewModel $model
     * @return string
     */
    public function bigscreen(ViewModel $model) {
        $model->streamInfo = Application::instance ()->getCacheDriver ()->fetch ( 'streaminfo' );
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
        return 'redirect: http://ting.7eer.net/c/72409/87559/2020';
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
        return 'redirect: http://teespring.com/twitch/desteeny2';
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

}
