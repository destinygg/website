<?php
namespace Destiny\Controllers;

use Destiny\Chat\ChatEmotes;
use Destiny\Common\Session;
use Destiny\Common\User\UserFeature;
use Destiny\Common\User\UserRole;
use Destiny\Common\ViewModel;
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
     * @Route ("/chat/emotes.json")
     */
    public function emotes() {
        // just return every single one
        $emotes = array_merge(
            ChatEmotes::get('destiny'),
            ChatEmotes::get('twitch')
        );

        $response = new Response ( Http::STATUS_OK, json_encode ( $emotes ) );
        $response->addHeader ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
        return $response;
    }

    /**
     * @Route ("/chat/init")
     */
    public function chatInit() {
        $options = $this->getChatOptionParams ();
        $options['user'] = $this->getChatUser();
        return $this->buildChatInitResponse($options);
    }

    /**
     * @Route ("/chat/history")
     */
    public function getBacklog(){
        $chatIntegrationService = ChatIntegrationService::instance();
        $backlog = $chatIntegrationService->getChatLog();
        $response = new Response (Http::STATUS_OK, json_encode($backlog));
        $response->addHeader(Http::HEADER_CONTENTTYPE, MimeType::JSON);
        $response->addHeader(Http::HEADER_CACHE_CONTROL, 'no-cache, max-age=0, must-revalidate, no-store');
        return $response;
    }

    /**
     * Get the chat params from the get request
     * Make sure they are all valid
     *
     * @return array
     */
    private function getChatOptionParams() {
        $unreadMessageCount = (Session::hasRole(UserRole::USER)) ? PrivateMessageService::instance()->getUnreadMessageCount(Session::getCredentials()->getUserId()) : 0;
        return ['options' => [
                    'uri' => Config::$a['chat']['uri'],
                    'pmcountnum' => $unreadMessageCount
                ]];
    }

    /**
     * @return array|null
     */
    private function getChatUser(){
        if (Session::hasRole(UserRole::USER)) {
            $creds = Session::getCredentials ();
            return [
                'nick' => $creds->getUsername(),
                'features' => $creds->getFeatures(),
            ];
        }
        return null;
    }

    /**
     * @param array $options
     * @return Response
     */
    private function buildChatInitResponse(array $options){
        $response = new Response ( Http::STATUS_OK, 'destiny.chat.init(' . json_encode($options) . ')' );
        $response->addHeader ( Http::HEADER_CONTENTTYPE, MimeType::JAVASCRIPT );
        $response->addHeader ( Http::HEADER_CACHE_CONTROL, 'no-cache, max-age=0, must-revalidate, no-store' );
        return $response;
    }

}
