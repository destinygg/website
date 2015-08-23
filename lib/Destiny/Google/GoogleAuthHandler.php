<?php

namespace Destiny\Google;

use Destiny\Common\Exception;
use Destiny\Common\Authentication\AuthenticationRedirectionFilter;
use Destiny\Common\Authentication\AuthenticationCredentials;
use Destiny\Common\Config;
use Destiny\Common\Session;
use OAuth2\Client;

class GoogleAuthHandler {
    
    /**
     * The current auth type
     *
     * @var string
     */
    protected $authProvider = 'google';
    
    /**
     * Redirects the user to the auth provider
     *
     * @return void
     */
    public function getAuthenticationUrl() {
        $authConf = Config::$a ['oauth'] ['providers'] [$this->authProvider];
        $callback = sprintf ( Config::$a ['oauth'] ['callback'], $this->authProvider );
        $client = new Client ( $authConf ['clientId'], $authConf ['clientSecret'] );
        $client->setAccessTokenType ( Client::ACCESS_TOKEN_BEARER );
        return $client->getAuthenticationUrl ( 'https://accounts.google.com/o/oauth2/auth', $callback, array (
                'scope' => 'openid email',
                'state' => 'security_token=' . Session::getSessionId () 
        ) );
    }
    
    /**
     * @param array $params         
     * @throws Exception
     */
    public function authenticate(array $params) {
        if (! isset ( $params ['code'] ) || empty ( $params ['code'] )) {
            throw new Exception ( 'Authentication failed, invalid or empty code.' );
        }
        
        $authConf = Config::$a ['oauth'] ['providers'] [$this->authProvider];
        $callback = sprintf ( Config::$a ['oauth'] ['callback'], $this->authProvider );
        $client = new Client ( $authConf ['clientId'], $authConf ['clientSecret'] );
        $response = $client->getAccessToken ( 'https://accounts.google.com/o/oauth2/token', 'authorization_code', array (
                'redirect_uri' => $callback,
                'code' => $params ['code'] 
        ) );
        
        if (empty ( $response ) || isset ( $response ['error'] ))
            throw new Exception ( 'Invalid access_token response' );
        
        if (! isset ( $response ['result'] ) || empty ( $response ['result'] ) || ! isset ( $response ['result'] ['access_token'] ))
            throw new Exception ( 'Failed request for access token' );
        
        $client->setAccessToken ( $response ['result'] ['access_token'] );
        $response = $client->fetch ( 'https://www.googleapis.com/oauth2/v2/userinfo' );
        
        if (empty ( $response ['result'] ) || isset ( $response ['error'] ))
            throw new Exception ( 'Invalid user details response' );
        
        $authCreds = $this->getAuthCredentials ( $params ['code'], $response ['result'] );
        $authCredHandler = new AuthenticationRedirectionFilter ();
        return $authCredHandler->execute ( $authCreds );
    }
    
    /**
     * Build a standard auth array from custom data array from api response
     *
     * @param string $code          
     * @param array $data           
     * @return AuthenticationCredentials
     */
    private function getAuthCredentials($code, array $data) {
        if (empty ( $data ) || ! isset ( $data ['id'] ) || empty ( $data ['id'] )) {
            throw new Exception ( 'Authorization failed, invalid user data' );
        }
        $arr = array ();
        $arr ['authProvider'] = $this->authProvider;
        $arr ['authCode'] = $code;
        $arr ['authId'] = $data ['id'];
        $arr ['authDetail'] = $data ['email'];
        $arr ['username'] = (isset ( $data ['hd'] )) ? $data ['hd'] : '';
        $arr ['email'] = $data ['email'];
        return new AuthenticationCredentials ( $arr );
    }
}