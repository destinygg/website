<?php
namespace Destiny\Controllers;

use Destiny\Common\Application;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Authentication\AuthenticationService;
use Destiny\Common\Session;
use Destiny\Common\User\UserFeature;
use Destiny\Common\User\UserFeaturesService;
use Destiny\Common\User\UserRole;
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\FilterParams;
use Destiny\Common\Utils\FilterParamsException;
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

    protected function checkPrivateKey(array $params, $type) {
        if (empty ( $params['privatekey'] ) )
            return false;

        return Config::$a['privateKeys'][$type] === $params['privatekey'];
    }

    /**
     * @Route ("/auth/info")
     * @HttpMethod ({"GET"})
     *
     * @param array $params
     * @return string
     */
    public function profileInfo(array $params) {
        $log = Application::instance()->getLogger();

        if(! $this->checkPrivateKey($params, 'api')) {
            $log->info("Bad key check");
            return new Response ( Http::STATUS_BAD_REQUEST, 'privatekey' );
        }

        $userid = null;
        try {
            $userService = UserService::instance();
            if (isset($params['userid'])) {
                FilterParams::required($params, 'userid');
                $userid = $userService->getUserIdByField('userId', $params['userid']);
            } else if (isset($params['discordname'])) {
                FilterParams::required($params, 'discordname');
                $userid = $userService->getUserIdByField('discordname', $params['discordname']);
            } else if (isset($params['minecraftname'])) {
                FilterParams::required($params, 'minecraftname');
                $userid = $userService->getUserIdByField('minecraftname', $params['minecraftname']);
            } else if (isset($params['username'])) {
                FilterParams::required($params, 'username');
                $userid = $userService->getUserIdByField('username', $params['username']);
            } else {
                $log->info("No identification field");
                return new Response (Http::STATUS_BAD_REQUEST, "fielderror");
            }
        } catch (FilterParamsException $e) {
            $log->error("Field error", $e);
            return new Response ( Http::STATUS_BAD_REQUEST, "fielderror" );
        } catch (Exception $e) {
            $log->error("Internal error", $e);
            return new Response ( Http::STATUS_ERROR, "server" );
        }

        if(!empty($userid)) {
            $user = $userService->getUserById($userid);
            if(!empty($user)){
                $authService = AuthenticationService::instance();
                $creds = $authService->getUserCredentials($user, 'request');
                $log->info("Authentication successful", $creds->getData ());
                $response = new Response ( Http::STATUS_OK, json_encode ( $creds->getData () ) );
                $response->addHeader ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
                return $response;
            }
        }

        //$log->info("User not found");
        $response = new Response ( Http::STATUS_ERROR, "usernotfound" );
        return $response;
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
        $log = Application::instance()->getLogger();

        $log->info("Minecraft auth [GET]", $params);

        if(! $this->checkPrivateKey($params, 'minecraft')) {
            $log->info("Bad key check");
            return new Response ( Http::STATUS_BAD_REQUEST, 'privatekey' );
        }

        if (empty ( $params ['uuid'] ) || strlen ( $params ['uuid'] ) > 36 ) {
            $log->info("Bad uuid format");
            return new Response ( Http::STATUS_BAD_REQUEST, 'uuid' );
        }

        if ( !preg_match('/^[a-f0-9-]{32,36}$/', $params ['uuid'] ) ) {
            $log->info("Bad uuid format");
            return new Response ( Http::STATUS_BAD_REQUEST, 'uuid' );
        }

        $userService = UserService::instance();
        $userid = $userService->getUserIdByField('minecraftuuid', $params ['uuid']);
        if (!$userid) {
            $log->info("User not found");
            return new Response (Http::STATUS_NOT_FOUND, 'userNotFound');
        }

        $ban = $userService->getUserActiveBan($userid, @$params ['ipaddress']);
        if (!empty($ban)) {
            $log->info("User banned");
            return new Response (Http::STATUS_FORBIDDEN, 'userBanned');
        }

        $user = $userService->getUserById($userid);
        if (empty ( $user )) {
            $log->info("User not found");
            return new Response ( Http::STATUS_NOT_FOUND, 'userNotFound' );
        }

        $sub = SubscriptionsService::instance()->getUserActiveSubscription($userid);
        $features = UserFeaturesService::instance()->getUserFeatures($userid);
        if (in_array(UserFeature::MINECRAFTVIP, $features) || (!empty ($sub) && ((intval($sub ['subscriptionTier']) >= 1 && $user['istwitchsubscriber']) || intval($sub ['subscriptionTier']) >= 2))) {
            if (empty($sub)) {
                $sub = ['endDate' => Date::getDateTime('+1 hour')->format ( 'Y-m-d H:i:s' )];
            }
        } else {
            $log->info("Subscription not found");
            return new Response (Http::STATUS_FORBIDDEN, 'subscriptionNotFound');
        }

        $log->info("Auth successful");
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
        $log = Application::instance()->getLogger();

        $log->info("Minecraft auth [POST]", $params);

        if(! $this->checkPrivateKey($params, 'minecraft')) {
            $log->info("Bad key check");
            return new Response ( Http::STATUS_BAD_REQUEST, 'privatekey' );
        }

        if (empty ( $params ['uuid'] ) || strlen ( $params ['uuid'] ) > 36 ) {
            $log->info("Bad uuid format");
            return new Response ( Http::STATUS_BAD_REQUEST, 'uuid' );
        }

        if ( !preg_match('/^[a-f0-9-]{32,36}$/', $params ['uuid'] ) ) {
            $log->info("Bad uuid format");
            return new Response ( Http::STATUS_BAD_REQUEST, 'uuid' );
        }


        if (empty ( $params ['name'] ) || mb_strlen ( $params ['name'] ) > 16 ) {
            $log->info("Bad name format");
            return new Response ( Http::STATUS_BAD_REQUEST, 'name' );
        }

        $userService = UserService::instance ();
        $userid = $userService->getUserIdByField('minecraftname', $params ['name']);
        if (! $userid) {
            $log->info("user not found");
            return new Response ( Http::STATUS_NOT_FOUND, 'nameNotFound' );
        }

        $ban = $userService->getUserActiveBan( $userid, @$params ['ipaddress'] );
        if (!empty( $ban )) {
            $log->info("user banned");
            return new Response ( Http::STATUS_FORBIDDEN, 'userBanned' );
        }

        $user = $userService->getUserById($userid);
        if (empty ( $user )) {
            $log->info("user not found");
            return new Response ( Http::STATUS_NOT_FOUND, 'userNotFound' );
        }

        $end = null;
        $sub = SubscriptionsService::instance()->getUserActiveSubscription($userid);
        $features = UserFeaturesService::instance()->getUserFeatures($userid);
        /**
         * If user has MINECRAFTVIP feature
         * or if the user is a twitch subscriber and has a subscription with a tier 1 or higher
         */
        if (in_array(UserFeature::MINECRAFTVIP, $features) || (!empty ($sub) && ((intval($sub ['subscriptionTier']) >= 1 && $user['istwitchsubscriber']) || intval($sub ['subscriptionTier']) >= 2))) {
            if (empty($sub)) {
                $sub = ['endDate' => Date::getDateTime('+1 hour')->format ( 'Y-m-d H:i:s' )];
            }
        } else {
            $log->info("Subscription not found");
            return new Response (Http::STATUS_FORBIDDEN, 'subscriptionNotFound');
        }

        try {
            $success = $userService->setMinecraftUUID( $userid, $params['uuid'] );
            $log->info("uuidAlreadySet");
            if (!$success) {
              $existingUserId = $userService->getUserIdByField('minecraftuuid', $params ['uuid']);

              // only fail if the already set uuid is not the same
              if ( !$existingUserId or $existingUserId != $userid ) {
                  $log->info("uuidAlreadySet");
                  return new Response ( Http::STATUS_FORBIDDEN, 'uuidAlreadySet' );
              }
            }

        } catch ( \Doctrine\DBAL\DBALException $e ) {
            $log->info("duplicateUUID");
            return new Response ( Http::STATUS_BAD_REQUEST, 'duplicateUUID' );
        }

        $response = [
            'nick' => $user['username'],
            'end'  => strtotime( $sub['endDate'] ) * 1000,
        ];

        $log->info("Auth successful");
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
