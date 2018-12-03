<?php
namespace Destiny\Controllers;

use Destiny\Chat\ChatBanService;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\ResponseBody;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\Log;
use Destiny\Common\Request;
use Destiny\Common\Session\Session;
use Destiny\Common\User\UserService;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Response;
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\Http;
use Destiny\Chat\ChatRedisService;
use Doctrine\DBAL\DBALException;
use GuzzleHttp\Client;

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

    private static $LOGS_ENDPOINT = [
        'mentions' => 'https://overrustlelogs.net/api/v1/mentions/Destinygg/',
        'stalk' => 'https://overrustlelogs.net/api/v1/stalk/Destinygg/'
    ];

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
        try {
            $client = new Client(['timeout' => 10, 'connect_timeout' => 5]);
            $r = $client->get(self::$LOGS_ENDPOINT['stalk'] . urlencode($params['username']) . '.json', [
                'headers' => ['User-Agent' => Config::userAgent()],
                'query' => ['limit' => $limit]
            ]);
            if($r->getStatusCode() == Http::STATUS_OK) {
                $response->setStatus(Http::STATUS_OK);
                return json_decode($r->getBody(), true);
            }
        } catch (\Exception $e) {
            Log::warn("Failed to return valid response for chat stalk");
        }
        $response->setStatus(Http::STATUS_ERROR);
        return 'badproxyresponse';
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
        try {
            $client = new Client(['timeout' => 10, 'connect_timeout' => 5]);
            $r = $client->get(self::$LOGS_ENDPOINT['mentions'] . urlencode($params['username']) . '.json', [
                'headers' => ['User-Agent' => Config::userAgent()],
                'query' => ['limit' => $limit]
            ]);
            if($r->getStatusCode() == Http::STATUS_OK) {
                $response->setStatus(Http::STATUS_OK);
                return json_decode($r->getBody(), true);
            }
        } catch (\Exception $e) {
            Log::warn("Failed to return valid response for chat mentions");
        }
        $response->setStatus(Http::STATUS_ERROR);
        return 'badproxyresponse';
    }
}
