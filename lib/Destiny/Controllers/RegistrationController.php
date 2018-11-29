<?php
namespace Destiny\Controllers;

use Destiny\Common\Application;
use Destiny\Common\Log;
use Destiny\Common\ViewModel;
use Destiny\Common\Session\Session;
use Destiny\Common\Exception;
use Destiny\Common\Request;
use Destiny\Common\Authentication\AuthenticationCredentials;
use Destiny\Common\Authentication\AuthenticationRedirectionFilter;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Authentication\AuthenticationService;
use Destiny\Common\User\UserService;
use Destiny\Google\GoogleRecaptchaHandler;
use Doctrine\DBAL\DBALException;

/**
 * @Controller
 */
class RegistrationController {

    /**
     * Make sure we have a valid session
     * TODO clean this up
     *
     * @param array $params
     * @throws Exception
     * @return AuthenticationCredentials
     */
    private function getSessionAuthenticationCredentials(array $params) {
        if (!isset ($params ['code']) || empty ($params ['code'])) {
            throw new Exception ('Invalid code');
        }
        $authSession = Session::get('authSession');
        if (!empty($authSession) && $authSession instanceof AuthenticationCredentials) {
            if (empty ($authSession) || ($authSession->getAuthCode() != $params ['code'])) {
                throw new Exception ('Invalid authentication code');
            }
            if (!$authSession->isValid()) {
                throw new Exception ('Invalid authentication information');
            }
        } else {
            throw new Exception ('Could not retrieve session data. Possibly due to cookies not being enabled.');
        }
        return $authSession;
    }

    /**
     * @Route ("/register")
     * @HttpMethod ({"GET"})
     *
     * Handle the confirmation request
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     * @throws Exception
     */
    public function register(array $params, ViewModel $model) {
        $authCreds = $this->getSessionAuthenticationCredentials($params);
        $username = $authCreds->getUsername();
        $model->title = 'Register';
        $model->username = $username;
        $model->rememberme = (isset($params['rememberme'])) ? $params['rememberme'] : 0;
        $model->follow = (isset($params['follow'])) ? $params['follow'] : '';
        $model->grant = (isset($params['grant'])) ? $params['grant'] : '';
        $model->uuid = (isset($params['uuid'])) ? $params['uuid'] : '';
        $model->authProvider = $authCreds->getAuthProvider();
        $model->code = $authCreds->getAuthCode();
        return 'register';
    }

    /**
     * @Route ("/register")
     * @HttpMethod ({"POST"})
     *
     * @param array $params
     * @param ViewModel $model
     * @param Request $request
     * @return string
     *
     * @throws DBALException
     * @throws Exception
     */
    public function registerProcess(array $params, ViewModel $model, Request $request) {
        $userService = UserService::instance();
        $authService = AuthenticationService::instance();
        $authCreds = $this->getSessionAuthenticationCredentials($params);
        $username = (isset ($params ['username']) && !empty ($params ['username'])) ? $params ['username'] : '';

        try {
            $googleRecaptchaHandler = new GoogleRecaptchaHandler();
            $googleRecaptchaHandler->resolveWithRequest($request);
            $authService->validateUsername($username);
            $userService->checkUsernameTaken($username, -1);
            $authCreds->setUsername($username);
        } catch (Exception $e) {
            $model->title = 'Register Error';
            $model->username = $username;
            $model->follow = (isset($params['follow'])) ? $params['follow'] : '';
            $model->grant = (isset($params['grant'])) ? $params['grant'] : '';
            $model->uuid = (isset($params['uuid'])) ? $params['uuid'] : '';
            $model->authProvider = $authCreds->getAuthProvider();
            $model->code = $authCreds->getAuthCode();
            $model->error = $e;
            return 'register';
        }

        $conn = Application::getDbConn();
        try {
            $conn->beginTransaction();
            $userId = $userService->addUser([
                'username' => $username,
                'userStatus' => 'Active'
            ]);
            $userService->addUserAuthProfile([
                'userId' => $userId,
                'authProvider' => $authCreds->getAuthProvider(),
                'authId' => $authCreds->getAuthId(),
                'authCode' => $authCreds->getAuthCode(),
                'authDetail' => $authCreds->getAuthDetail(),
                'refreshToken' => $authCreds->getRefreshToken()
            ]);
            $conn->commit();
            Session::remove('authSession');
        } catch (DBALException $e) {
            $n = new Exception("Registration failed.", $e);
            Log::critical($n);
            $conn->rollBack();
            throw $n;
        }

        if (isset ($params ['rememberme']) && !empty ($params ['rememberme'])) {
            Session::set('rememberme', $params ['rememberme']);
        }
        if (isset ($params ['follow']) && !empty ($params ['follow'])) {
            Session::set('follow', $params ['follow']);
        }
        if (isset ($params ['grant']) && !empty ($params ['grant'])) {
            Session::set('grant', $params ['grant']);
        }
        if (isset ($params ['uuid']) && !empty ($params ['uuid'])) {
            Session::set('uuid', $params ['uuid']);
        }

        $authHandler = new AuthenticationRedirectionFilter($authCreds);
        return $authHandler->execute();
    }

}