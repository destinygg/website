<?php
namespace Destiny\Twitch;

use Destiny\Common\Exception;
use Destiny\Common\Authentication\AuthenticationRedirectionFilter;
use Destiny\Common\Authentication\AuthenticationCredentials;
use Destiny\Common\Config;
use Destiny\Common\ViewModel;

class TwitchAuthHandler{
  
    /**
     * The current auth type
     *
     * @var string
     */
    protected $authProvider = 'twitch';
    
    /**
     * Redirects the user to the auth provider
     *
     * @return void
     */
    public function getAuthenticationUrl() {
        $authConf = Config::$a ['oauth'] ['providers'] [$this->authProvider];
        $callback = sprintf ( Config::$a ['oauth'] ['callback'], $this->authProvider );
        $client = new \OAuth2\Client ( $authConf ['clientId'], $authConf ['clientSecret'] );
        $client->setAccessTokenType ( \OAuth2\Client::ACCESS_TOKEN_OAUTH );
        return $client->getAuthenticationUrl ( 'https://api.twitch.tv/kraken/oauth2/authorize', $callback, array (
            'scope' => 'user_read' 
        ) );
    }
    
    /**
     * @param array $params         
     * @throws Exception
     */
    public function authenticate(array $params, ViewModel $model) {
        if (! isset ( $params ['code'] ) || empty ( $params ['code'] )) {
           throw new Exception ( 'Authentication failed, invalid or empty code.' );
        }
        
        $oAuthConf = Config::$a ['oauth'] ['providers'] [$this->authProvider];
        $client = new \OAuth2\Client ( $oAuthConf ['clientId'], $oAuthConf ['clientSecret'] );
        $client->setAccessTokenType ( \OAuth2\Client::ACCESS_TOKEN_OAUTH );
        $response = $client->getAccessToken ( 'https://api.twitch.tv/kraken/oauth2/token', 'authorization_code', array (
          'redirect_uri' => sprintf ( Config::$a ['oauth'] ['callback'], $this->authProvider ),
          'code' => $params ['code']
        ) );
        
        if (empty ( $response ) || isset ( $response ['error'] ))
           throw new Exception ( 'Invalid access_token response' );
        
        if (! isset ( $response ['result'] ) || empty ( $response ['result'] ) || ! isset ( $response ['result'] ['access_token'] ))
            throw new Exception ( 'Failed request for access token' );
        
        $client->setAccessToken ( $response ['result'] ['access_token'] );
        $response = $client->fetch ( 'https://api.twitch.tv/kraken/user' );
        
        if (empty ( $response ['result'] ) || isset ( $response ['error'] ))
           throw new Exception ( 'Invalid user details response' );
        
        if (is_string ( $response ['result'] ))
           throw new Exception ( sprintf ( 'Invalid auth result %s', $response ['result'] ) );
        
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
        if (empty ( $data ) || ! isset ( $data ['_id'] ) || empty ( $data ['_id'] )) {
           throw new Exception ( 'Authorization failed, invalid user data' );
        }
        $arr = array ();
        $arr ['authProvider'] = $this->authProvider;
        $arr ['authCode'] = $code;
        $arr ['authId'] = $data ['_id'];
        $arr ['authDetail'] = $data ['name'];
        $arr ['username'] = (isset ( $data ['display_name'] ) && ! empty ( $data ['display_name'] )) ? $data ['display_name'] : $data ['name'];
        $arr ['email'] = (isset ( $data ['email'] ) && ! empty ( $data ['email'] )) ? $data ['email'] : '';
        return new AuthenticationCredentials ( $arr );
    }
}