<?php
namespace Destiny\Reddit;

use Destiny\Common\Exception;
use Destiny\Common\Authentication\AuthenticationRedirectionFilter;
use Destiny\Common\Authentication\AuthenticationCredentials;
use Destiny\Common\Config;

class RedditAuthHandler {
    
    /**
     * The current auth type
     *
     * @var string
     */
    protected $authProvider = 'reddit';
    
    /**
     * Redirects the user to the auth provider
     *
     * @return void
     */
    public function getAuthenticationUrl() {
        $authConf = Config::$a ['oauth'] ['providers'] [$this->authProvider];
        $callback = sprintf ( Config::$a ['oauth'] ['callback'], $this->authProvider );
        $client = new \OAuth2\Client ( $authConf ['clientId'], $authConf ['clientSecret'], \OAuth2\Client::AUTH_TYPE_AUTHORIZATION_BASIC );
        return $client->getAuthenticationUrl ( 'https://ssl.reddit.com/api/v1/authorize', $callback, array (
                'scope' => 'identity',
                'state' => md5 ( time () . 'eFdcSA_' ) 
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
        
        $oAuthConf = Config::$a ['oauth'] ['providers'] [$this->authProvider];
        $client = new \OAuth2\Client ( $oAuthConf ['clientId'], $oAuthConf ['clientSecret'], \OAuth2\Client::AUTH_TYPE_AUTHORIZATION_BASIC );
        $client->setAccessTokenType ( \OAuth2\Client::ACCESS_TOKEN_BEARER );
        $response = $client->getAccessToken ( 'https://ssl.reddit.com/api/v1/access_token', 'authorization_code', array (
                'redirect_uri' => sprintf ( Config::$a ['oauth'] ['callback'], $this->authProvider ),
                'code' => $params ['code'] 
        ) );
        
        if (empty ( $response ) || isset ( $response ['error'] ))
            throw new Exception ( 'Invalid access_token response' );
        
        if (! isset ( $response ['result'] ) || empty ( $response ['result'] ) || ! isset ( $response ['result'] ['access_token'] ))
            throw new Exception ( 'Failed request for access token' );
        
        $client->setAccessToken ( $response ['result'] ['access_token'] );
        $response = $client->fetch ( "https://oauth.reddit.com/api/v1/me.json" );
        
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
        $arr ['authDetail'] = $data ['name'];
        $arr ['username'] = $data ['name'];
        $arr ['email'] = '';
        return new AuthenticationCredentials ( $arr );
    }
}