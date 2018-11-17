<?php
namespace Destiny\Controllers;

use Destiny\Api\ApiAuthenticationService;
use Destiny\Common\Annotation\PrivateKey;
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
use Destiny\Common\User\UserService;
use Doctrine\DBAL\DBALException;

/**
 * @Controller
 */
class ApiController {

    /**
     * Returns JSON profile information for ANY user based on various criteria
     * - userid
     * - username
     * - discordid
     * - discordusername
     * - discordname
     * - redditname
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
     * @PrivateKey ({"api","chat","reddit"})
     * @ResponseBody
     *
     * @param Response $response
     * @param array $params
     * @return array|string
     *
     * @throws DBALException
     */
    public function userByField(Response $response, array $params) {
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
            } else if (isset($params['redditid'])) {
                FilterParams::required($params, 'redditid');
                $userid = $userService->getUserIdByAuthId($params['redditid'], 'reddit');
            } else {
                $response->setStatus(Http::STATUS_BAD_REQUEST);
                return ['message' => 'No field specified', 'error' => 'fielderror', 'code' => Http::STATUS_BAD_REQUEST];
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
                $creds = $authService->buildUserCredentials($user);
                $response->setStatus(Http::STATUS_OK);
                return $creds;
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
     * @PrivateKey ({"api","chat","reddit"})
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
        $authToken = ApiAuthenticationService::instance()->getAuthTokenByToken($params ['authtoken']);
        if (empty ($authToken)) {
            $response->setStatus(Http::STATUS_FORBIDDEN);
            return ['message' => 'Auth token not found', 'error' => 'invalidtoken', 'code' => Http::STATUS_FORBIDDEN];
        }
        $user = UserService::instance()->getUserById($authToken ['userId']);
        if (empty ($user)) {
            $response->setStatus(Http::STATUS_FORBIDDEN);
            return ['message' => 'User not found', 'error' => 'usernotfound', 'code' => Http::STATUS_FORBIDDEN];
        }
        $authService = AuthenticationService::instance();
        $credentials = $authService->buildUserCredentials($user);
        return $credentials;
    }
}
