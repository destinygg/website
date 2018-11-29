<?php
namespace Destiny\Controllers;

use Destiny\Common\Annotation\PrivateKey;
use Destiny\Common\Annotation\ResponseBody;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Application;
use Destiny\Common\Authentication\AuthenticationService;
use Destiny\Common\Authentication\OAuthService;
use Destiny\Common\Log;
use Destiny\Common\Request;
use Destiny\Common\Utils\FilterParams;
use Destiny\Common\Utils\FilterParamsException;
use Destiny\Common\Response;
use Destiny\Common\Utils\Http;
use Destiny\Common\User\UserService;
use Doctrine\DBAL\DBALException;

/**
 * @Controller
 */
class ApiController {

    /**
     * @Route("/api/userinfo")
     * @ResponseBody
     *
     * @param array $params
     * @throws \Exception
     * @return \Destiny\Common\Session\SessionCredentials|array
     */
    public function userInfo(array $params) {
        FilterParams::required($params, 'token');
        $token = trim($params['token']);
        $oauthService = OAuthService::instance();
        $data = $oauthService->getAccessTokenByToken($token);
        if (!empty($data)) {
            // TODO encapsulate
            if ($oauthService->hasAccessTokenExpired($data)) {
                return [
                    'error' => 'token_expired',
                    'message' => 'The token has expired.',
                    'code' => Http::STATUS_FORBIDDEN
                ];
            }
            FilterParams::required($data, 'userId');
            $userService = UserService::instance();
            $authService = AuthenticationService::instance();
            $user = $userService->getUserById($data['userId']);
            return $authService->buildUserCredentials($user);
        }
        return [
            'error' => 'invalid_token',
            'message' => 'Invalid token',
            'code' => Http::STATUS_BAD_REQUEST
        ];
    }

    /**
     * Returns JSON profile information for ANY user based on various criteria
     * - userid
     * - username
     * - discordid
     * - discordusername
     * - discordname
     * - redditname
     * - redditid
     * - minecraftname
     *
     * Requires the 'privatekey' parameter
     * If no profile is found, returns a Http 500 error, with a string error code as the body
     * If the privatekey is invalid returns Http 400 bad request
     *
     * Error codes
     *  - fielderror    = invalid parameter
     *  - usernotfound  = no user found
     *  - server        = server error
     *
     *
     * @Route ("/api/info/profile")
     * @Route ("/auth/info")
     * @HttpMethod ({"GET"})
     * @PrivateKey ({"api","reddit","minecraft"})
     * @ResponseBody
     *
     * @param Response $response
     * @param Request $request
     * @param array $params
     * @return array|string
     *
     * @throws DBALException
     */
    public function apiUserByField(Response $response, Request $request, array $params) {
        $userid = null;
        try {
            $userService = UserService::instance();
            // Depending on which privatekey is used, only certain access is permitted
            switch (strtoupper(Application::getPrivateKeyNameFromRequest($request))) {
                case 'API':
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
                    } else if (isset($params['redditid'])) {
                        FilterParams::required($params, 'redditid');
                        $userid = $userService->getUserIdByAuthId($params['redditid'], 'reddit');
                    } else {
                        $response->setStatus(Http::STATUS_BAD_REQUEST);
                        return ['message' => 'No field specified', 'error' => 'fielderror', 'code' => Http::STATUS_BAD_REQUEST];
                    }
                    break;

                case 'MINECRAFT':
                    if (isset($params['minecraftname'])) {
                        FilterParams::required($params, 'minecraftname');
                        $userid = $userService->getUserIdByField('minecraftname', $params['minecraftname']);
                    } else {
                        $response->setStatus(Http::STATUS_BAD_REQUEST);
                        return ['message' => 'No field specified', 'error' => 'fielderror', 'code' => Http::STATUS_BAD_REQUEST];
                    }
                    break;

                case 'REDDIT':
                    if (isset($params['redditname'])) {
                        FilterParams::required($params, 'redditname');
                        $userid = $userService->getUserIdByAuthDetail($params['redditname'], 'reddit');
                    } else if (isset($params['redditid'])) {
                        FilterParams::required($params, 'redditid');
                        $userid = $userService->getUserIdByAuthId($params['redditid'], 'reddit');
                    } else {
                        $response->setStatus(Http::STATUS_BAD_REQUEST);
                        return ['message' => 'No field specified', 'error' => 'fielderror', 'code' => Http::STATUS_BAD_REQUEST];
                    }
                    break;
            }
        } catch (FilterParamsException $e) {
            $response->setStatus(Http::STATUS_BAD_REQUEST);
            return ['message' => $e->getMessage(), 'error' => 'fielderror', 'code' => Http::STATUS_BAD_REQUEST];
        } catch (\Exception $e) {
            Log::error("Internal error $e");
            $response->setStatus(Http::STATUS_BAD_REQUEST);
            return ['message' => 'Server error', 'error' => 'server', 'code' => Http::STATUS_BAD_REQUEST];
        }

        if (!empty($userid)) {
            $user = $userService->getUserById($userid);
            if (!empty($user)) {
                $authService = AuthenticationService::instance();
                $response->setStatus(Http::STATUS_OK);
                return $authService->buildUserCredentials($user);
            }
        }

        $response->setStatus(Http::STATUS_ERROR);
        return ['message' => 'User not found', 'error' => 'usernotfound', 'code' => Http::STATUS_NOT_FOUND];
    }

    /**
     * Get user information using the privatekey and authtoken
     * This is used by chat server
     *  - authtoken
     *  - privatekey
     *
     * NOTE: this may be used by other people
     *
     * @Route ("/api/auth")
     * @ResponseBody
     * @PrivateKey ({"api","chat"})
     *
     * @param Response $response
     * @param array $params
     * @return array|string
     *
     * @throws DBALException
     */
    public function userByAuthToken(Response $response, array $params) {
        if (!isset ($params ['authtoken']) || empty ($params ['authtoken'])) {
            $response->setStatus(Http::STATUS_FORBIDDEN);
            return ['message' => 'Invalid or empty authToken', 'error' => 'fielderror', 'code' => Http::STATUS_FORBIDDEN];
        }
        $oauthService = OAuthService::instance();
        $accessToken = $oauthService->getAccessTokenByToken($params['token']);
        if (empty ($accessToken) || !empty($accessToken['clientId']) /* ONLY ALLOW CLIENT-LESS ACCESS KEYS */) {
            $response->setStatus(Http::STATUS_FORBIDDEN);
            return ['message' => 'Auth token not found', 'error' => 'invalidtoken', 'code' => Http::STATUS_FORBIDDEN];
        }
        if ($oauthService->hasAccessTokenExpired($accessToken)) {
            $response->setStatus(Http::STATUS_FORBIDDEN);
            return ['message' => 'Access token expired', 'error' => 'expiredtoken', 'code' => Http::STATUS_FORBIDDEN];
        }
        $userService = UserService::instance();
        $user = $userService->getUserById($accessToken['userId']);
        if (empty ($user)) {
            $response->setStatus(Http::STATUS_FORBIDDEN);
            return ['message' => 'User not found', 'error' => 'usernotfound', 'code' => Http::STATUS_FORBIDDEN];
        }
        $authService = AuthenticationService::instance();
        return $authService->buildUserCredentials($user);
    }
}