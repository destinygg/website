<?php
namespace Destiny\Controllers;

use Destiny\Api\ApiAuthenticationService;
use Destiny\Common\Annotation\ResponseBody;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Authentication\AuthenticationService;
use Destiny\Common\Log;
use Destiny\Common\Utils\FilterParams;
use Destiny\Common\Utils\FilterParamsException;
use Destiny\Common\Response;
use Destiny\Common\Utils\Http;
use Destiny\Common\Config;
use Destiny\Common\User\UserService;
use Doctrine\DBAL\DBALException;

/**
 * @Controller
 */
class ApiIntegrationController {

    protected function checkPrivateKey(array $params, $type) {
        return isset($params['privatekey']) && Config::$a['privateKeys'][$type] === $params['privatekey'];
    }

    /**
     * @Route ("/api/info/profile")
     * @Route ("/auth/info")
     * @HttpMethod ({"GET"})
     * @ResponseBody
     *
     * @param Response $response
     * @param array $params
     * @return array|string
     *
     * @throws DBALException
     */
    public function profileInfo(Response $response, array $params) {
        if (!$this->checkPrivateKey($params, 'api')) {
            Log::warn('Profile info requested with bad key');
            $response->setStatus(Http::STATUS_BAD_REQUEST);
            return 'privatekey';
        }
        $userid = null;
        try {
            $userService = UserService::instance();
            if (isset($params['userid'])) {
                FilterParams::required($params, 'userid');
                $userid = $params['userid'];
            } else if (isset($params['discordid'])) {
                FilterParams::required($params, 'discordid');
                $userid = $userService->getUserIdByDiscordId($params['discordid']);
            } else if (isset($params['discordusername'])) {
                FilterParams::required($params, 'discordusername');
                $userid = $userService->getUserIdByAuthDetail($params['discordusername'], 'discord');
            } else if (isset($params['discordname'])) {
                FilterParams::required($params, 'discordname');
                $userid = $userService->getUserIdByField('discordname', $params['discordname']);
            } else if (isset($params['minecraftname'])) {
                FilterParams::required($params, 'minecraftname');
                $userid = $userService->getUserIdByField('minecraftname', $params['minecraftname']);
            } else if (isset($params['username'])) {
                FilterParams::required($params, 'username');
                $userid = $userService->getUserIdByField('username', $params['username']);
            } else if (isset($params['redditname'])) {
                FilterParams::required($params, 'redditname');
                $userid = $userService->getUserIdByAuthDetail($params['redditname'], 'reddit');
            } else {
                Log::info("No identification field");
                $response->setStatus(Http::STATUS_BAD_REQUEST);
                return 'fielderror';
            }
        } catch (FilterParamsException $e) {
            Log::error("Field error", $e);
            $response->setStatus(Http::STATUS_BAD_REQUEST);
            return 'fielderror';
        } catch (\Exception $e) {
            Log::error("Internal error", $e);
            $response->setStatus(Http::STATUS_BAD_REQUEST);
            return 'server';
        }

        if (!empty($userid)) {
            $user = $userService->getUserById($userid);
            if (!empty($user)) {
                $authService = AuthenticationService::instance();
                $creds = $authService->buildUserCredentials($user, 'request');
                $response->setStatus(Http::STATUS_OK);
                return $creds->getData();
            }
        }

        $response->setStatus(Http::STATUS_ERROR);
        return 'usernotfound';
    }

    /**
     * @Route ("/api/auth")
     * @ResponseBody
     *
     * @param Response $response
     * @param array $params
     * @return array|string
     *
     * @throws DBALException
     */
    public function authApi(Response $response, array $params) {
        if (!isset ($params ['authtoken']) || empty ($params ['authtoken'])) {
            $response->setStatus(Http::STATUS_FORBIDDEN);
            return 'Invalid or empty authToken';
        }
        $authToken = ApiAuthenticationService::instance()->getAuthToken($params ['authtoken']);
        if (empty ($authToken)) {
            $response->setStatus(Http::STATUS_FORBIDDEN);
            return 'Auth token not found';
        }
        $user = UserService::instance()->getUserById($authToken ['userId']);
        if (empty ($user)) {
            $response->setStatus(Http::STATUS_FORBIDDEN);
            return 'User not found';
        }
        $authenticationService = AuthenticationService::instance();
        $credentials = $authenticationService->buildUserCredentials($user, 'API');
        return $credentials->getData();
    }
}
