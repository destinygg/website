<?php
namespace Destiny\Controllers;

use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\CurlBrowser;
use Destiny\Common\Exception;
use Destiny\Common\Request;
use Destiny\Common\Session;
use Destiny\Common\User\UserService;
use Destiny\Common\Utils\Date;
use Destiny\Common\ViewModel;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Response;
use Destiny\Common\Utils\Http;
use Destiny\Common\MimeType;
use Destiny\Chat\ChatIntegrationService;

/**
 * @Controller
 */
class ChatController {

    /**
     * @Route ("/chat/faq")
     * @HttpMethod ({"GET"})
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
     * @HttpMethod ({"GET"})
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
     * @Route ("/chat/me")
     * @Secure ({"USER"})
     * @HttpMethod ({"GET"})
     */
    public function getUser(){
        $cred = Session::getCredentials ();
        $response = new Response (Http::STATUS_OK, json_encode([
            'nick'     => $cred->getUsername(),
            'features' => $cred->getFeatures()
        ]));
        $response->addHeader(Http::HEADER_CONTENTTYPE, MimeType::JSON);
        return $response;
    }

    /**
     * @Route ("/chat/api/v1/{username}/stalk")
     * @Secure ({"USER"})
     * @HttpMethod ({"GET"})
     *
     * @param array $params
     * @return Response
     */
    public function stalk(array $params){
        if (!isset($params['username']) || preg_match ( '/^[A-Za-z0-9_]{3,20}$/', $params['username'] ) === 0){
            $response = new Response (Http::STATUS_ERROR, "invalidnick");
            $response->addHeader(Http::HEADER_CONTENTTYPE, MimeType::JSON);
            return $response;
        }
        $cd = Session::get('chat_ucd_stalks');
        if($cd != null && Date::getDateTime($cd) >= Date::getDateTime()){
            $response = new Response (Http::STATUS_ERROR, "throttled");
            $response->addHeader(Http::HEADER_CONTENTTYPE, MimeType::JSON);
            return $response;
        }
        Session::set('chat_ucd_stalks', time() + 10);
        $r = new CurlBrowser ([
            'url' => 'https://overrustlelogs.net/api/v1/stalk/Destinygg%20chatlog/'. urlencode($params['username']) .'.json?limit=8',
            'headers' => ['Client-ID' => Config::$a['meta']['shortName'].'_'.Config::version()],
            'contentType' => MimeType::JSON,
            'timeout' => 5000
        ]);
        if($r->getResponseCode() === Http::STATUS_OK){
            $response = new Response (Http::STATUS_OK, json_encode($r->getResponse()));
        } else {
            $response = new Response (Http::STATUS_ERROR);
        }
        $response->addHeader(Http::HEADER_CONTENTTYPE, MimeType::JSON);
        return $response;
    }

    /**
     * @Route ("/chat/api/v1/{username}/mentions")
     * @Secure ({"USER"})
     * @HttpMethod ({"GET"})
     *
     * @param array $params
     * @return Response
     */
    public function mentions(array $params){
        if (!isset($params['username']) || preg_match ( '/^[A-Za-z0-9_]{3,20}$/', $params['username'] ) === 0){
            $response = new Response (Http::STATUS_ERROR, "invalidnick");
            $response->addHeader(Http::HEADER_CONTENTTYPE, MimeType::JSON);
            return $response;
        }
        $cd = Session::get('chat_ucd_mentions');
        if($cd != null && Date::getDateTime($cd) >= Date::getDateTime()){
            $response = new Response (Http::STATUS_ERROR, "throttled");
            $response->addHeader(Http::HEADER_CONTENTTYPE, MimeType::JSON);
            return $response;
        }
        Session::set('chat_ucd_mentions', time() + 10);
        $r = new CurlBrowser ([
            'url' => 'https://polecat.me/api/mentions/'. urlencode($params['username']) .'?size=8',
            'headers' => ['Client-ID' => Config::$a['meta']['shortName'].'_'.Config::version()],
            'contentType' => MimeType::JSON,
            'timeout' => 5000
        ]);
        if($r->getResponseCode() === Http::STATUS_OK){
            $response = new Response (Http::STATUS_OK, json_encode($r->getResponse()));
        } else {
            $response = new Response (Http::STATUS_ERROR);
        }
        $response->addHeader(Http::HEADER_CONTENTTYPE, MimeType::JSON);
        return $response;
    }

    /**
     * @Route ("/chat/settings")
     * @Secure ({"USER"})
     * @HttpMethod ({"POST"})
     *
     * @param Request $request
     * @return Response
     */
    public function saveChatSettings(Request $request){
        $response = new Response ();
        try {
            $data = $request->getBody();
            if(strlen($data) <= 65535) {
                $userService = UserService::instance();
                $userId = Session::getCredentials ()->getUserId();
                $userService->saveChatSettings($userId, $request->getBody());
                $response->setStatus(Http::STATUS_OK);
            } else {
                throw new Exception("toolarge");
            }
        } catch (\Exception $e) {
            $response->setStatus(Http::STATUS_ERROR);
            $response->setBody($e->getMessage());
        }
        $response->addHeader(Http::HEADER_CONTENTTYPE, MimeType::JSON);
        return $response;
    }

    /**
     * @Route ("/chat/settings")
     * @Secure ({"USER"})
     * @HttpMethod ({"GET"})
     *
     * @return Response
     */
    public function getChatSettings(){
        $response = new Response ();
        try {
            $userService = UserService::instance();
            $userId = Session::getCredentials ()->getUserId();
            $response->setStatus(Http::STATUS_OK);
            $response->setBody(json_encode($userService->fetchChatSettings($userId)));
        } catch (\Exception $e) {
            $response->setStatus(Http::STATUS_ERROR);
            $response->setBody($e->getMessage());
        }
        $response->addHeader(Http::HEADER_CONTENTTYPE, MimeType::JSON);
        return $response;
    }

    /**
     * @Route ("/chat/settings")
     * @Secure ({"USER"})
     * @HttpMethod ({"DELETE"})
     *
     * @return Response
     */
    public function clearChatSettings(){
        $response = new Response ();
        try {
            $userService = UserService::instance();
            $userId = Session::getCredentials ()->getUserId();
            $userService->deleteChatSettings($userId);
            $response->setStatus(Http::STATUS_OK);
        } catch (\Exception $e) {
            $response->setStatus(Http::STATUS_ERROR);
            $response->setBody($e->getMessage());
        }
        $response->addHeader(Http::HEADER_CONTENTTYPE, MimeType::JSON);
        return $response;
    }

}
