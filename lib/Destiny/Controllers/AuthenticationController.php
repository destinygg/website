<?php
namespace Destiny\Controllers;

use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Transactional;
use Destiny\Common\Session;
use Destiny\Common\User\UserRole;
use Destiny\Google\GoogleAuthHandler;
use Destiny\Common\ViewModel;
use Destiny\Twitter\TwitterAuthHandler;
use Destiny\Twitch\TwitchAuthHandler;
use Destiny\Api\ApiAuthHandler;
use Destiny\Common\Response;
use Destiny\Common\Utils\Http;
use Destiny\Reddit\RedditAuthHandler;
use Destiny\Common\Exception;
use Destiny\Common\Authentication\AuthenticationService;
use Destiny\Commerce\SubscriptionsService;
use Destiny\Common\Config;
use Destiny\Common\User\UserService;
use Destiny\Api\ApiAuthenticationService;
use Destiny\Common\MimeType;

/**
 * @Controller
 */
class AuthenticationController {

    protected function checkPrivateKey($params){
        if (empty ( $params['privatekey'] ) )
            return false;

        return (Config::$a['privateKeys']['minecraft'] === $params['privatekey']);
    }

    /**
     * @Route ("/auth/minecraft")
     * @HttpMethod ({"GET"})
     *
     * @param array $params
     * @return Response
     * @throws Exception
     */
    public function authMinecraftGET(array $params) {
        if(! $this->checkPrivateKey($params))
            return new Response ( Http::STATUS_FORBIDDEN, 'privateKey' );

        if (empty ( $params ['uuid'] ) || strlen ( $params ['uuid'] ) > 36 )
            return new Response ( Http::STATUS_FORBIDDEN, 'UUID' );

        if ( !preg_match('/^[a-f0-9-]{32,36}$/', $params ['uuid'] ) )
            return new Response ( Http::STATUS_FORBIDDEN, 'UUID' );

        $userId = UserService::instance ()->getUserIdFromMinecraftUUID ( $params ['uuid'] );
        if ( !$userId )
            return new Response ( Http::STATUS_FORBIDDEN, 'notfound' );

        $sub = SubscriptionsService::getUserActiveSubscription( $userId );
        if (empty ( $sub ))
            return new Response ( Http::STATUS_FORBIDDEN, 'subscriptionNotFound' );

        $response = array(
            'end'  => strtotime( $sub['endDate'] ) * 1000,
        );

        $response = new Response ( Http::STATUS_OK, json_encode ( $response ) );
        $response->addHeader ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
        return $response;
    }

    /**
     * @Route ("/auth/minecraft")
     * @HttpMethod ({"POST"})
     *
     * @param array $params
     * @return Response
     * @throws Exception
     */
    public function authMinecraftPOST(array $params) {
        if(! $this->checkPrivateKey($params))
            return new Response ( Http::STATUS_FORBIDDEN, 'privateKey' );

        if (empty ( $params ['uuid'] ) || strlen ( $params ['uuid'] ) > 36 )
            return new Response ( Http::STATUS_FORBIDDEN, 'UUID' );

        if ( !preg_match('/^[a-f0-9-]{32,36}$/', $params ['uuid'] ) )
            return new Response ( Http::STATUS_FORBIDDEN, 'UUID' );

        if (empty ( $params ['authtoken'] ) || strlen ( $params ['authtoken'] ) > 32 )
            return new Response ( Http::STATUS_FORBIDDEN, 'authToken' );

        if ( !preg_match('/^[a-f0-9]{32}$/', $params ['authtoken'] ) )
            return new Response ( Http::STATUS_FORBIDDEN, 'authToken' );

        $authToken = ApiAuthenticationService::instance ()->getAuthToken ( $params ['authtoken'] );
        if (empty ( $authToken ))
            return new Response ( Http::STATUS_FORBIDDEN, 'authTokenNotFound' );

        $sub = SubscriptionsService::getUserActiveSubscription( $authToken['userId'] );
        if (empty ( $sub ))
            return new Response ( Http::STATUS_FORBIDDEN, 'subscriptionNotFound' );

        $user = UserService::instance ();
        $userRow = $user->getUserById( $authToken['userId'] );
        if (empty ( $userRow ))
            return new Response ( Http::STATUS_FORBIDDEN, 'userNotFound' );

        $user->setMinecraftUUID( $authToken['userId'], $params['uuid'] );
        $response = array(
            'nick' => $userRow['username'],
            'end'  => strtotime( $sub['endDate'] ) * 1000,
        );

        $response = new Response ( Http::STATUS_OK, json_encode ( $response ) );
        $response->addHeader ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
        return $response;
    }

    /**
     * @Route ("/api/auth")
     * @Transactional
     *
     * @param array $params
     * @return Response
     * @throws Exception
     */
    public function authApi(array $params) {
        try {
            $authHandler = new ApiAuthHandler ();
            return $authHandler->authenticate ( $params );
        } catch ( \Exception $e ) {
            return new Response ( Http::STATUS_ERROR, $e->getMessage () );
        }
    }

    /**
     * @Route ("/auth/twitch")
     * @Transactional
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     * @throws Exception
     */
    public function authTwitch(array $params, ViewModel $model) {
        try {
            $authHandler = new TwitchAuthHandler ();
            return $authHandler->authenticate ( $params );
        } catch ( \Exception $e ) {
            return $this->handleAuthError($e, $model);
        }
    }

    /**
     * @Route ("/auth/twitter")
     * @Transactional
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     * @throws Exception
     */
    public function authTwitter(array $params, ViewModel $model) {
        try {
            $authHandler = new TwitterAuthHandler ();
            return $authHandler->authenticate ( $params );
        } catch ( \Exception $e ) {
            return $this->handleAuthError($e, $model);
        }
    }

    /**
     * @Route ("/auth/google")
     * @Transactional
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     * @throws Exception
     */
    public function authGoogle(array $params, ViewModel $model) {
        try {
            $authHandler = new GoogleAuthHandler ();
            return $authHandler->authenticate ( $params );
        } catch ( \Exception $e ) {
            return $this->handleAuthError($e, $model);
        }
    }

    /**
     * @Route ("/auth/reddit")
     * @Transactional
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     * @throws Exception
     */
    public function authReddit(array $params, ViewModel $model) {
        try {
            $authHandler = new RedditAuthHandler ();
            return $authHandler->authenticate ( $params );
        } catch ( \Exception $e ) {
            return $this->handleAuthError($e, $model);
        }
    }

    /**
     * @param \Exception $e
     * @param ViewModel $model
     * @return string
     */
    private function handleAuthError(\Exception $e, ViewModel $model) {
        if(Session::hasRole ( UserRole::USER )){
            Session::set('modelError', $e->getMessage());
            return 'redirect: /profile/authentication';
        } else {
            $model->title = 'Login error';
            $model->error = $e;
            return 'login';
        }
    }
}