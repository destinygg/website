<?php

namespace Destiny\Controllers;

use Destiny\Common\Utils\Date;
use Destiny\Common\Exception;
use Destiny\Common\ViewModel;
use Destiny\Common\Utils\Country;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Annotation\Transactional;
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
class UserAdminController {
  
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
        $model->title = 'User';
        
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
        $model->subscriptions = $subscriptionsService->getUserSubscriptions($user ['userId']);

        if (Session::get ( 'modelSuccess' )) {
            $model->success = Session::get ( 'modelSuccess' );
            Session::set ( 'modelSuccess' );
        }
        return 'admin/user';
    }
    
    /**
     * @Route ("/admin/user/{id}/edit")
     * @Secure ({"ADMIN"})
     * @HttpMethod ({"POST"})
     * @Transactional
     *
     * @param array $params         
     * @param ViewModel $model          
     * @throws Exception
     * @return string
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
        
        $authService->validateUsername ( $username, $user );
        $authService->validateEmail ( $email, $user );
        if (! empty ( $country )) {
            $countryArr = Country::getCountryByCode ( $country );
            if (empty ( $countryArr )) {
               throw new Exception ( 'Invalid country' );
            }
            $country = $countryArr ['alpha-2'];
        }
        
        // Data for update
        $userData = array (
            'username' => $username,
            'country' => $country,
            'email' => $email 
        );
        $userService->updateUser ( $user ['userId'], $userData );
        $user = $userService->getUserById ( $params ['id'] );
        
        // Features
        if (! isset ( $params ['features'] ))
           $params ['features'] = array ();
          
          // Roles
        if (! isset ( $params ['roles'] ))
            $params ['roles'] = array ();
        
        $userFeatureService->setUserFeatures ( $user ['userId'], $params ['features'] );
        $userService->setUserRoles ( $user ['userId'], $params ['roles'] );
        $authService->flagUserForUpdate ( $user ['userId'] );
        
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
          'recurring' => false
        );
        
        $authService = AuthenticationService::instance ();
        $authService->flagUserForUpdate ( $params ['id'] );
        $model->title = 'Subsription';
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
        $order = array ();
        
        if (! empty ( $params ['subscriptionId'] )) {
            $subscription = $subscriptionsService->getSubscriptionById ( $params ['subscriptionId'] );
            $order = $ordersService->getOrderById ( $subscription ['orderId'] );
            $payments = $ordersService->getPaymentsByOrderId ( $subscription ['orderId'] );
        }
        
        if (Session::get ( 'modelSuccess' )) {
            $model->success = Session::get ( 'modelSuccess' );
            Session::set ( 'modelSuccess' );
        }
        
        $model->user = $userService->getUserById ( $params ['id'] );
        $model->subscriptions = Config::$a ['commerce'] ['subscriptions'];
        $model->subscription = $subscription;
        $model->order = $order;
        $model->payments = $payments;
        $model->title = 'Subsription';
        return "admin/subscription";
    }
    
    /**
     * @Route ("/admin/user/{id}/subscription/{subscriptionId}/save")
     * @Route ("/admin/user/{id}/subscription/save")
     * @Secure ({"ADMIN"})
     * @HttpMethod ({"POST"})
     *
     * @param array $params         
     * @param ViewModel $model          
     * @throws Exception
     * @return string
     */
    public function subscriptionSave(array $params, ViewModel $model) {
        FilterParams::required ( $params, 'subscriptionType' );
        FilterParams::required ( $params, 'status' );
        FilterParams::required ( $params, 'createdDate' );
        FilterParams::required ( $params, 'endDate' );
        
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

}
