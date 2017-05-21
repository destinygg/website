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
     * Chat expects a slightly different signature that what we have in the session
     *
     * @Route ("/chat/me")
     */
    public function getUser(){
        $cred = Session::getCredentials ();
        $user = [
            'nick'     => $cred->getUsername(),
            'features' => $cred->getFeatures()
        ];
        $response = new Response (Http::STATUS_OK, json_encode($user));
        $response->addHeader(Http::HEADER_CONTENTTYPE, MimeType::JSON);
        return $response;
    }

}
