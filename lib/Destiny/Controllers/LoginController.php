<?php
namespace Destiny\Controllers;

use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Authentication\AuthenticationHandler;
use Destiny\Common\Authentication\AuthenticationRedirectionFilter;
use Destiny\Common\Authentication\AuthenticationService;
use Destiny\Common\Authentication\AuthProvider;
use Destiny\Common\Authentication\DggOAuthService;
use Destiny\Common\Exception;
use Destiny\Common\Log;
use Destiny\Common\Request;
use Destiny\Common\Session\Session;
use Destiny\Common\User\UserRole;
use Destiny\Common\Utils\FilterParams;
use Destiny\Common\ViewModel;
use Destiny\Discord\DiscordAuthHandler;
use Destiny\Google\GoogleAuthHandler;
use Destiny\Reddit\RedditAuthHandler;
use Destiny\StreamElements\StreamElementsAuthHandler;
use Destiny\StreamLabs\StreamLabsAuthHandler;
use Destiny\Twitch\TwitchAuthHandler;
use Destiny\Twitter\TwitterAuthHandler;

/**
 * @Controller
 */
class LoginController {

    /**
     * @Route ("/login")
     * @HttpMethod ({"GET"})
     */
    public function login(array $params, ViewModel $model): string {
        if (Session::hasRole(UserRole::USER)) {
            Session::setErrorBag('You are already signed in');
            return 'redirect: /profile';
        }
        Session::remove('isConnectingAccount');
        $grant = isset($params['grant']) ? $params['grant'] : null;
        $follow = (isset($params ['follow'])) ? $params ['follow'] : '';
        $uuid = (isset($params ['uuid'])) ? $params ['uuid'] : '';

        if (!empty($uuid)) {
            try {
                $oauthService = DggOAuthService::instance();
                $auth = $oauthService->getFlashStore($uuid, 'uuid');
                $app = $oauthService->getAuthClientByCode($auth['client_id']);
            } catch (Exception $e) {
                Session::setErrorBag($e->getMessage());
                return 'redirect: /profile';
            }
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
     */
    public function logout(): string {
        AuthenticationService::instance()->removeWebSession();
        return 'redirect: /';
    }

    /**
     * @Route ("/auth/twitch")
     * @Route ("/auth/twitter")
     * @Route ("/auth/google")
     * @Route ("/auth/reddit")
     * @Route ("/auth/discord")
     */
    public function authByType(array $params, Request $request): string {
        try {
            $type = substr($request->path(), strlen("/auth/"));
            $authService = AuthenticationService::instance();
            $authHandler = $authService->getLoginAuthHandlerByType($type);
            $response = $authHandler->exchangeCode($params);
            $redirectFilter = new AuthenticationRedirectionFilter($response);
            return $redirectFilter->execute();
        } catch (Exception $e) {
            Session::setErrorBag($e->getMessage());
            Log::warn($e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
        return 'redirect: /login';
    }

    /**
     * @Route ("/login")
     * @HttpMethod ({"POST"})
     */
    public function loginPost(array $params): string {
        try {
            FilterParams::required($params, 'authProvider');
            $authService = AuthenticationService::instance();
            Session::start();
            Session::set('rememberme', (isset ($params ['rememberme']) && !empty ($params ['rememberme'])) ? 1 : 0);
            Session::set('follow', (isset ($params ['follow']) && !empty ($params ['follow'])) ? $params ['follow'] : null);
            Session::set('grant', (isset ($params ['grant']) && !empty ($params ['grant'])) ? $params ['grant'] : null);
            Session::set('uuid', (isset ($params ['uuid']) && !empty ($params ['uuid'])) ? $params ['uuid'] : null);
            $handler = $authService->getLoginAuthHandlerByType($params ['authProvider']);
            return 'redirect: ' . $handler->getAuthorizationUrl();
        } catch (Exception $e) {
            Session::setErrorBag($e->getMessage());
            return 'redirect: /login';
        }
    }
}
