<?php

namespace Destiny\Controllers;

use Destiny\Common\Application;
use Destiny\Common\Utils\Date;
use Destiny\Common\Exception;
use Destiny\Common\ViewModel;
use Destiny\Common\Utils\Country;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;
use Destiny\Chat\ChatlogService;
use Destiny\Common\User\UserFeaturesService;
use Destiny\Common\User\UserService;
use Destiny\Common\Authentication\AuthenticationService;
use Destiny\Api\ApiAuthenticationService;
use Destiny\Common\Session;
use Destiny\Commerce\SubscriptionsService;
use Destiny\Common\Utils\FilterParams;
use Destiny\Common\Config;
use Destiny\Commerce\OrdersService;

/**
 * @Controller
 */
class AdminUserController {
  
    /**
     * @Route ("/admin/user/{id}/edit")
     * @Secure ({"ADMIN"})
     * @HttpMethod ({"GET"})
     *
     * @param array $params         
     * @param ViewModel $model          
     * @throws Exception
     * @return string
     */
    public function adminUserEdit(array $params, ViewModel $model) {
        FilterParams::required($params, 'id');
        
        $user = UserService::instance ()->getUserById ( $params ['id'] );
        if (empty ( $user )) {
            throw new Exception ( 'User was not found' );
        }
        
        $userService = UserService::instance ();
        $userFeaturesService = UserFeaturesService::instance ();
        $apiAuthenticationService = ApiAuthenticationService::instance ();
        $chatlogService = ChatlogService::instance ();
        $subscriptionsService = SubscriptionsService::instance();
        
        $user ['roles'] = $userService->getUserRolesByUserId ( $user ['userId'] );
        $user ['features'] = $userFeaturesService->getUserFeatures ( $user ['userId'] );
        $user ['ips'] = $userService->getIPByUserId( $user ['userId'] );

        $model->user = $user;
        $model->smurfs = $userService->findSameIPUsers( $user ['userId'] );
        $model->features = $userFeaturesService->getDetailedFeatures ();
        $ban = $userService->getUserActiveBan ( $user ['userId'] );
        $banContext = array ();
        if (! empty ( $ban )) {
            $banContext = $chatlogService->getChatLogBanContext ( $user ['userId'], Date::getDateTime ( $ban ['starttimestamp'] ), 18 );
        }
        $model->banContext = $banContext;
        $model->ban = $ban;
        $model->authSessions = $apiAuthenticationService->getAuthSessionsByUserId ( $user ['userId'] );
        $model->address = $userService->getAddressByUserId ( $user ['userId'] );
        $model->subscriptions = $subscriptionsService->getSubscriptionsByUserId($user ['userId']);
        $model->gifts = $subscriptionsService->getSubscriptionsByGifter($user ['userId']);

        $gifters = array();
        $recipients = array();

        foreach ( $model->subscriptions as $subscription ){
            if(!empty($subscription['gifter'])){
                $gifters[$subscription['gifter']] = $userService->getUserById($subscription['gifter']);
            }
        }
        foreach ( $model->gifts as $subscription ){
            $recipients[$subscription['userId']] = $userService->getUserById($subscription['userId']);
        }

        $model->gifters = $gifters;
        $model->recipients = $recipients;

        if (Session::get ( 'modelSuccess' )) {
            $model->success = Session::get ( 'modelSuccess' );
            Session::set ( 'modelSuccess' );
        }
        $model->title = 'User';
        return 'admin/user';
    }

    /**
     * @Route ("/admin/user/{id}/edit")
     * @Secure ({"ADMIN"})
     * @HttpMethod ({"POST"})
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     * @throws Exception
     * @throws \Exception
     */
    public function adminUserEditProcess(array $params, ViewModel $model) {
        $model->title = 'User';
        
        FilterParams::required($params, 'id');
        
        $authService = AuthenticationService::instance ();
        $userService = UserService::instance ();
        $userFeatureService = UserFeaturesService::instance ();
        
        $user = $userService->getUserById ( $params ['id'] );
        if (empty ( $user )) {
            throw new Exception ( 'User was not found' );
        }
        
        $username = (isset ( $params ['username'] ) && ! empty ( $params ['username'] )) ? $params ['username'] : $user ['username'];
        $email = (isset ( $params ['email'] ) && ! empty ( $params ['email'] )) ? $params ['email'] : $user ['email'];
        $country = (isset ( $params ['country'] ) && ! empty ( $params ['country'] )) ? $params ['country'] : $user ['country'];
        $allowGifting = (isset ( $params ['allowGifting'] )) ? $params ['allowGifting'] : $user ['allowGifting'];
        $minecraftname = (isset ( $params ['minecraftname'] ) && ! empty ( $params ['minecraftname'] )) ? $params ['minecraftname'] : $user ['minecraftname'];
        $minecraftuuid = (isset ( $params ['minecraftuuid'] ) && ! empty ( $params ['minecraftuuid'] )) ? $params ['minecraftuuid'] : $user ['minecraftuuid'];

        $authService->validateEmail ( $email, $user );
        if (! empty ( $country )) {
            $countryArr = Country::getCountryByCode ( $country );
            if (empty ( $countryArr )) {
               throw new Exception ( 'Invalid country' );
            }
            $country = $countryArr ['alpha-2'];
        }
        
        $userData = array (
            'username' => $username,
            'country' => $country,
            'email' => $email,
            'minecraftname' => $minecraftname,
            'minecraftuuid' => $minecraftuuid,
            'allowGifting' => $allowGifting
        );

        $log = Application::instance()->getLogger();
        $conn = Application::instance()->getConnection();
        $conn->beginTransaction();

        try {
            $userService->updateUser ( $user ['userId'], $userData );
            $user = $userService->getUserById ( $params ['id'] );
            if (! isset ( $params ['features'] ))
                $params ['features'] = array ();
            if (! isset ( $params ['roles'] ))
                $params ['roles'] = array ();
            $userFeatureService->setUserFeatures ( $user ['userId'], $params ['features'] );
            $userService->setUserRoles ( $user ['userId'], $params ['roles'] );
            $authService->flagUserForUpdate ( $user ['userId'] );
            $conn->commit();
        } catch (\Exception $e){
            $log->critical("Error updating user", $user);
            $conn->rollBack();
            throw $e;
        }

        Session::set ( 'modelSuccess', 'User profile updated' );
        return 'redirect: /admin/user/'.$user ['userId'].'/edit';
    }
    
    /**
     * @Route ("/admin/user/{id}/subscription/add")
     * @Secure ({"ADMIN"})
     * @HttpMethod ({"GET"})
     *
     * @param array $params         
     * @param ViewModel $model          
     * @throws Exception
     * @return string
     */
    public function subscriptionAdd(array $params, ViewModel $model) {
        FilterParams::required ( $params, 'id');
        
        $userService = UserService::instance ();

        $model->user = $userService->getUserById ( $params ['id'] );
        $model->subscriptions = Config::$a ['commerce'] ['subscriptions'];
        $model->subscription = array (
          'subscriptionType' => '',
          'createdDate' => gmdate('Y-m-d H:i:s'),
          'endDate' => gmdate('Y-m-d H:i:s'),
          'status' => 'Active',
          'gifter' => '',
          'recurring' => false
        );
        
        $authService = AuthenticationService::instance ();
        $authService->flagUserForUpdate ( $params ['id'] );
        $model->title = 'Subscription';
        return "admin/subscription";
    }
    
    /**
     * @Route ("/admin/user/{id}/subscription/{subscriptionId}/edit")
     * @Secure ({"ADMIN"})
     * @HttpMethod ({"GET"})
     *
     * @param array $params         
     * @param ViewModel $model          
     * @throws Exception
     * @return string
     */
    public function subscriptionEdit(array $params, ViewModel $model) {
        FilterParams::required ( $params, 'id' );
        FilterParams::required ( $params, 'subscriptionId' );
        
        $subscriptionsService = SubscriptionsService::instance ();
        $userService = UserService::instance ();
        $ordersService = OrdersService::instance();
        
        $subscription = array ();
        $payments = array ();

        if (! empty ( $params ['subscriptionId'] )) {
            $subscription = $subscriptionsService->getSubscriptionById ( $params ['subscriptionId'] );
            $payments = $ordersService->getPaymentsBySubscriptionId ( $subscription ['subscriptionId'] );
        }
        
        if (Session::get ( 'modelSuccess' )) {
            $model->success = Session::get ( 'modelSuccess' );
            Session::set ( 'modelSuccess' );
        }
        
        $model->user = $userService->getUserById ( $params ['id'] );
        $model->subscriptions = Config::$a ['commerce'] ['subscriptions'];
        $model->subscription = $subscription;
        $model->payments = $payments;
        $model->title = 'Subscription';
        return "admin/subscription";
    }

    /**
     * @Route ("/admin/user/{id}/subscription/{subscriptionId}/save")
     * @Route ("/admin/user/{id}/subscription/save")
     * @Secure ({"ADMIN"})
     * @HttpMethod ({"POST"})
     *
     * @param array $params
     * @return string
     * @throws Exception
     * @throws \Destiny\Common\Utils\FilterParamsException
     */
    public function subscriptionSave(array $params) {
        FilterParams::required ( $params, 'subscriptionType' );
        FilterParams::required ( $params, 'status' );
        FilterParams::required ( $params, 'createdDate' );
        FilterParams::required ( $params, 'endDate' );
        FilterParams::declared ( $params, 'gifter' );
        
        $subscriptionsService = SubscriptionsService::instance ();
        $subscriptionType = $subscriptionsService->getSubscriptionType($params ['subscriptionType']);

        $subscription = array ();
        $subscription ['subscriptionType'] = $subscriptionType ['id'];
        $subscription ['subscriptionTier'] = $subscriptionType ['tier'];
        $subscription ['status'] = $params ['status'];
        $subscription ['createdDate'] = $params ['createdDate'];
        $subscription ['endDate'] = $params ['endDate'];
        $subscription ['userId'] = $params ['id'];
        $subscription ['subscriptionSource'] = (isset ( $params ['subscriptionSource'] ) && ! empty ( $params ['subscriptionSource'] )) ? $params ['subscriptionSource'] : Config::$a ['subscriptionType'];

        if(!empty($params ['gifter']))
            $subscription ['gifter'] = $params ['gifter'];
        
        if (isset ( $params ['subscriptionId'] ) && ! empty ( $params ['subscriptionId'] )) {
            $subscription ['subscriptionId'] = $params ['subscriptionId'];
            $subscriptionId = $subscription ['subscriptionId'];
            $subscriptionsService->updateSubscription ( $subscription );
            Session::set ( 'modelSuccess', 'Subscription updated!' );
        } else {
            $subscriptionId = $subscriptionsService->addSubscription ( $subscription );
            Session::set ( 'modelSuccess', 'Subscription created!' );
        }
        
        
        $authService = AuthenticationService::instance ();
        $authService->flagUserForUpdate ( $params ['id'] );
        
        return 'redirect: /admin/user/'. urlencode($params['id']) .'/subscription/'. urlencode($subscriptionId) .'/edit';
    }

    /**
     * @Route ("/admin/user/{id}/auth/{provider}/delete")
     * @Secure ({"ADMIN"})
     * @HttpMethod ({"POST"})
     *
     * @param array $params
     * @return string
     */
    public function authProviderDelete(array $params) {
        $apiAuthService = ApiAuthenticationService::instance ();
        $apiAuthService->deleteAuthProfileByUserId($params['id'], $params['provider']);
        Session::set ( 'modelSuccess', 'Authentication profile removed!' );
        return 'redirect: /admin/user/'. urlencode($params['id']) .'/edit';
    }

    /**
     * @Route ("/admin/user/{userId}/ban")
     * @Secure ({"ADMIN"})
     * @HttpMethod ({"GET"})
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     * @throws Exception
     */
    public function addBan(array $params, ViewModel $model) {
        $model->title = 'New Ban';
        if (! isset ( $params ['userId'] ) || empty ( $params ['userId'] )) {
            throw new Exception ( 'userId required' );
        }

        $userService = UserService::instance ();
        $user = $userService->getUserById ( $params ['userId'] );
        if (empty ( $user )) {
            throw new Exception ( 'User was not found' );
        }

        $model->user = $user;
        $time = Date::getDateTime ( 'NOW' );
        $model->ban = array (
            'reason' => '',
            'starttimestamp' => $time->format ( 'Y-m-d H:i:s' ),
            'endtimestamp' => ''
        );
        return 'admin/userban';
    }

    /**
     * @Route ("/admin/user/{userId}/ban")
     * @Secure ({"ADMIN"})
     * @HttpMethod ({"POST"})
     *
     * @param array $params
     * @return string
     * @throws Exception
     */
    public function insertBan(array $params) {
        if (! isset ( $params ['userId'] ) || empty ( $params ['userId'] )) {
            throw new Exception ( 'userId required' );
        }

        $ban = array ();
        $ban ['reason'] = $params ['reason'];
        $ban ['userid'] = Session::getCredentials ()->getUserId ();
        $ban ['ipaddress'] = '';
        $ban ['targetuserid'] = $params ['userId'];
        $ban ['starttimestamp'] = Date::getDateTime ( $params ['starttimestamp'] )->format ( 'Y-m-d H:i:s' );
        if (! empty ( $params ['endtimestamp'] )) {
            $ban ['endtimestamp'] = Date::getDateTime ( $params ['endtimestamp'] )->format ( 'Y-m-d H:i:s' );
        }
        $userService = UserService::instance ();
        $ban ['id'] = $userService->insertBan ( $ban );
        AuthenticationService::instance ()->flagUserForUpdate ( $ban ['targetuserid'] );
        return 'redirect: /admin/user/' . $params ['userId'] . '/ban/' . $ban ['id'] . '/edit';
    }

    /**
     * @Route ("/admin/user/{userId}/ban/{id}/edit")
     * @Secure ({"ADMIN"})
     * @HttpMethod ({"GET"})
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     * @throws Exception
     */
    public function editBan(array $params, ViewModel $model) {
        $model->title = 'Update Ban';
        if (! isset ( $params ['id'] ) || empty ( $params ['id'] )) {
            throw new Exception ( 'id required' );
        }
        if (! isset ( $params ['userId'] ) || empty ( $params ['userId'] )) {
            throw new Exception ( 'userId required' );
        }

        $userService = UserService::instance ();
        $user = $userService->getUserById ( $params ['userId'] );
        if (empty ( $user )) {
            throw new Exception ( 'User was not found' );
        }

        $model->user = $user;
        $model->ban = $userService->getBanById ( $params ['id'] );
        return 'admin/userban';
    }

    /**
     * @Route ("/admin/user/{userId}/ban/{id}/update")
     * @Secure ({"ADMIN"})
     * @HttpMethod ({"POST"})
     *
     * @param array $params
     * @return string
     * @throws Exception
     */
    public function updateBan(array $params) {
        if (! isset ( $params ['id'] ) || empty ( $params ['id'] )) {
            throw new Exception ( 'id required' );
        }
        if (! isset ( $params ['userId'] ) || empty ( $params ['userId'] )) {
            throw new Exception ( 'userId required' );
        }

        $userService = UserService::instance ();
        $authenticationService = AuthenticationService::instance ();
        $eBan = $userService->getBanById ( $params ['id'] );

        $ban = array ();
        $ban ['id'] = $eBan ['id'];
        $ban ['reason'] = $params ['reason'];
        $ban ['userid'] = $eBan ['userid'];
        $ban ['ipaddress'] = $eBan ['ipaddress'];
        $ban ['targetuserid'] = $eBan ['targetuserid'];
        $ban ['starttimestamp'] = Date::getDateTime ( $params ['starttimestamp'] )->format ( 'Y-m-d H:i:s' );
        $ban ['endtimestamp'] = '';
        if (! empty ( $params ['endtimestamp'] )) {
            $ban ['endtimestamp'] = Date::getDateTime ( $params ['endtimestamp'] )->format ( 'Y-m-d H:i:s' );
        }
        $userService->updateBan ( $ban );
        $authenticationService->flagUserForUpdate ( $ban ['targetuserid'] );

        return 'redirect: /admin/user/' . $params ['userId'] . '/ban/' . $params ['id'] . '/edit';
    }

    /**
     * @Route ("/admin/user/{userId}/ban/remove")
     * @Secure ({"ADMIN"})
     *
     * @param array $params
     * @return string
     * @throws Exception
     */
    public function removeBan(array $params) {
        if (! isset ( $params ['userId'] ) || empty ( $params ['userId'] )) {
            throw new Exception ( 'userId required' );
        }

        $userService = UserService::instance ();
        $authenticationService = AuthenticationService::instance ();

        // if there were rows modified there were bans removed, so an update is
        // required, removeUserBan returns the number of rows modified
        if ( $userService->removeUserBan ( $params ['userId'] ) )
            $authenticationService->flagUserForUpdate ( $params ['userId'] );

        if ( isset( $params['follow'] ) and substr( $params['follow'], 0, 1 ) == '/' )
            return 'redirect: ' . $params['follow'];

        return 'redirect: /admin/user/' . $params ['userId'] . '/edit';
    }

}
