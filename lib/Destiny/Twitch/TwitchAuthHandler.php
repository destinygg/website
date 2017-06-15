<?php
namespace Destiny\Twitch;

use Destiny\Common\Exception;
use Destiny\Common\Authentication\AuthenticationRedirectionFilter;
use Destiny\Common\Authentication\AuthenticationCredentials;
use Destiny\Common\Config;
use Doctrine\DBAL\DBALException;
use OAuth2;

class TwitchAuthHandler{
  
    /**
     * @var string
     */
    protected $authProvider = 'twitch';
    
    /**
     * @return string
     */
    public function getAuthenticationUrl() {
        $authConf = Config::$a ['oauth'] ['providers'] [$this->authProvider];
        $callback = sprintf ( Config::$a ['oauth'] ['callback'], $this->authProvider );
        $client = new OAuth2\Client ( $authConf ['clientId'], $authConf ['clientSecret'] );
        $client->setAccessTokenType ( OAuth2\Client::ACCESS_TOKEN_OAUTH );
        return $client->getAuthenticationUrl ( 'https://api.twitch.tv/kraken/oauth2/authorize', $callback, array (
            'scope' => 'user_read' 
        ) );
    }

    /**
     * @param array $params
     * @return string
     * @throws Exception
     *
     * @throws DBALException
     * @throws OAuth2\Exception
     * @throws OAuth2\InvalidArgumentException
     */
    public function authenticate(array $params) {
        if (! isset ( $params ['code'] ) || empty ( $params ['code'] )) {
           throw new Exception ( 'Authentication failed, invalid or empty code.' );
        }
        
        $oAuthConf = Config::$a ['oauth'] ['providers'] [$this->authProvider];
        $client = new OAuth2\Client ( $oAuthConf ['clientId'], $oAuthConf ['clientSecret'] );
        $client->setAccessTokenType ( OAuth2\Client::ACCESS_TOKEN_OAUTH );
        $response = $client->getAccessToken ( 'https://api.twitch.tv/kraken/oauth2/token', 'authorization_code', array (
          'redirect_uri' => sprintf ( Config::$a ['oauth'] ['callback'], $this->authProvider ),
          'code' => $params ['code']
        ), ['Client-ID' => Config::$a['oauth']['providers']['twitch']['clientId']] );
        
        if (empty ( $response ) || isset ( $response ['error'] ))
           throw new Exception ( 'Invalid access_token response' );
        
        if (! isset ( $response ['result'] ) || empty ( $response ['result'] ) || ! isset ( $response ['result'] ['access_token'] ))
            throw new Exception ( 'Failed request for access token' );
        
        $client->setAccessToken ( $response ['result'] ['access_token'] );
        $response = $client->fetch ( 'https://api.twitch.tv/kraken/user', [], OAuth2\Client::HTTP_METHOD_GET, [
            'Client-ID' => Config::$a['oauth']['providers']['twitch']['clientId']
        ]);
        
        if (empty ( $response ['result'] ) || isset ( $response ['error'] ))
           throw new Exception ( 'Invalid user details response' );
        
        if (is_string ( $response ['result'] ))
           throw new Exception ( sprintf ( 'Invalid auth result %s', $response ['result'] ) );
        
        $authCreds = $this->getAuthCredentials ( $params ['code'], $response ['result'] );
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
        if (empty ( $data ) || ! isset ( $data ['_id'] ) || empty ( $data ['_id'] )) {
           throw new Exception ( 'Authorization failed, invalid user data' );
        }

        if(!isset($data['email']) || empty($data['email']) || !$data['email']){
            throw new Exception ( 'You must have a verified email address for your registration to complete successfully.' );
        }

        $arr = array ();
        $arr ['authProvider'] = $this->authProvider;
        $arr ['authCode'] = $code;
        $arr ['authId'] = $data ['_id'];
        $arr ['authDetail'] = $data ['name'];
        $arr ['username'] = (isset ( $data ['display_name'] ) && ! empty ( $data ['display_name'] )) ? $data ['display_name'] : $data ['name'];
        $arr ['email'] = $data ['email'];
        return new AuthenticationCredentials ( $arr );
    }
}