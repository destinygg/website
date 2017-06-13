<?php
namespace Destiny\Controllers;

use Destiny\Common\Application;
use Destiny\Common\Log;
use Destiny\Common\Utils\Country;
use Destiny\Common\ViewModel;
use Destiny\Common\Session;
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

/**
 * @Controller
 */
class RegistrationController {

    /**
     * Make sure we have a valid session
     *
     * @param array $params
     * @throws Exception
     * @return AuthenticationCredentials
     */
    private function getSessionAuthenticationCredentials(array $params) {
        if (! isset ( $params ['code'] ) || empty ( $params ['code'] )) {
            throw new Exception ( 'Invalid code' );
        }
        $authSession = Session::get ( 'authSession' );
        if ($authSession instanceof AuthenticationCredentials) {
            if (empty ( $authSession ) || ($authSession->getAuthCode () != $params ['code'])) {
                throw new Exception ( 'Invalid authentication code' );
            }
            if (! $authSession->isValid ()) {
                throw new Exception ( 'Invalid authentication information' );
            }
        } else {
            throw new Exception ( 'Could not retrieve session data. Possibly due to cookies not being enabled.' );
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
        $authCreds = $this->getSessionAuthenticationCredentials ( $params );
        $email = $authCreds->getEmail ();
        $username = $authCreds->getUsername ();
        if (! empty ( $username ) && empty ( $email )) {
            $email = $username . '@destiny.gg';
        }
        $model->title = 'Register';
        $model->username = $username;
        $model->email = $email;
        $model->follow = (isset($params['follow'])) ? $params['follow']:'';
        $model->authProvider = $authCreds->getAuthProvider ();
        $model->code = $authCreds->getAuthCode ();
        $model->rememberme = Session::get ( 'rememberme' );
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
     * @throws \Exception
     */
    public function registerProcess(array $params, ViewModel $model, Request $request) {
        $userService = UserService::instance ();
        $authService = AuthenticationService::instance ();
        $authCreds = $this->getSessionAuthenticationCredentials ( $params );
        
        $username = (isset ( $params ['username'] ) && ! empty ( $params ['username'] )) ? $params ['username'] : '';
        $email = (isset ( $params ['email'] ) && ! empty ( $params ['email'] )) ? $params ['email'] : '';
        $country = (isset ( $params ['country'] ) && ! empty ( $params ['country'] )) ? $params ['country'] : '';
        $rememberme = (isset ( $params ['rememberme'] ) && ! empty ( $params ['rememberme'] )) ? true : false;
        
        $authCreds->setUsername ( $username );
        $authCreds->setEmail ( $email );

        if ($rememberme)
            Session::set ( 'rememberme', 1 );

        try {
            if (!isset($params['g-recaptcha-response']) || empty($params['g-recaptcha-response']))
                throw new Exception ('You must solve the recaptcha.');
            $googleRecaptchaHandler = new GoogleRecaptchaHandler();
            $googleRecaptchaHandler->resolve($params['g-recaptcha-response'], $request);
            $authService->validateUsername($username);
            $authService->validateEmail($email);
            if (!empty ($country)) {
                $countryArr = Country::getCountryByCode($country);
                if (empty ($countryArr)) {
                    throw new Exception ('Invalid country');
                }
                $country = $countryArr ['alpha-2'];
            }
        } catch ( Exception $e ) {
            $model->title = 'Register Error';
            $model->username = $username;
            $model->email = $email;
            $model->follow = (isset($params['follow'])) ? $params['follow']:'';
            $model->authProvider = $authCreds->getAuthProvider ();
            $model->code = $authCreds->getAuthCode ();
            $model->error = $e;
            return 'register';
        }

        $conn = Application::instance()->getConnection();
        $conn->beginTransaction();

        try {
            $user = array ();
            $user ['username'] = $username;
            $user ['email'] = $email;
            $user ['userStatus'] = 'Active';
            $user ['country'] = $country;
            $user ['userId'] = $userService->addUser ( $user );
            $userService->addUserAuthProfile([
                'userId' => $user ['userId'],
                'authProvider' => $authCreds->getAuthProvider(),
                'authId' => $authCreds->getAuthId(),
                'authCode' => $authCreds->getAuthCode(),
                'authDetail' => $authCreds->getAuthDetail(),
                'refreshToken' => $authCreds->getRefreshToken()
            ]);
            $conn->commit();
            Session::set ( 'authSession' );
        } catch ( \Exception $e ) {
            Log::critical("Error registering user");
            $conn->rollBack();
            throw $e;
        }

        $authCredHandler = new AuthenticationRedirectionFilter ();
        return $authCredHandler->execute ( $authCreds );

    }

}