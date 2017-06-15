<?php
namespace Destiny\Twitter;

use Destiny\Common\Exception;
use Destiny\Common\Authentication\AuthenticationRedirectionFilter;
use Destiny\Common\Authentication\AuthenticationCredentials;
use Destiny\Common\Config;
use Destiny\Common\Session;
use Doctrine\DBAL\DBALException;

class TwitterAuthHandler {
    
    /**
     * @var string
     */
    protected $authProvider = 'twitter';

    /**
     * @return string
     * @throws Exception
     */
    public function getAuthenticationUrl() {
        $authConf = Config::$a ['oauth'] ['providers'] [$this->authProvider];
        $callback = sprintf ( Config::$a ['oauth'] ['callback'], $this->authProvider );
        $tmhOAuth = new \tmhOAuth ( array (
                'consumer_key' => $authConf ['clientId'],
                'consumer_secret' => $authConf ['clientSecret'],
                'curl_connecttimeout' => 10,
                'curl_timeout' => 15,
                'curl_ssl_verifypeer' => false
        ) );
        $code = $tmhOAuth->apponly_request ( array (
                'without_bearer' => true,
                'method' => 'POST',
                'url' => $tmhOAuth->url ( 'oauth/request_token', '' ),
                'params' => array (
                        'oauth_callback' => $callback 
                ) 
        ) );
        if ($code != 200) {
            throw new Exception ( 'There was an error communicating with Twitter.' );
        }
        $response = $tmhOAuth->extract_params ( $tmhOAuth->response ['response'] );
        if ($response ['oauth_callback_confirmed'] !== 'true') {
            throw new Exception ( 'The callback was not confirmed by Twitter so we cannot continue.' );
        }
        Session::set ( 'oauth', $response );
        return $tmhOAuth->url ( 'oauth/authorize', '' ) . "?oauth_token={$response['oauth_token']}";
    }

    /**
     * @param array $params
     * @return string
     * @throws Exception
     *
     * @throws DBALException
     */
    public function authenticate(array $params) {
        if ((! isset ( $params ['oauth_token'] ) || empty ( $params ['oauth_token'] )) || ! isset ( $params ['oauth_verifier'] ) || empty ( $params ['oauth_verifier'] )) {
            throw new Exception ( 'Authentication failed' );
        }
        $oauth = Session::set ( 'oauth' );
        if ($params ['oauth_token'] !== $oauth ['oauth_token']) {
            throw new Exception ( 'Invalid login session' );
        }
        $twitterOAuthConf = Config::$a ['oauth'] ['providers'] ['twitter'];
        $tmhOAuth = new \tmhOAuth ( array (
                'consumer_key' => $twitterOAuthConf ['clientId'],
                'consumer_secret' => $twitterOAuthConf ['clientSecret'],
                'token' => $oauth ['oauth_token'],
                'secret' => $oauth ['oauth_token_secret'],
                'curl_connecttimeout' => 10,
                'curl_timeout' => 30,
                'curl_ssl_verifypeer' => false
        ) );
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $code = $tmhOAuth->user_request ( array (
                'method' => 'POST',
                'url' => $tmhOAuth->url ( 'oauth/access_token', '' ),
                'params' => array (
                        'oauth_verifier' => trim ( $params ['oauth_verifier'] ) 
                ) 
        ) );
        if ($code != 200) {
            throw new Exception ( 'Failed to retrieve user data' );
        }
        $data = $tmhOAuth->extract_params ( $tmhOAuth->response ['response'] );
        $authCreds = $this->getAuthCredentials ( $oauth ['oauth_token'], $data );
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
        if (empty ( $data ) || ! isset ( $data ['user_id'] ) || empty ( $data ['user_id'] )) {
            throw new Exception ( 'Authorization failed, invalid user data' );
        }
        $arr = array ();
        $arr ['authProvider'] = $this->authProvider;
        $arr ['authCode'] = $code;
        $arr ['authId'] = $data ['user_id'];
        $arr ['authDetail'] = $data ['screen_name'];
        $arr ['username'] = $data ['screen_name'];
        $arr ['email'] = '';
        return new AuthenticationCredentials ( $arr );
    }
}