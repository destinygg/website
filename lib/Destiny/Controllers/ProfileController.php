<?php
namespace Destiny\Controllers;

use Destiny\Commerce\SubscriptionStatus;
use Destiny\Common\Application;
use Destiny\Common\MimeType;
use Destiny\Common\Response;
use Destiny\Common\Utils\Date;
use Destiny\Common\Session;
use Destiny\Common\Exception;
use Destiny\Common\Utils\Country;
use Destiny\Common\Utils\Http;
use Destiny\Common\ViewModel;
use Destiny\Common\Request;
use Destiny\Common\Config;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Authentication\AuthenticationService;
use Destiny\Common\User\UserService;
use Destiny\Commerce\SubscriptionsService;
use Destiny\Api\ApiAuthenticationService;
use Destiny\Messages\PrivateMessageService;
use Destiny\Twitch\TwitchAuthHandler;
use Destiny\Google\GoogleAuthHandler;
use Destiny\Twitter\TwitterAuthHandler;
use Destiny\Reddit\RedditAuthHandler;
use Destiny\Common\Utils\FilterParams;
use Destiny\Google\GoogleRecaptchaHandler;

/**
 * @Controller
 */
class ProfileController {

    /**
     * @Route ("/profile/info")
     * @Secure ({"USER"})
     *
     * @return string
     */
    public function profileInfo() {
        $response = new Response ( Http::STATUS_OK, json_encode ( Session::getCredentials ()->getData () ) );
        $response->addHeader ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
        return $response;
    }
    
    /**
     * @Route ("/profile")
     * @HttpMethod ({"GET"})
     * @Secure ({"USER"})
     *
     * @param ViewModel $model
     * @return string
     */
    public function profile(ViewModel $model) {
      $userService = UserService::instance ();
      $subscriptionsService = SubscriptionsService::instance ();
      $userId = Session::getCredentials ()->getUserId ();
      
      $address = $userService->getAddressByUserId ( $userId );
      if (empty ( $address )) {
        $address = array ();
        $address ['fullName'] = '';
        $address ['line1'] = '';
        $address ['line2'] = '';
        $address ['city'] = '';
        $address ['region'] = '';
        $address ['zip'] = '';
        $address ['country'] = '';
      }
      
      if (Session::has ( 'modelSuccess' )) {
        $model->success = Session::get ( 'modelSuccess' );
        Session::set ( 'modelSuccess' );
      }
      if (Session::has ( 'modelError' )) {
        $model->error = Session::get ( 'modelError' );
        Session::set ( 'modelError' );
      }

      $subscriptions = $subscriptionsService->getUserActiveAndPendingSubscriptions( $userId );
      $gifts = $subscriptionsService->getSubscriptionsByGifterIdAndStatus ( $userId, SubscriptionStatus::ACTIVE );

      $model->ban = $userService->getUserActiveBan ( $userId );
      $model->user = $userService->getUserById ( $userId );
      $model->gifts = $gifts;
      $model->subscriptions = $subscriptions;
      $model->address = $address;
      $model->title = 'Account';
      return 'profile';
    }

  /**
   * @Route ("/profile/update")
   * @HttpMethod ({"POST"})
   * @Secure ({"USER"})
   *
   * @param array $params
   * @return string
   * @throws Exception
   * @throws \Doctrine\DBAL\DBALException
   * @throws \Exception
   */
    public function profileSave(array $params) {
      // Get user
      $userService = UserService::instance ();
      $authenticationService = AuthenticationService::instance ();
      
      $userId = Session::getCredentials ()->getUserId ();
      $user = $userService->getUserById ( $userId);
      
      if (empty ( $user )) {
        throw new Exception ( 'Invalid user' );
      }
      
      $username = (isset ( $params ['username'] ) && ! empty ( $params ['username'] )) ? $params ['username'] : $user ['username'];
      $email = (isset ( $params ['email'] ) && ! empty ( $params ['email'] )) ? $params ['email'] : $user ['email'];
      $country = (isset ( $params ['country'] ) && ! empty ( $params ['country'] )) ? $params ['country'] : $user ['country'];
      $allowGifting = (isset ( $params ['allowGifting'] )) ? $params ['allowGifting'] : $user ['allowGifting'];

      try {
        $authenticationService->validateUsername ( $username, $user );
        $authenticationService->validateEmail ( $email, $user );
        if (! empty ( $country )) {
          $countryArr = Country::getCountryByCode ( $country );
          if (empty ( $countryArr )) {
            throw new Exception ( 'Invalid country' );
          }
          $country = $countryArr ['alpha-2'];
        }
      } catch ( Exception $e ) {
        Session::set ( 'modelError', $e->getMessage () );
        return 'redirect: /profile';
      }
      
      // Date for update
      $userData = array (
          'username' => $username,
          'country' => $country,
          'email' => $email,
          'allowGifting' => $allowGifting
      );
      
      // Is the user changing their name?
      if (strcasecmp ( $username, $user ['username'] ) !== 0) {
        $nameChangeCount = intval ( $user ['nameChangedCount'] );
        // have they hit their limit
        if ($nameChangeCount >= Config::$a ['profile'] ['nameChangeLimit']) {
          throw new Exception ( 'You have reached your name change limit' );
        } else {
          $userData ['nameChangedDate'] = Date::getDateTime ( 'NOW' )->format ( 'Y-m-d H:i:s' );
          $userData ['nameChangedCount'] = $nameChangeCount + 1;
        }
      }

      $userService->updateUser ( $user ['userId'], $userData );
      $authenticationService->flagUserForUpdate ( $user ['userId'] );
      
      Session::set ( 'modelSuccess', 'Your profile has been updated' );
      return 'redirect: /profile';
    }

    /**
     * @Route ("/profile/authentication")
     * @Secure ({"USER"})
     *
     * @param ViewModel $model
     * @return string
     */
    public function profileAuthentication(ViewModel $model) {
      $userService = UserService::instance ();
      $userId = Session::getCredentials ()->getUserId ();
      $model->title = 'Authentication';
      $model->user = $userService->getUserById ( $userId );
      
      // Build a list of profile types for UI purposes
      $authProfiles = $userService->getAuthProfilesByUserId ( $userId );
      $authProfileTypes = array ();
      if (! empty ( $authProfiles )) {
        foreach ( $authProfiles as $profile ) {
          $authProfileTypes [] = $profile ['authProvider'];
        }
        $model->authProfiles = $authProfiles;
      }
      $model->authProfileTypes = $authProfileTypes;
      
      if (Session::has ( 'modelSuccess' )) {
        $model->success = Session::get ( 'modelSuccess' );
        Session::set ( 'modelSuccess' );
      }
      if (Session::has ( 'modelError' )) {
        $model->error = Session::get ( 'modelError' );
        Session::set ( 'modelError' );
      }
      
      $model->authTokens = ApiAuthenticationService::instance ()->getAuthTokensByUserId ( $userId );
      $model->title = 'Authentication';
      return 'profile/authentication';
    }

  /**
   * @Route ("/profile/authtoken/create")
   * @HttpMethod ({"POST"})
   * @Secure ({"USER"})
   *
   * @param array $params
   * @param Request $request
   * @return string
   * @throws \Exception
   */
    public function profileAuthTokenCreate(array $params, Request $request) {
      if(!isset($params['g-recaptcha-response']) || empty($params['g-recaptcha-response']))
        throw new Exception ( 'You must solve the recaptcha.' );

      $googleRecaptchaHandler = new GoogleRecaptchaHandler();
      $googleRecaptchaHandler->resolve($params['g-recaptcha-response'], $request);

      $apiAuthService = ApiAuthenticationService::instance ();
      $userId = Session::getCredentials ()->getUserId ();
      $tokens = $apiAuthService->getAuthTokensByUserId ( $userId );
      if (count ( $tokens ) >= 5) {
        throw new Exception ( 'You have reached the maximum [5] allowed login keys.' );
      }

      $log = Application::instance()->getLogger();
      $conn = Application::instance()->getConnection();
      $conn->beginTransaction();
      try {
        $token = $apiAuthService->createAuthToken ( $userId );
        $apiAuthService->addAuthToken ( $userId, $token );
        $conn->commit();
      } catch ( \Exception $e ) {
        $log->critical("Error creating auth token");
        $conn->rollBack();
        throw $e;
      }
      Session::set ( 'modelSuccess', 'Auth token created!' );
      return 'redirect: /profile/authentication';
    }

  /**
   * @Route ("/profile/authtoken/{authToken}/delete")
   * @HttpMethod ({"POST"})
   * @Secure ({"USER"})
   *
   * @param array $params
   * @return string
   * @throws Exception
   * @throws \Destiny\Common\Utils\FilterParamsException
   */
    public function profileAuthTokenDelete(array $params) {
      FilterParams::required ( $params, 'authToken' );
      
      $userId = Session::getCredentials ()->getUserId ();
      $apiAuthService = ApiAuthenticationService::instance ();
      $authToken = $apiAuthService->getAuthToken ( $params ['authToken'] );
      if (empty ( $authToken )) {
        throw new Exception ( 'Auth token not found' );
      }
      if ($authToken ['userId'] != $userId) {
        throw new Exception ( 'Auth token not owned by user' );
      }
      $apiAuthService->removeAuthToken ( $authToken ['authTokenId'] );
      Session::set ( 'modelSuccess', 'Auth token removed!' );
      return 'redirect: /profile/authentication';
    }

  /**
   * @Route ("/profile/connect/{provider}")
   * @Secure ({"USER"})
   *
   * @param array $params
   * @return string
   * @throws Exception
   * @throws \Destiny\Common\Utils\FilterParamsException
   */
    public function profileConnect(array $params) {
      FilterParams::required ( $params, 'provider' );
      $authProvider = $params ['provider'];
      
      // check if the auth provider you are trying to login with is not the same as the current
      $currentAuthProvider = Session::getCredentials ()->getAuthProvider ();
      if (strcasecmp ( $currentAuthProvider, $authProvider ) === 0) {
        throw new Exception ( 'Provider already authenticated' );
      }

      // Set a session var that is picked up in the AuthenticationService
      // in the GET method, this variable is unset
      Session::set ( 'accountMerge', '1' );
      
      switch (strtoupper ( $authProvider )) {
        case 'TWITCH' :
          $authHandler = new TwitchAuthHandler ();
          return 'redirect: ' . $authHandler->getAuthenticationUrl ();
        
        case 'GOOGLE' :
          $authHandler = new GoogleAuthHandler ();
          return 'redirect: ' . $authHandler->getAuthenticationUrl ();
        
        case 'TWITTER' :
          $authHandler = new TwitterAuthHandler ();
          return 'redirect: ' . $authHandler->getAuthenticationUrl ();
        
        case 'REDDIT' :
          $authHandler = new RedditAuthHandler ();
          return 'redirect: ' . $authHandler->getAuthenticationUrl ();
        
        default :
          throw new Exception ( 'Authentication type not supported' );
      }
    }

    /**
     * Update/add a address
     *
     * @Route ("/profile/address/update")
     * @HttpMethod ({"POST"})
     * @Secure ({"USER"})
     *
     * @param array $params
     * @return string
     */
    public function updateAddress(array $params){
      
      FilterParams::required ( $params, 'fullName' );
      FilterParams::required ( $params, 'line1' );
      FilterParams::declared ( $params, 'line2' );
      FilterParams::required ( $params, 'city' );
      FilterParams::required ( $params, 'region' );
      FilterParams::required ( $params, 'zip' );
      FilterParams::required ( $params, 'country' );

      $userService = UserService::instance ();
      $userId = Session::getCredentials ()->getUserId ();
      
      $address = $userService->getAddressByUserId ( $userId );
      if (empty ( $address )) {
        $address = array ();
        $address ['userId'] = $userId;
      }
      
      $address ['fullName'] = $params ['fullName'];
      $address ['line1'] = $params ['line1'];
      $address ['line2'] = $params ['line2'];
      $address ['city'] = $params ['city'];
      $address ['region'] = $params ['region'];
      $address ['zip'] = $params ['zip'];
      $address ['country'] = $params ['country'];
      
      if (! isset ( $address ['id'] ) || empty ( $address ['id'] )) {
        $userService->addAddress ( $address );
      } else {
        $userService->updateAddress ( $address );
      }
      
      Session::set ( 'modelSuccess', 'Your address has been updated' );
      return 'redirect: /profile';
    }

    /**
     * Minecraft update
     *
     * @Route ("/profile/minecraft/update")
     * @HttpMethod ({"POST"})
     * @Secure ({"USER"})
     *
     * @param array $params
     * @return string
     */
    public function updateMinecraft(array $params){
        $userService = UserService::instance();
        $userId = Session::getCredentials ()->getUserId ();
        FilterParams::declared ( $params, 'minecraftname' );
        $data = ['minecraftname' => $params['minecraftname']];

        if(trim($data['minecraftname']) == '')
            $data['minecraftname'] = null;

        if (mb_strlen($data['minecraftname']) > 16) {
            Session::set ( 'modelError', 'Minecraft name too long.' );
            return 'redirect: /profile';
        }

        $uId = $userService->getUserIdByField('minecraftname', $params['minecraftname']);
        if($data['minecraftname'] == null || empty($uId) || intval($uId) === intval($userId)){
            $userService->updateUser ( $userId, $data );
            Session::set ( 'modelSuccess', 'Minecraft name has been updated' );
        } else {
            Session::set ( 'modelError', 'Minecraft name already in use' );
        }

        return 'redirect: /profile';
    }

    /**
     * Discord update
     *
     * @Route ("/profile/discord/update")
     * @HttpMethod ({"POST"})
     * @Secure ({"USER"})
     *
     * @param array $params
     * @return string
     */
    public function updateDiscord(array $params){
        $userService = UserService::instance();
        $userId = Session::getCredentials ()->getUserId ();
        FilterParams::declared ( $params, 'discordname' );
        $data = ['discordname' => $params['discordname']];

        if(trim($data['discordname']) == '')
            $data['discordname'] = null;

        if (mb_strlen($data['discordname']) > 36) {
            Session::set ( 'modelError', 'Discord username too long.' );
            return 'redirect: /profile';
        }

        $uId = $userService->getUserIdByField('discordname', $params['discordname']);
        if($data['discordname'] == null || empty($uId) || intval($uId) === intval($userId)){
            $userService->updateUser ( $userId, $data );
            Session::set ( 'modelSuccess', 'Discord info has been updated' );
        } else {
            Session::set ( 'modelError', 'Discord name already in use' );
        }

        return 'redirect: /profile';
    }


}
