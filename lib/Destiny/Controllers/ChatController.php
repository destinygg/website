<?php
namespace Destiny\Controllers;

use Destiny\Common\Session;
use Destiny\Common\User\UserRole;
use Destiny\Common\ViewModel;
use Destiny\Common\Application;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Response;
use Destiny\Common\Utils\Http;
use Destiny\Common\MimeType;
use Destiny\Chat\ChatIntegrationService;
use Destiny\Common\Config;
use Destiny\Messages\PrivateMessageService;

/**
 * @Controller
 */
class ChatController {

    /**
     * @Route ("/chat/faq")
     *
     * @param ViewModel $model
     * @return string
     */
    public function faq(ViewModel $model) {
        $model->title = 'Frequently Asked Questions';
        return 'chat/faq';
    }

    /**
     * @Route ("/chat/halloffame")
     *
     * @param ViewModel $model
     * @return string
     */
    public function halloffame(ViewModel $model) {
        $chatIntegrationService = ChatIntegrationService::instance();
        $combos = Application::instance()->getCacheDriver()->fetch('chatcombos');

        $username = $this->getChatUser()['nick'];
        if (!empty($username)) {
            $model->myCombos = $chatIntegrationService->getMyChatCombos($username);
        }
        $model->title = 'Hall of Fame';
        $model->topCombos = $combos['top'];
        $model->recentCombos = $combos['recent'];
        return 'chat/halloffame';
    }

    /**
     * @Route ("/chat/emotes.json")
     */
    public function emotes() {
        // just return every single one
        $emotes = array_merge(
            Config::$a ['chat'] ['customemotes'],
            Config::$a ['chat'] ['twitchemotes']
        );

        $response = new Response ( Http::STATUS_OK, json_encode ( $emotes ) );
        $response->addHeader ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
        return $response;
    }

    /**
     * @Route ("/chat/init")
     */
    public function chatInit() {
        $chatIntegrationService = ChatIntegrationService::instance();
        $options = $this->getChatOptionParams ();
        $options['backlog'] = $chatIntegrationService->getChatLog();
        return $this->buildChatInitResponse($options);
    }

    /**
     * @Route ("/chat/onstream/init")
     */
    public function onStreamChatInit() {
        $chatIntegrationService = ChatIntegrationService::instance();
        $options = $this->getChatOptionParams ();
        $options['backlog'] = $chatIntegrationService->getChatLog();
        $options['maxlines'] = 30;
        return $this->buildChatInitResponse($options);
    }

    /**
     * Get the chat params from the get request
     * Make sure they are all valid
     *
     * @return array
     */
    private function getChatOptionParams() {
        $emotes = Config::$a ['chat'] ['customemotes'];
        natcasesort( $emotes );

        $twemotes = Config::$a ['chat'] ['twitchemotes'];
        natcasesort( $twemotes );

        $host = Config::$a ['chat'] ['host'];
        if(empty($host))
            $host = $_SERVER['SERVER_NAME'];

        $unreadMessageCount = (Session::hasRole(UserRole::USER)) ? PrivateMessageService::instance()->getUnreadMessageCount(Session::getCredentials()->getUserId()) : 0;

        return [
            'host'         => $host,
            'port'         => Config::$a ['chat'] ['port'],
            'maxlines'     => Config::$a ['chat'] ['maxlines'],
            'emoticons'    => array_values( $emotes ),
            'twitchemotes' => array_values( $twemotes ),
            'pmcountnum'   => $unreadMessageCount
        ];
    }

    /**
     * @return array|null
     */
    private function getChatUser(){
        $user = null;
        if (Session::hasRole(UserRole::USER)) {
            $creds = Session::getCredentials ();
            $user = array ();
            $user ['nick'] = $creds->getUsername ();
            $user ['features'] = $creds->getFeatures ();
        }
        return $user;
    }

    /**
     * @param array $options
     * @return Response
     */
    private function buildChatInitResponse(array $options){
        $user = $this->getChatUser();
        $out = 'destiny.chat = new chat(' . json_encode ([
                'user'    => $user,
                'options' => $options
            ]) . ');' . PHP_EOL;
        $out.= 'destiny.chat.start();' . PHP_EOL;
        $response = new Response ( Http::STATUS_OK, $out );
        $response->addHeader ( Http::HEADER_CONTENTTYPE, MimeType::JAVASCRIPT );
        $response->addHeader ( Http::HEADER_CACHE_CONTROL, 'no-cache, max-age=0, must-revalidate, no-store' );
        return $response;
    }

}
