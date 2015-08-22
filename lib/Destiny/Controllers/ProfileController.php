<?php
namespace Destiny\Controllers;

use Destiny\Common\Utils\Date;
use Destiny\Common\Session;
use Destiny\Common\Exception;
use Destiny\Common\Utils\Country;
use Destiny\Common\ViewModel;
use Destiny\Common\Request;
use Destiny\Common\Config;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Annotation\Transactional;
use Destiny\Common\Authentication\AuthenticationService;
use Destiny\Common\User\UserFeaturesService;
use Destiny\Common\User\UserService;
use Destiny\Commerce\OrdersService;
use Destiny\Commerce\SubscriptionsService;
use Destiny\Api\ApiAuthenticationService;
use Destiny\Common\Response;
use Destiny\Common\MimeType;
use Destiny\Common\Utils\Http;
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
     * Get a subscriptions payment profile
     * @TODO clean up
     *
     * @param array $subscription         
     * @return array
     */
    private function getPaymentProfile(array $subscription) {
      $orderService = OrdersService::instance ();
      $paymentProfile = null;
      if (! empty ( $subscription ) && ! empty ( $subscription ['paymentProfileId'] )) {
        $paymentProfile = $orderService->getPaymentProfileById ( $subscription ['paymentProfileId'] );
        if (! empty ( $paymentProfile )) {
          $paymentProfile ['billingCycle'] = $orderService->buildBillingCycleString ( $paymentProfile ['billingFrequency'], $paymentProfile ['billingPeriod'] );
        }
      }
      return $paymentProfile;
    }

    /**
     * @Route ("/profile/info")
     * @Secure ({"USER"})
     *
     * @param array $params         
     */
    public function profileInfo(array $params) {
      $response = new Response ( Http::STATUS_OK, json_encode ( Session::getCredentials ()->getData () ) );
      $response->addHeader ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
      return $response;
    }
    
    /**
     * @Route ("/profile")
     * @HttpMethod ({"GET"})
     * @Secure ({"USER"})
     *
     * @param array $params         
     * @param ViewModel $model          
     * @return string
     */
    public function profile(array $params, ViewModel $model) {
      $userService = UserService::instance ();
      $orderService = OrdersService::instance ();
      $subscriptionsService = SubscriptionsService::instance ();
      $userId = Session::getCredentials ()->getUserId ();
      
      $subscription = $subscriptionsService->getUserActiveSubscription ( $userId );
      if (empty ( $subscription )) {
        $subscription = $subscriptionsService->getUserPendingSubscription ( $userId );
      }
      
      $paymentProfile = null;
      $subscriptionType = null;
      
      if (! empty ( $subscription ) && ! empty ( $subscription ['subscriptionType'] )) {
        $subscriptionType = $subscriptionsService->getSubscriptionType ( $subscription ['subscriptionType'] );
        $paymentProfile = $this->getPaymentProfile ( $subscription );
      }
      
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
      
      if (Session::get ( 'modelSuccess' )) {
        $model->success = Session::get ( 'modelSuccess' );
        Session::set ( 'modelSuccess' );
      }
      if (Session::get ( 'modelError' )) {
        $model->error = Session::get ( 'modelError' );
        Session::set ( 'modelError' );
      }
      
      $model->title = 'Profile';
      $model->user = $userService->getUserById ( $userId );
      $model->subscription = $subscription;
      $model->subscriptionType = $subscriptionType;

      $gifts = $subscriptionsService->getActiveSubscriptionsByGifterId ( $userId );
      for ( $i=0; $i < count($gifts); $i++ ){
        $gifts [$i]['type'] = $subscriptionsService->getSubscriptionType ( $gifts [$i]['subscriptionType'] );
      }
      $model->gifts = $gifts;

      $model->paymentProfile = $paymentProfile;
      $model->address = $address;
      $model->title = 'Account';
      return 'profile';
    }

    /**
     * @Route ("/profile/update")
     * @HttpMethod ({"POST"})
     * @Secure ({"USER"})
     * @Transactional
     *
     * @param array $params         
     * @param ViewModel $model          
     * @throws Exception
     * @return string
     */
    public function profileSave(array $params, ViewModel $model) {
      // Get user
      $userService = UserService::instance ();
      $userFeaturesService = UserFeaturesService::instance ();
      $subscriptionsService = SubscriptionsService::instance ();
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
      
      // Update user
      $userService->updateUser ( $user ['userId'], $userData );
      $authenticationService->flagUserForUpdate ( $user ['userId'] );
      
      Session::set ( 'modelSuccess', 'Your profile has been updated' );
      return 'redirect: /profile';
    }

    /**
     * @Route ("/profile/authentication")
     * @Secure ({"USER"})
     *
     * @param array $params         
     * @param ViewModel $model          
     * @return string
     */
    public function profileAuthentication(array $params, ViewModel $model) {
      $userService = UserService::instance ();
      $subscriptionsService = SubscriptionsService::instance ();
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
      
      if (Session::get ( 'modelSuccess' )) {
        $model->success = Session::get ( 'modelSuccess' );
        Session::set ( 'modelSuccess' );
      }
      if (Session::get ( 'modelError' )) {
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
     * @Transactional
     *
     * @param array $params         
     */
    public function profileAuthtokenCreate(array $params, ViewModel $model, Request $request) {
      if(!isset($params['g-recaptcha-response']) || empty($params['g-recaptcha-response']))
        throw new Exception ( 'You must solve the recaptcha.' );

      $googleRecaptchaHandler = new GoogleRecaptchaHandler();
      $googleRecaptchaHandler->resolve(Config::$a ['g-recaptcha'] ['secret'], $params['g-recaptcha-response'], $request->ipAddress());

      $apiAuthService = ApiAuthenticationService::instance ();
      $userId = Session::getCredentials ()->getUserId ();
      $tokens = $apiAuthService->getAuthTokensByUserId ( $userId );
      if (count ( $tokens ) >= 5) {
        throw new Exception ( 'You have reached the maximum [5] allowed login keys.' );
      }
      $token = $apiAuthService->createAuthToken ( $userId );
      $apiAuthService->addAuthToken ( $userId, $token );
      Session::set ( 'modelSuccess', 'Auth token created!' );
      return 'redirect: /profile/authentication';
    }

    /**
     * @Route ("/profile/authtoken/{authToken}/delete")
     * @HttpMethod ({"POST"})
     * @Secure ({"USER"})
     * @Transactional
     *
     * @param array $params         
     */
    public function profileAuthtokenDelete(array $params) {
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
     * @param ViewModel $model          
     * @return string
     */
    public function profileConnect(array $params, ViewModel $model) {
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
     * @Route ("/profile/connect/{provider}/delete")
     * @HttpMethod ({"POST"})
     * @Secure ({"USER"})
     * @Transactional
     *
     * @param array $params   
     * @param ViewModel $model          
     * @return string
     */
    public function profileConnectDelete(array $params) {
      FilterParams::required ( $params, 'provider' );

      $userId = Session::getCredentials ()->getUserId ();
      $apiAuthService = ApiAuthenticationService::instance ();
      $apiAuthService->deleteAuthProfileByUserId($userId, $params ['provider']);
      
      Session::set ( 'modelSuccess', 'Authentication profile removed!' );
      return 'redirect: /profile/authentication';
    }

    /**
     * Update/add a address
     * 
     * @Route ("/profile/address/update")
     * @HttpMethod ({"POST"})
     * @Secure ({"USER"})
     * @Transactional
     *
     * @param array $params         
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

}
