<?php
namespace Destiny\Controllers;

use Destiny\Chat\ChatBanService;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\ResponseBody;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Exception;
use Destiny\Common\Request;
use Destiny\Common\Session\Session;
use Destiny\Common\User\UserService;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Response;
use Destiny\Common\Utils\Http;
use Destiny\Chat\ChatRedisService;
use Doctrine\DBAL\DBALException;

/**
 * @Controller
 */
class ChatController {

    /**
     * @Route ("/api/chat/history")
     * @HttpMethod ({"GET"})
     * @ResponseBody
     *
     * @param Response $response
     * @return array
     */
    public function getBacklog(Response $response){
        $redisService = ChatRedisService::instance();
        $response->addHeader(Http::HEADER_CACHE_CONTROL, 'no-cache, max-age=0, must-revalidate, no-store');
        return $redisService->getChatLog();
    }

    /**
     * @Route ("/api/chat/me")
     * @Secure ({"USER"})
     * @HttpMethod ({"GET"})
     * @ResponseBody
     *
     * @return array
     * @throws DBALException
     */
    public function getUser(){
        $userService = UserService::instance();
        $creds = Session::getCredentials ();
        $data = $creds->getData();
        $data['settings'] = $userService->fetchChatSettings($creds->getUserId());
        return $data;
    }

    /**
     * @Route ("/api/chat/me/settings")
     * @Secure ({"USER"})
     * @HttpMethod ({"POST"})
     *
     * @param Request $request
     * @throws Exception
     * @throws DBALException
     */
    public function saveChatSettings(Request $request){
        $data = $request->getBody();
        if(mb_strlen($data) <= 65535) {
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
     *
     * @throws DBALException
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
     * @throws DBALException
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
     * @throws DBALException
     */
    public function banInfo(Request $request){
        $chatBanService = ChatBanService::instance();
        $userId = Session::getCredentials ()->getUserId();
        $ban = $chatBanService->getUserActiveBan($userId, $request->address());
        return empty($ban) ? 'bannotfound' : $ban;
    }

}
