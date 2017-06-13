<?php
namespace Destiny\Controllers;

use Destiny\Api\ApiAuthenticationService;
use Destiny\Common\Annotation\ResponseBody;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Authentication\AuthenticationService;
use Destiny\Common\Log;
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
use Destiny\Common\Response;
use Destiny\Common\Utils\Http;
use Destiny\Reddit\RedditAuthHandler;
use Destiny\Common\Exception;
use Destiny\Commerce\SubscriptionsService;
use Destiny\Common\Config;
use Destiny\Common\User\UserService;

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
     * @Route ("/api/info/profile")
     * @Route ("/auth/info")
     * @HttpMethod ({"GET"})
     * @ResponseBody
     *
     * @param Response $response
     * @param array $params
     * @return string|array
     */
    public function profileInfo(Response $response, array $params) {
        if(! $this->checkPrivateKey($params, 'api')) {
            Log::info('Bad key check');
            $response->setStatus(Http::STATUS_BAD_REQUEST);
            return 'privatekey';
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
                Log::info("No identification field");
                $response->setStatus(Http::STATUS_BAD_REQUEST);
                return 'fielderror';
            }
        } catch (FilterParamsException $e) {
            Log::error("Field error", $e);
            $response->setStatus(Http::STATUS_BAD_REQUEST);
            return 'fielderror';
        } catch (Exception $e) {
            Log::error("Internal error", $e);
            $response->setStatus(Http::STATUS_BAD_REQUEST);
            return 'server';
        }

        if(!empty($userid)) {
            $user = $userService->getUserById($userid);
            if(!empty($user)){
                $authService = AuthenticationService::instance();
                $creds = $authService->getUserCredentials($user, 'request');
                Log::info("Authentication successful", $creds->getData ());
                $response->setStatus(Http::STATUS_OK);
                return $creds->getData();
            }
        }

        $response->setStatus(Http::STATUS_ERROR);
        return 'usernotfound';
    }

    /**
     * @Route ("/auth/minecraft")
     * @HttpMethod ({"GET"})
     * @ResponseBody
     *
     * @param Response $response
     * @param array $params
     * @return array|string
     */
    public function authMinecraftGET(Response $response, array $params) {
        Log::info('Minecraft auth [GET]', $params);

        if(! $this->checkPrivateKey($params, 'minecraft')) {
            Log::info('Bad key check');
            $response->setStatus(Http::STATUS_BAD_REQUEST);
            return 'privatekey';
        }

        if (empty ( $params ['uuid'] ) || strlen ( $params ['uuid'] ) > 36 ) {
            Log::info('Bad uuid format');
            $response->setStatus(Http::STATUS_BAD_REQUEST);
            return 'uuid';
        }

        if ( !preg_match('/^[a-f0-9-]{32,36}$/', $params ['uuid'] ) ) {
            Log::info('Bad uuid format');
            $response->setStatus(Http::STATUS_BAD_REQUEST);
            return 'uuid';
        }

        $userService = UserService::instance();
        $userid = $userService->getUserIdByField('minecraftuuid', $params ['uuid']);
        if (!$userid) {
            Log::info('User not found');
            $response->setStatus(Http::STATUS_NOT_FOUND);
            return 'userNotFound';
        }

        $ban = $userService->getUserActiveBan($userid, @$params ['ipaddress']);
        if (!empty($ban)) {
            Log::info('User banned');
            $response->setStatus(Http::STATUS_FORBIDDEN);
            return 'userBanned';
        }

        $user = $userService->getUserById($userid);
        if (empty ( $user )) {
            Log::info('User not found');
            $response->setStatus(Http::STATUS_NOT_FOUND);
            return 'userNotFound';
        }

        $sub = SubscriptionsService::instance()->getUserActiveSubscription($userid);
        $features = UserFeaturesService::instance()->getUserFeatures($userid);
        if (in_array(UserFeature::MINECRAFTVIP, $features) || boolval($user ['istwitchsubscriber']) || (!empty ($sub) && intval($sub ['subscriptionTier']) >= 1)) {
            if (empty($sub)) {
                $sub = ['endDate' => Date::getDateTime('+1 hour')->format ( 'Y-m-d H:i:s' )];
            }
        } else {
            Log::info('Subscription not found');
            $response->setStatus(Http::STATUS_FORBIDDEN);
            return 'subscriptionNotFound';
        }

        Log::info('Auth successful');
        $response->setStatus(Http::STATUS_OK);
        return ['end'  => strtotime($sub['endDate']) * 1000];
    }

    /**
     * @Route ("/auth/minecraft")
     * @HttpMethod ({"POST"})
     * @ResponseBody
     *
     * @param Response $response
     * @param array $params
     * @return array|string
     */
    public function authMinecraftPOST(Response $response, array $params) {
        Log::info("Minecraft auth [POST]", $params);

        if(! $this->checkPrivateKey($params, 'minecraft')) {
            Log::info("Bad key check");
            $response->setStatus(Http::STATUS_BAD_REQUEST);
            return 'privatekey';
        }

        if (empty ( $params ['uuid'] ) || strlen ( $params ['uuid'] ) > 36 ) {
            Log::info("Bad uuid format");
            $response->setStatus(Http::STATUS_BAD_REQUEST);
            return 'uuid';
        }

        if ( !preg_match('/^[a-f0-9-]{32,36}$/', $params ['uuid'] ) ) {
            Log::info("Bad uuid format");
            $response->setStatus(Http::STATUS_BAD_REQUEST);
            return 'uuid';
        }


        if (empty ( $params ['name'] ) || mb_strlen ( $params ['name'] ) > 16 ) {
            Log::info("Bad name format");
            $response->setStatus(Http::STATUS_BAD_REQUEST);
            return 'name';
        }

        $userService = UserService::instance ();
        $userid = $userService->getUserIdByField('minecraftname', $params ['name']);
        if (! $userid) {
            Log::info("user not found");
            $response->setStatus(Http::STATUS_NOT_FOUND);
            return 'nameNotFound';
        }

        $ban = $userService->getUserActiveBan( $userid, @$params ['ipaddress'] );
        if (!empty( $ban )) {
            Log::info("user banned");
            $response->setStatus(Http::STATUS_FORBIDDEN);
            return 'userBanned';
        }

        $user = $userService->getUserById($userid);
        if (empty ( $user )) {
            Log::info("user not found");
            $response->setStatus(Http::STATUS_NOT_FOUND);
            return 'userNotFound';
        }

        $end = null;
        $sub = SubscriptionsService::instance()->getUserActiveSubscription($userid);
        $features = UserFeaturesService::instance()->getUserFeatures($userid);
        /**
         * If user has MINECRAFTVIP feature
         * or if the user is a twitch subscriber and has a subscription with a tier 1 or higher
         */
        if (in_array(UserFeature::MINECRAFTVIP, $features) || boolval($user ['istwitchsubscriber']) || (!empty ($sub) && intval($sub ['subscriptionTier']) >= 1)) {
            if (empty($sub)) {
                $sub = ['endDate' => Date::getDateTime('+1 hour')->format ( 'Y-m-d H:i:s' )];
            }
        } else {
            Log::info("Subscription not found");
            $response->setStatus(Http::STATUS_FORBIDDEN);
            return 'subscriptionNotFound';
        }

        try {
            $success = $userService->setMinecraftUUID( $userid, $params['uuid'] );
            Log::info("uuidAlreadySet");
            if (!$success) {
              $existingUserId = $userService->getUserIdByField('minecraftuuid', $params ['uuid']);

              // only fail if the already set uuid is not the same
              if ( !$existingUserId or $existingUserId != $userid ) {
                  Log::info("uuidAlreadySet");
                  $response->setStatus(Http::STATUS_FORBIDDEN);
                  return 'uuidAlreadySet';
              }
            }

        } catch ( \Doctrine\DBAL\DBALException $e ) {
            Log::info("duplicateUUID");
            $response->setStatus(Http::STATUS_BAD_REQUEST);
            return 'duplicateUUID';
        }

        Log::info("Auth successful");
        $response->setStatus(Http::STATUS_OK);
        return [
            'nick' => $user['username'],
            'end'  => strtotime( $sub['endDate'] ) * 1000,
        ];
    }

    /**
     * @Route ("/api/auth")
     * @ResponseBody
     *
     * @param Response $response
     * @param array $params
     * @return array|string
     */
    public function authApi(Response $response, array $params){
        if (!isset ($params ['authtoken']) || empty ($params ['authtoken'])) {
            $response->setStatus(Http::STATUS_FORBIDDEN);
            return 'Invalid or empty authToken';
        }
        $authToken = ApiAuthenticationService::instance()->getAuthToken($params ['authtoken']);
        if (empty ($authToken)) {
            $response->setStatus(Http::STATUS_FORBIDDEN);
            return 'Auth token not found';
        }
        $user = UserService::instance()->getUserById($authToken ['userId']);
        if (empty ($user)) {
            $response->setStatus(Http::STATUS_FORBIDDEN);
            return 'User not found';
        }
        $authenticationService = AuthenticationService::instance();
        $credentials = $authenticationService->getUserCredentials($user, 'API');
        return $credentials->getData();
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
            Session::setErrorBag($e->getMessage());
            return 'redirect: /profile/authentication';
        } else {
            $model->title = 'Login error';
            $model->error = $e;
            return 'login';
        }
    }
}
