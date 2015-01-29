<?php

namespace Destiny\Controllers;

use Destiny\Common\Exception;
use Destiny\Common\Session;
use Destiny\Common\ViewModel;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\User\UserService;
use Destiny\Common\Authentication\AuthenticationService;
use Destiny\Twitch\TwitchAuthHandler;
use Destiny\Google\GoogleAuthHandler;
use Destiny\Twitter\TwitterAuthHandler;
use Destiny\Reddit\RedditAuthHandler;

/**
 * @Controller
 */
class LoginController {
    
    /**
     * @Route ("/logout")
     *
     * @param array $params         
     */
    public function logout(array $params) {
        AuthenticationService::instance ()->logout ();
        return 'redirect: /';
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
        Session::set ( 'accountMerge' );
        $model->title = 'Login';
        $model->follow = (isset ( $params ['follow'] )) ? $params ['follow'] : '';
        return 'login';
    }
    
    /**
     * @Route ("/login")
     * @HttpMethod ({"POST"})
     *
     * @param array $params         
     * @param ViewModel $model          
     * @return string
     */
    public function loginPost(array $params, ViewModel $model) {
        $userService = UserService::instance ();
        
        $authProvider = (isset ( $params ['authProvider'] ) && ! empty ( $params['authProvider'] )) ? $params ['authProvider'] : '';
        $rememberme = (isset ( $params ['rememberme'] ) && ! empty ( $params ['rememberme'] )) ? true : false;
        
        if (empty ( $authProvider )) {
            $model->title = 'Login error';
            $model->rememberme = $rememberme;
            $model->error = new Exception ( 'Please select a authentication provider' );
            return 'login';
        }
        
        Session::start ( Session::START_NOCOOKIE );
        if ($rememberme) {
            Session::set ( 'rememberme', 1 );
        }
        
        if (isset ( $params ['follow'] ) && ! empty ( $params ['follow'] )) {
            Session::set ( 'follow', $params ['follow'] );
        }
        
        switch (strtoupper ( $authProvider )) {
            case 'TWITCH' :
                $authHandler = new TwitchAuthHandler ();
                return 'redirect: ' . $authHandler->getAuthenticationUrl ();
            
            case 'GOOGLE' :
                $authHandler = new GoogleAuthHandler ();
                return 'redirect: ' . $authHandler->getAuthenticationUrl ();
            
            case 'TWITTER' :
                $authHandler = new TwitterAuthHandler ();
                return 'redirect: ' . $authHandler->getAuthenticationUrl ();
            
            /*
            case 'REDDIT' :
                $authHandler = new RedditAuthHandler ();
                return 'redirect: ' . $authHandler->getAuthenticationUrl ();
            */

            default :
                $model->title = 'Login error';
                $model->rememberme = $rememberme;
                $model->error = new Exception ( 'Authentication type not supported' );
                return 'login';
        }
    }
}
