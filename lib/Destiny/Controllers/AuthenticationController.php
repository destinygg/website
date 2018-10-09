<?php
namespace Destiny\Controllers;

use Destiny\Chat\ChatRedisService;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Route;
use Destiny\Common\AuthHandlerInterface;
use Destiny\Common\Request;
use Destiny\Common\Session;
use Destiny\Common\User\UserRole;
use Destiny\Common\ViewModel;
use Destiny\Discord\DiscordAuthHandler;
use Destiny\Google\GoogleAuthHandler;
use Destiny\Reddit\RedditAuthHandler;
use Destiny\Twitch\TwitchAuthHandler;
use Destiny\Twitter\TwitterAuthHandler;
use Exception;

/**
 * @Controller
 */
class AuthenticationController {

    /**
     * @param $type String
     * @return AuthHandlerInterface
     * @throws Exception
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
                throw new Exception('No handler found.');
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
     */
    public function login(array $params, ViewModel $model) {
        Session::remove('accountMerge');
        $model->title = 'Login';
        $model->follow = (isset ($params ['follow'])) ? $params ['follow'] : '';
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
            return $this->handleAuthError($e, $model);
        }
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
        try {
            return 'redirect: ' . $this->getAuthHandlerByType($authProvider)->getAuthenticationUrl();
        } catch (Exception $e) {
            $model->title = 'Login error';
            $model->rememberme = $rememberme;
            $model->error = new Exception ('Authentication type not supported');
            return 'error';
        }
    }

    /**
     * @param \Exception $e
     * @param ViewModel $model
     * @return string
     */
    private function handleAuthError(\Exception $e, ViewModel $model) {
        if (Session::hasRole(UserRole::USER)) {
            Session::setErrorBag($e->getMessage());
            return 'redirect: /profile/authentication';
        }
        $model->title = 'Login error';
        $model->error = $e;
        return 'error';
    }
}
