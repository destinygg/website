<?php
namespace Destiny\Controllers;

use Destiny\Chat\ChatRedisService;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Authentication\OAuthService;
use Destiny\Common\AuthHandlerInterface;
use Destiny\Common\Exception;
use Destiny\Common\Request;
use Destiny\Common\Session\Session;
use Destiny\Common\User\UserRole;
use Destiny\Common\ViewModel;
use Destiny\Discord\DiscordAuthHandler;
use Destiny\Google\GoogleAuthHandler;
use Destiny\Reddit\RedditAuthHandler;
use Destiny\Twitch\TwitchAuthHandler;
use Destiny\Twitter\TwitterAuthHandler;
use Doctrine\DBAL\DBALException;

/**
 * @Controller
 */
class LoginController {

    /**
     * @param string $type
     * @return AuthHandlerInterface
     * @throws \Exception
     */
    private function getAuthHandlerByType($type) {
        $authHandler = null;
        switch (strtolower($type)) {
            case 'twitch':
                $authHandler = new TwitchAuthHandler();
                break;
            case 'twitter':
                $authHandler = new TwitterAuthHandler();
                break;
            case 'google':
                $authHandler = new GoogleAuthHandler();
                break;
            case 'reddit':
                $authHandler = new RedditAuthHandler();
                break;
            case 'discord':
                $authHandler = new DiscordAuthHandler();
                break;
            default:
                throw new Exception('No authentication handler found.');
        }
        return $authHandler;
    }

    /**
     * @Route ("/login")
     * @HttpMethod ({"GET"})
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     * @throws Exception
     * @throws DBALException
     */
    public function login(array $params, ViewModel $model) {
        Session::remove('isConnectingAccount');
        $grant = isset($params['grant']) ? $params['grant'] : null;
        $follow = (isset($params ['follow'])) ? $params ['follow'] : '';
        $uuid = (isset($params ['uuid'])) ? $params ['uuid'] : '';

        if (!empty($uuid)) {
            $oauthService = OAuthService::instance();
            $auth = $oauthService->getFlashStore($uuid, 'uuid');
            $app = $oauthService->getAuthClientByCode($auth['client_id']);
        } else {
            $app = [];
        }

        $model->title = 'Login';
        $model->follow = $follow;
        $model->grant = $grant;
        $model->uuid = $uuid;
        $model->app = $app;
        return 'login';
    }

    /**
     * @Route ("/logout")
     *
     * @return string
     */
    public function logout() {
        if (Session::isStarted()) {
            $redis = ChatRedisService::instance();
            $redis->removeChatSession(Session::getSessionId());
            Session::destroy();
        }
        return 'redirect: /';
    }

    /**
     * @Route ("/auth/twitch")
     * @Route ("/auth/twitter")
     * @Route ("/auth/google")
     * @Route ("/auth/reddit")
     * @Route ("/auth/discord")
     *
     * @param array $params
     * @param ViewModel $model
     * @param Request $request
     * @return string
     */
    public function authType(array $params, ViewModel $model, Request $request) {
        try {
            $type = substr($request->path(), strlen("/auth/"));
            return $this->getAuthHandlerByType($type)->authenticate($params);
        } catch (\Exception $e) {
            if (Session::hasRole(UserRole::USER)) {
                Session::setErrorBag($e->getMessage());
                return 'redirect: /profile/authentication';
            }
            $model->title = 'Login error';
            $model->error = $e;
            return 'error';
        }
    }

    /**
     * @Route ("/login")
     * @HttpMethod ({"POST"})
     *
     * @param array $params
     * @return string
     * @throws \Exception
     */
    public function loginPost(array $params) {
        try {
            if (!isset($params['authProvider']) || empty($params['authProvider'])) {
                throw new Exception('Please select a authentication provider');
            }
            Session::start(); // TODO Using the session for this kind of state is bad.
            Session::set('rememberme', (isset ($params ['rememberme']) && !empty ($params ['rememberme'])) ? 1 : 0);
            Session::set('follow', (isset ($params ['follow']) && !empty ($params ['follow'])) ? $params ['follow'] : null);
            Session::set('grant', (isset ($params ['grant']) && !empty ($params ['grant'])) ? $params ['grant'] : null);
            Session::set('uuid', (isset ($params ['uuid']) && !empty ($params ['uuid'])) ? $params ['uuid'] : null);
            $handler = $this->getAuthHandlerByType($params ['authProvider']);
            return 'redirect: ' . $handler->getAuthenticationUrl();
        } catch (Exception $e) {
            Session::setErrorBag($e->getMessage());
            return 'redirect: /login';
        }
    }
}
