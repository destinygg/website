<?php
namespace Destiny\Reddit;

use Destiny\Common\Exception;
use Destiny\Common\Authentication\AuthenticationRedirectionFilter;
use Destiny\Common\Authentication\AuthenticationCredentials;
use Destiny\Common\Config;
use OAuth2\Client;

class RedditAuthHandler {
    
    /**
     * @var string
     */
    protected $authProvider = 'reddit';
    
    /**
     * @return string
     */
    public function getAuthenticationUrl() {
        $authConf = Config::$a ['oauth'] ['providers'] [$this->authProvider];
        $callback = sprintf ( Config::$a ['oauth'] ['callback'], $this->authProvider );
        $client = new Client ( $authConf ['clientId'], $authConf ['clientSecret'], Client::AUTH_TYPE_AUTHORIZATION_BASIC );
        return $client->getAuthenticationUrl ( 'https://ssl.reddit.com/api/v1/authorize', $callback, array (
            'scope' => 'identity',
            'state' => md5 ( time () . 'eFdcSA_' ) 
        ) );
    }

    /**
     * @param array $params
     * @return string
     * @throws Exception
     */
    public function authenticate(array $params) {
        if (! isset ( $params ['code'] ) || empty ( $params ['code'] )) {
            throw new Exception ( 'Authentication failed, invalid or empty code.' );
        }
        
        $oAuthConf = Config::$a ['oauth'] ['providers'] [$this->authProvider];
        $client = new Client ( $oAuthConf ['clientId'], $oAuthConf ['clientSecret'], Client::AUTH_TYPE_AUTHORIZATION_BASIC );
        $client->setAccessTokenType ( Client::ACCESS_TOKEN_BEARER );
        $response = $client->getAccessToken ( 'https://ssl.reddit.com/api/v1/access_token', 'authorization_code', array (
            'redirect_uri' => sprintf ( Config::$a ['oauth'] ['callback'], $this->authProvider ),
            'code' => $params ['code'] 
        ) );
        
        if (empty ( $response ) || isset ( $response ['error'] ))
            throw new Exception ( 'Invalid access_token response' );
        
        if (! isset ( $response ['result'] ) || empty ( $response ['result'] ) || ! isset ( $response ['result'] ['access_token'] ))
            throw new Exception ( 'Failed request for access token' );

        $client->setAccessToken ( $response ['result'] ['access_token'] );

        // Reddit requires a User-Agent
        $info = $client->fetch ( "https://oauth.reddit.com/api/v1/me.json", array(), 'GET', array(
            'User-Agent' => 'destiny.gg/'.Config::version ()
        ));

        if (empty ( $info ['result'] ) || !is_array ( $info ['result'] ) || isset ( $info ['error'] ))
            throw new Exception ( 'Invalid user details response' );
        
        $authCreds = $this->getAuthCredentials ( $params ['code'], $info ['result'] );
        $authCredHandler = new AuthenticationRedirectionFilter ();
        return $authCredHandler->execute ( $authCreds );
    }

    /**
     * @param string $code
     * @param array $data
     * @return AuthenticationCredentials
     * @throws Exception
     */
    private function getAuthCredentials($code, array $data) {
        if (empty ( $data ) || ! isset ( $data ['id'] ) || empty ( $data ['id'] )) {
            throw new Exception ( 'Authorization failed, invalid user data' );
        }

        if(!isset($data['has_verified_email']) || empty($data['has_verified_email']) || $data['has_verified_email'] != 1){
            throw new Exception ( 'You must have a verified email address for your registration to complete successfully.' );
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