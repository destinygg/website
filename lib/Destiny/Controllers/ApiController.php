<?php
namespace Destiny\Controllers;

use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\PrivateKey;
use Destiny\Common\Annotation\ResponseBody;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Application;
use Destiny\Common\Authentication\AuthenticationService;
use Destiny\Common\Authentication\AuthProvider;
use Destiny\Common\Authentication\DggOAuthService;
use Destiny\Common\Log;
use Destiny\Common\Request;
use Destiny\Common\Response;
use Destiny\Common\Session\SessionCredentials;
use Destiny\Common\User\UserAuthService;
use Destiny\Common\User\UserService;
use Destiny\Common\Utils\FilterParams;
use Destiny\Common\Utils\FilterParamsException;
use Destiny\Common\Utils\Http;
use Doctrine\DBAL\DBALException;
use Exception;

/**
 * @Controller
 */
class ApiController {

    /**
     * @Route("/api/userinfo")
     * @ResponseBody
     *
     * @throws Exception
     * @return SessionCredentials|array
     */
    public function userInfo(array $params) {
        FilterParams::required($params, 'token');
        $token = trim($params['token']);
        $oauthService = DggOAuthService::instance();
        $data = $oauthService->getAccessTokenByToken($token);
        if (!empty($data)) {
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
     * @throws DBALException
     * @return array|SessionCredentials
     */
    public function apiUserByField(Response $response, Request $request, array $params) {
        $userid = null;
        try {
            $userService = UserService::instance();
            $userAuthService = UserAuthService::instance();
            // Depending on which privatekey is used, only certain access is permitted
            switch (strtoupper(Application::getPrivateKeyNameFromRequest($request))) {
                case 'API':
                    if (isset($params['userid'])) {
                        FilterParams::required($params, 'userid');
                        $userid = $params['userid'];
                    } else if (isset($params['discordid'])) {
                        FilterParams::required($params, 'discordid');
                        $userid = $userAuthService->getUserIdByAuthIdAndProvider($params['discordid'], AuthProvider::DISCORD);
                    } else if (isset($params['discordusername'])) {
                        FilterParams::required($params, 'discordusername');
                        $userid = $userAuthService->getUserIdByAuthDetail($params['discordusername'], AuthProvider::DISCORD);
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
                        $userid = $userAuthService->getUserIdByAuthDetail($params['redditname'], AuthProvider::REDDIT);
                    } else if (isset($params['redditid'])) {
                        FilterParams::required($params, 'redditid');
                        $userid = $userAuthService->getUserIdByAuthIdAndProvider($params['redditid'], AuthProvider::REDDIT);
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
                        $userid = $userAuthService->getUserIdByAuthDetail($params['redditname'], AuthProvider::REDDIT);
                    } else if (isset($params['redditid'])) {
                        FilterParams::required($params, 'redditid');
                        $userid = $userAuthService->getUserIdByAuthIdAndProvider($params['redditid'], AuthProvider::REDDIT);
                    } else {
                        $response->setStatus(Http::STATUS_BAD_REQUEST);
                        return ['message' => 'No field specified', 'error' => 'fielderror', 'code' => Http::STATUS_BAD_REQUEST];
                    }
                    break;
            }
        } catch (FilterParamsException $e) {
            $response->setStatus(Http::STATUS_BAD_REQUEST);
            return ['message' => $e->getMessage(), 'error' => 'fielderror', 'code' => Http::STATUS_BAD_REQUEST];
        } catch (Exception $e) {
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
     * @return SessionCredentials|array
     * @throws DBALException
     */
    public function userByAuthToken(Response $response, array $params) {
        if (!isset ($params ['authtoken']) || empty ($params ['authtoken'])) {
            $response->setStatus(Http::STATUS_FORBIDDEN);
            return ['message' => 'Invalid or empty authToken', 'error' => 'fielderror', 'code' => Http::STATUS_FORBIDDEN];
        }
        $oauthService = DggOAuthService::instance();
        $accessToken = $oauthService->getAccessTokenByToken($params['authtoken']);
        if (empty ($accessToken)) {
            $response->setStatus(Http::STATUS_FORBIDDEN);
            return ['message' => 'Auth token not found', 'error' => 'invalidtoken', 'code' => Http::STATUS_FORBIDDEN];
        }
        if (!empty($accessToken['clientId']) /* ONLY ALLOW CLIENT-LESS ACCESS KEYS */) {
            $response->setStatus(Http::STATUS_FORBIDDEN);
            return ['message' => 'Only DGG Login Keys can be used', 'error' => 'invalidtoken', 'code' => Http::STATUS_FORBIDDEN];
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