<?php
namespace Destiny\Controllers;

use Destiny\Chat\ChatBanService;
use Destiny\Chat\ChatRedisService;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\ResponseBody;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Authentication\AuthenticationService;
use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\HttpClient;
use Destiny\Common\Log;
use Destiny\Common\Request;
use Destiny\Common\Response;
use Destiny\Common\Session\Session;
use Destiny\Common\User\UserService;
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\Http;

/**
 * @Controller
 */
class ChatController {

    /**
     * @Route ("/api/chat/history")
     * @HttpMethod ({"GET"})
     * @ResponseBody
     *
     * @return array|null
     */
    public function getBacklog(Response $response) {
        $redisService = ChatRedisService::instance();
        $response->addHeader(Http::HEADER_CACHE_CONTROL, 'no-cache, max-age=0, must-revalidate, no-store');
        return $redisService->getChatLog();
    }

    /**
     * @Route ("/api/chat/me")
     * @Secure ({"USER"})
     * @HttpMethod ({"GET"})
     * @ResponseBody
     * @throws Exception
     */
    public function getUser(): array {
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
     * @throws Exception
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
     * @throws Exception
     */
    public function getChatSettings(): array {
        $userService = UserService::instance();
        $userId = Session::getCredentials ()->getUserId();
        return $userService->fetchChatSettings($userId);
    }

    /**
     * @Route ("/api/chat/me/settings")
     * @Secure ({"USER"})
     * @HttpMethod ({"DELETE"})
     * @throws Exception
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
     * @return array|string
     * @throws Exception
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
     * @return array|mixed|string
     */
    public function stalk(array $params, Response $response) {
        if (!isset($params['username']) || preg_match(AuthenticationService::REGEX_VALID_USERNAME, $params['username']) === 0) {
            $response->setStatus(Http::STATUS_ERROR);
            return 'invalidnick';
        }
        $cd = Session::get('chat_ucd_stalks');
        if ($cd != null && Date::getDateTime($cd) >= Date::getDateTime()) {
            $response->setStatus(Http::STATUS_ERROR);
            return 'throttled';
        }
        Session::set('chat_ucd_stalks', time() + 10);
        $limit = isset($params['limit']) ? intval($params['limit']) : 3;
        $limit = $limit > 0 && $limit < 30 ? $limit : 3;
        $client = HttpClient::instance();
        $r = $client->get(self::$LOGS_ENDPOINT['stalk'] . urlencode($params['username']) . '.json', [
            'headers' => ['User-Agent' => Config::userAgent()],
            'query' => ['limit' => $limit]
        ]);
        if ($r->getStatusCode() == Http::STATUS_OK || $r->getStatusCode() == Http::STATUS_NOT_FOUND) {
            $response->setStatus(Http::STATUS_OK);
            return json_decode($r->getBody(), true);
        } else {
            Log::warn('Failed to return valid response for chat mentions', ['message' => $response->getBody()]);
        }
        $response->setStatus(Http::STATUS_ERROR);
        return 'badproxyresponse';
    }

    /**
     * @Route ("/api/chat/mentions")
     * @Secure ({"USER"})
     * @HttpMethod ({"GET"})
     * @ResponseBody
     * @return array|mixed|string
     */
    public function mentions(array $params, Response $response) {
        if (!isset($params['username']) || preg_match(AuthenticationService::REGEX_VALID_USERNAME, $params['username']) === 0) {
            $response->setStatus(Http::STATUS_ERROR);
            return 'invalidnick';
        }
        $cd = Session::get('chat_ucd_mentions');
        if ($cd != null && Date::getDateTime($cd) >= Date::getDateTime()) {
            $response->setStatus(Http::STATUS_ERROR);
            return 'throttled';
        }
        Session::set('chat_ucd_mentions', time() + 10);
        $limit = isset($params['limit']) ? intval($params['limit']) : 3;
        $limit = $limit > 0 && $limit < 30 ? $limit : 3;
        $client = HttpClient::instance();
        $r = $client->get(self::$LOGS_ENDPOINT['mentions'] . urlencode($params['username']) . '.json', [
            'headers' => ['User-Agent' => Config::userAgent()],
            'query' => ['limit' => $limit]
        ]);
        if ($r->getStatusCode() == Http::STATUS_OK || $r->getStatusCode() == Http::STATUS_NOT_FOUND) {
            $response->setStatus(Http::STATUS_OK);
            return json_decode($r->getBody(), true);
        } else {
            Log::warn('Failed to return valid response for chat mentions', ['message' => $response->getBody()]);
        }
        $response->setStatus(Http::STATUS_ERROR);
        return 'badproxyresponse';
    }
}
