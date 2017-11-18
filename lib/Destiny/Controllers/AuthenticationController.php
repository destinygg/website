<?php
namespace Destiny\Controllers;

use Destiny\Api\ApiAuthenticationService;
use Destiny\Chat\ChatIntegrationService;
use Destiny\Common\Annotation\ResponseBody;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Authentication\AuthenticationService;
use Destiny\Common\Log;
use Destiny\Common\Session;
use Destiny\Common\User\UserRole;
use Destiny\Common\Utils\FilterParams;
use Destiny\Common\Utils\FilterParamsException;
use Destiny\Discord\DiscordAuthHandler;
use Destiny\Google\GoogleAuthHandler;
use Destiny\Common\ViewModel;
use Destiny\Twitter\TwitterAuthHandler;
use Destiny\Twitch\TwitchAuthHandler;
use Destiny\Common\Response;
use Destiny\Common\Utils\Http;
use Destiny\Reddit\RedditAuthHandler;
use Destiny\Common\Exception;
use Destiny\Common\Config;
use Destiny\Common\User\UserService;
use Doctrine\DBAL\DBALException;

/**
 * @Controller
 */
class AuthenticationController {

    protected function checkPrivateKey(array $params, $type) {
        return isset($params['privatekey']) && Config::$a['privateKeys'][$type] === $params['privatekey'];
    }

    /**
     * @Route ("/login")
     * @HttpMethod ({"GET"})
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     */
    public function login(array $params, ViewModel $model) {
        Session::remove('accountMerge');
        $model->title = 'Login';
        $model->follow = (isset ($params ['follow'])) ? $params ['follow'] : '';
        return 'login';
    }

    /**
     * @Route ("/login")
     * @HttpMethod ({"POST"})
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     *
     * @throws Exception
     */
    public function loginPost(array $params, ViewModel $model) {
        $authProvider = (isset ($params ['authProvider']) && !empty ($params['authProvider'])) ? $params ['authProvider'] : '';
        $rememberme = (isset ($params ['rememberme']) && !empty ($params ['rememberme'])) ? true : false;
        if (empty ($authProvider)) {
            $model->title = 'Login error';
            $model->rememberme = $rememberme;
            $model->error = new Exception ('Please select a authentication provider');
            return 'error';
        }
        Session::start();
        if ($rememberme) {
            Session::set('rememberme', 1);
        }
        if (isset ($params ['follow']) && !empty ($params ['follow'])) {
            Session::set('follow', $params ['follow']);
        }
        switch (strtoupper($authProvider)) {
            case 'TWITCH' :
                $authHandler = new TwitchAuthHandler ();
                return 'redirect: ' . $authHandler->getAuthenticationUrl();

            case 'GOOGLE' :
                $authHandler = new GoogleAuthHandler ();
                return 'redirect: ' . $authHandler->getAuthenticationUrl();

            case 'TWITTER' :
                $authHandler = new TwitterAuthHandler ();
                return 'redirect: ' . $authHandler->getAuthenticationUrl();

            case 'REDDIT' :
                $authHandler = new RedditAuthHandler ();
                return 'redirect: ' . $authHandler->getAuthenticationUrl();

            case 'DISCORD' :
                $authHandler = new DiscordAuthHandler ();
                return 'redirect: ' . $authHandler->getAuthenticationUrl();

            default :
                $model->title = 'Login error';
                $model->rememberme = $rememberme;
                $model->error = new Exception ('Authentication type not supported');
                return 'error';
        }
    }

    /**
     * @Route ("/logout")
     *
     * @return string
     */
    public function logout() {
        if (Session::isStarted()) {
            ChatIntegrationService::instance()->deleteChatSession(Session::getSessionId());
            Session::destroy();
        }
        return 'redirect: /';
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
        if(! $this->checkPrivateKey($params, 'api')) {
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
                $userid = $userService->getUserIdByDiscordUsername($params['discordusername']);
            } else if (isset($params['discordname'])) {
                FilterParams::required($params, 'discordname');
                $userid = $userService->getUserIdByField('discordname', $params['discordname']);
            } else if (isset($params['username'])) {
                FilterParams::required($params, 'username');
                $userid = $userService->getUserIdByField('username', $params['username']);
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

        if(!empty($userid)) {
            $user = $userService->getUserById($userid);
            if(!empty($user)){
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
    public function authApi(Response $response, array $params){
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

    /**
     * @Route ("/auth/twitch")
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     */
    public function authTwitch(array $params, ViewModel $model) {
        try {
            $authHandler = new TwitchAuthHandler ();
            return $authHandler->authenticate ( $params );
        } catch ( \Exception $e ) {
            return $this->handleAuthError($e, $model);
        }
    }

    /**
     * @Route ("/auth/twitter")
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     */
    public function authTwitter(array $params, ViewModel $model) {
        try {
            $authHandler = new TwitterAuthHandler ();
            return $authHandler->authenticate ( $params );
        } catch ( \Exception $e ) {
            return $this->handleAuthError($e, $model);
        }
    }

    /**
     * @Route ("/auth/google")
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     */
    public function authGoogle(array $params, ViewModel $model) {
        try {
            $authHandler = new GoogleAuthHandler ();
            return $authHandler->authenticate ( $params );
        } catch ( \Exception $e ) {
            return $this->handleAuthError($e, $model);
        }
    }

    /**
     * @Route ("/auth/reddit")
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     */
    public function authReddit(array $params, ViewModel $model) {
        try {
            $authHandler = new RedditAuthHandler ();
            return $authHandler->authenticate ( $params );
        } catch ( \Exception $e ) {
            return $this->handleAuthError($e, $model);
        }
    }

    /**
     * @Route ("/auth/discord")
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     */
    public function authDiscord(array $params, ViewModel $model) {
        try {
            $authHandler = new DiscordAuthHandler();
            return $authHandler->authenticate($params);
        } catch (\Exception $e) {
            return $this->handleAuthError($e, $model);
        }
    }

    /**
     * @param \Exception $e
     * @param ViewModel $model
     * @return string
     */
    private function handleAuthError(\Exception $e, ViewModel $model) {
        if(Session::hasRole ( UserRole::USER )){
            Session::setErrorBag($e->getMessage());
            return 'redirect: /profile/authentication';
        } else {
            $model->title = 'Login error';
            $model->error = $e;
            return 'error';
        }
    }
}
