<?php
namespace Destiny\Controllers;

use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\ResponseBody;
use Destiny\Common\Annotation\Secure;
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
     * @Route ("/api/chat/history")
     * @HttpMethod ({"GET"})
     * @ResponseBody
     *
     * @param Response $response
     * @return array
     */
    public function getBacklog(Response $response){
        $chatIntegrationService = ChatIntegrationService::instance();
        $response->addHeader(Http::HEADER_CACHE_CONTROL, 'no-cache, max-age=0, must-revalidate, no-store');
        return $chatIntegrationService->getChatLog();
    }

    /**
     * @Route ("/api/chat/me")
     * @Secure ({"USER"})
     * @HttpMethod ({"GET"})
     * @ResponseBody
     *
     * @return array
     */
    public function getUser(){
        $cred = Session::getCredentials ();
        $userService = UserService::instance();
        return [
            'nick'     => $cred->getUsername(),
            'features' => $cred->getFeatures(),
            'settings' => $userService->fetchChatSettings($cred->getUserId())
        ];
    }

    /**
     * @Route ("/api/chat/stalk")
     * @Secure ({"USER"})
     * @HttpMethod ({"GET"})
     * @ResponseBody
     *
     * @param array $params
     * @param Response $response
     * @return array|mixed|string
     */
    public function stalk(array $params, Response $response){
        if (!isset($params['username']) || preg_match ( '/^[A-Za-z0-9_]{3,20}$/', $params['username'] ) === 0){
            $response->setStatus(Http::STATUS_ERROR);
            return 'invalidnick';
        }
        $cd = Session::get('chat_ucd_stalks');
        if($cd != null && Date::getDateTime($cd) >= Date::getDateTime()){
            $response->setStatus(Http::STATUS_ERROR);
            return 'throttled';
        }
        Session::set('chat_ucd_stalks', time() + 10);
        $limit = isset($params['limit']) ? intval($params['limit']) : 3;
        $limit = $limit > 0 && $limit < 30 ? $limit : 3;
        $r = new CurlBrowser ([
            'url' => 'https://overrustlelogs.net/api/v1/stalk/Destinygg%20chatlog/'. urlencode($params['username']) .'.json?limit=' . urlencode($limit),
            'headers' => ['Client-ID' => Config::$a['meta']['shortName'].'_'.Config::version()],
            'contentType' => MimeType::JSON,
            'timeout' => 5000
        ]);
        if($r->getResponseCode() === Http::STATUS_OK){
            $response->setStatus(Http::STATUS_OK);
            return $r->getResponse();
        } else {
            $response->setStatus(Http::STATUS_ERROR);
            return 'badproxyresponse';
        }
    }

    /**
     * @Route ("/api/chat/mentions")
     * @Secure ({"USER"})
     * @HttpMethod ({"GET"})
     * @ResponseBody
     *
     * @param array $params
     * @param Response $response
     * @return array|mixed|string
     */
    public function mentions(array $params, Response $response){
        if (!isset($params['username']) || preg_match ( '/^[A-Za-z0-9_]{3,20}$/', $params['username'] ) === 0){
            $response->setStatus(Http::STATUS_ERROR);
            return 'invalidnick';
        }
        $cd = Session::get('chat_ucd_mentions');
        if($cd != null && Date::getDateTime($cd) >= Date::getDateTime()){
            $response->setStatus(Http::STATUS_ERROR);
            return 'throttled';
        }
        Session::set('chat_ucd_mentions', time() + 10);
        $limit = isset($params['limit']) ? intval($params['limit']) : 3;
        $limit = $limit > 0 && $limit < 30 ? $limit : 3;
        $r = new CurlBrowser ([
            'url' => 'https://overrustlelogs.net/api/v1/mentions/Destinygg/'. urlencode($params['username']) .'.json?limit=' . urlencode($limit),
            'headers' => ['Client-ID' => Config::$a['meta']['shortName'].'_'.Config::version()],
            'contentType' => MimeType::JSON,
            'timeout' => 5000
        ]);
        if($r->getResponseCode() === Http::STATUS_OK){
            $response->setStatus(Http::STATUS_OK);
            return $r->getResponse();
        } else {
            $response->setStatus(Http::STATUS_ERROR);
            return 'badproxyresponse';
        }
    }

    /**
     * @Route ("/api/chat/me/settings")
     * @Secure ({"USER"})
     * @HttpMethod ({"POST"})
     *
     * @param Request $request
     * @throws Exception
     */
    public function saveChatSettings(Request $request){
        $data = $request->getBody();
        if(strlen($data) <= 65535) {
            $userService = UserService::instance();
            $userId = Session::getCredentials ()->getUserId();
            $userService->saveChatSettings($userId, $request->getBody());
        } else {
            throw new Exception('toolarge');
        }
    }

    /**
     * @Route ("/api/chat/me/settings")
     * @Secure ({"USER"})
     * @HttpMethod ({"GET"})
     * @ResponseBody
     */
    public function getChatSettings(){
        $userService = UserService::instance();
        $userId = Session::getCredentials ()->getUserId();
        return $userService->fetchChatSettings($userId);
    }

    /**
     * @Route ("/api/chat/me/settings")
     * @Secure ({"USER"})
     * @HttpMethod ({"DELETE"})
     */
    public function clearChatSettings(){
        $userService = UserService::instance();
        $userId = Session::getCredentials ()->getUserId();
        $userService->deleteChatSettings($userId);
    }

    /**
     * @Route ("/api/chat/me/ban")
     * @Secure ({"USER"})
     * @HttpMethod ({"GET"})
     * @ResponseBody
     *
     * @param Request $request
     * @return array|string
     */
    public function banInfo(Request $request){
        $userService = UserService::instance();
        $userId = Session::getCredentials ()->getUserId();
        $ban = $userService->getUserActiveBan($userId, $request->ipAddress());
        return empty($ban) ? 'bannotfound' : $ban;
    }

}
