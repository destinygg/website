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
use Destiny\Common\ViewModel;
use Destiny\Discord\DiscordAuthHandler;
use Destiny\Google\GoogleAuthHandler;
use Destiny\Reddit\RedditAuthHandler;
use Destiny\StreamElements\StreamElementsAuthHandler;
use Destiny\StreamLabs\StreamLabsAuthHandler;
use Destiny\Twitch\TwitchAuthHandler;
use Destiny\Twitter\TwitterAuthHandler;
use Doctrine\DBAL\DBALException;

/**
 * @Controller
 */
class LoginController {

    /**
     * @throws Exception
     */
    private function getAuthHandlerByType(string $type): AuthenticationHandler {
        $authHandler = null;
        switch (strtolower($type)) {
            case AuthProvider::TWITCH:
                $authHandler = new TwitchAuthHandler();
                break;
            case AuthProvider::TWITTER:
                $authHandler = new TwitterAuthHandler();
                break;
            case AuthProvider::GOOGLE:
                $authHandler = new GoogleAuthHandler();
                break;
            case AuthProvider::REDDIT:
                $authHandler = new RedditAuthHandler();
                break;
            case AuthProvider::DISCORD:
                $authHandler = new DiscordAuthHandler();
                break;
            case AuthProvider::STREAMELEMENTS:
                $authHandler = new StreamElementsAuthHandler();
                break;
            case AuthProvider::STREAMLABS:
                $authHandler = new StreamLabsAuthHandler();
                break;
            default:
                throw new Exception('No authentication handler found.');
        }
        return $authHandler;
    }

    /**
     * @Route ("/login")
     * @HttpMethod ({"GET"})
     * @throws Exception
     * @throws DBALException
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
            $oauthService = DggOAuthService::instance();
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
            $authHandler = $this->getAuthHandlerByType($type);
            $response = $authHandler->exchangeCode($params);
            $redirectFilter = new AuthenticationRedirectionFilter($response);
            return $redirectFilter->execute();
        } catch (\Exception $e) {
            Session::setErrorBag($e->getMessage());
            Log::warn($e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return 'redirect: /login';
        }
    }

    /**
     * @Route ("/login")
     * @HttpMethod ({"POST"})
     */
    public function loginPost(array $params): string {
        try {
            if (!isset($params['authProvider']) || empty($params['authProvider'])) {
                throw new Exception('Please select a authentication provider');
            }
            Session::start();
            Session::set('rememberme', (isset ($params ['rememberme']) && !empty ($params ['rememberme'])) ? 1 : 0);
            Session::set('follow', (isset ($params ['follow']) && !empty ($params ['follow'])) ? $params ['follow'] : null);
            Session::set('grant', (isset ($params ['grant']) && !empty ($params ['grant'])) ? $params ['grant'] : null);
            Session::set('uuid', (isset ($params ['uuid']) && !empty ($params ['uuid'])) ? $params ['uuid'] : null);
            $handler = $this->getAuthHandlerByType($params ['authProvider']);
            return 'redirect: ' . $handler->getAuthorizationUrl();
        } catch (Exception $e) {
            Session::setErrorBag($e->getMessage());
            return 'redirect: /login';
        }
    }
}
