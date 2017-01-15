<?php
namespace Destiny\Controllers;

use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
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
use Destiny\Commerce\SubscriptionsService;
use Destiny\Common\Config;
use Destiny\Common\User\UserService;
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
            return new Response ( Http::STATUS_BAD_REQUEST, 'privatekey' );

        if (empty ( $params ['uuid'] ) || strlen ( $params ['uuid'] ) > 36 )
            return new Response ( Http::STATUS_BAD_REQUEST, 'uuid' );

        if ( !preg_match('/^[a-f0-9-]{32,36}$/', $params ['uuid'] ) )
            return new Response ( Http::STATUS_BAD_REQUEST, 'uuid' );

        $userService = UserService::instance ();
        $userId = $userService->getUserIdFromMinecraftUUID ( $params ['uuid'] );
        if ( !$userId )
            return new Response ( Http::STATUS_NOT_FOUND, 'userNotFound' );

        $ban = $userService->getUserActiveBan( $userId, @$params ['ipaddress'] );
        if (!empty( $ban ))
          return new Response ( Http::STATUS_FORBIDDEN, 'userBanned' );

        $user = $userService->getUserById( $userId );
        $sub = SubscriptionsService::instance ()->getUserActiveSubscription( $userId );
        if (empty ($sub) || intval($sub ['subscriptionTier']) < 2 || (intval($sub ['subscriptionTier']) == 1 && !$user['istwitchsubscriber'])) {
            return new Response (Http::STATUS_FORBIDDEN, 'subscriptionNotFound');
        }

        $response = new Response ( Http::STATUS_OK, json_encode (['end'  => strtotime($sub['endDate']) * 1000]) );
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
            return new Response ( Http::STATUS_BAD_REQUEST, 'privatekey' );

        if (empty ( $params ['uuid'] ) || strlen ( $params ['uuid'] ) > 36 )
            return new Response ( Http::STATUS_BAD_REQUEST, 'uuid' );

        if ( !preg_match('/^[a-f0-9-]{32,36}$/', $params ['uuid'] ) )
            return new Response ( Http::STATUS_BAD_REQUEST, 'uuid' );

        if (empty ( $params ['name'] ) || mb_strlen ( $params ['name'] ) > 16 )
            return new Response ( Http::STATUS_BAD_REQUEST, 'name' );

        $user   = UserService::instance ();
        $userid = $user->getUserIdFromMinecraftName( $params ['name'] );
        if (! $userid)
            return new Response ( Http::STATUS_NOT_FOUND, 'nameNotFound' );

        $ban = $user->getUserActiveBan( $userid, @$params ['ipaddress'] );
        if (!empty( $ban ))
          return new Response ( Http::STATUS_FORBIDDEN, 'userBanned' );

        $userRow = $user->getUserById( $userid );
        if (empty ( $userRow ))
            return new Response ( Http::STATUS_NOT_FOUND, 'userNotFound' );

        $sub = SubscriptionsService::instance ()->getUserActiveSubscription( $userid );
        if (empty ($sub) || intval($sub ['subscriptionTier']) < 2 || (intval($sub ['subscriptionTier']) == 1 && !$userRow['istwitchsubscriber'])) {
            return new Response (Http::STATUS_FORBIDDEN, 'subscriptionNotFound');
        }

        try {
            $success = $user->setMinecraftUUID( $userid, $params['uuid'] );
            if (!$success) {
              $existingUserId = $user->getUserIdFromMinecraftUUID ( $params ['uuid'] );

              // only fail if the already set uuid is not the same
              if ( !$existingUserId or $existingUserId != $userid )
                return new Response ( Http::STATUS_FORBIDDEN, 'uuidAlreadySet' );
            }

        } catch ( \Doctrine\DBAL\DBALException $e ) {
            return new Response ( Http::STATUS_BAD_REQUEST, 'duplicateUUID' );
        }

        $response = [
            'nick' => $userRow['username'],
            'end'  => strtotime( $sub['endDate'] ) * 1000,
        ];

        $response = new Response ( Http::STATUS_OK, json_encode ( $response ) );
        $response->addHeader ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
        return $response;
    }

    /**
     * @Route ("/api/auth")
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
