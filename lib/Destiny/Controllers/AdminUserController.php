<?php
namespace Destiny\Controllers;

use Destiny\Chat\ChatRedisService;
use Destiny\Common\Application;
use Destiny\Common\Log;
use Destiny\Common\Utils\Date;
use Destiny\Common\Exception;
use Destiny\Common\ViewModel;
use Destiny\Common\Utils\Country;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\User\UserService;
use Destiny\Common\Authentication\AuthenticationService;
use Destiny\Api\ApiAuthenticationService;
use Destiny\Common\Session;
use Destiny\Commerce\SubscriptionsService;
use Destiny\Common\Utils\FilterParams;
use Destiny\Common\Config;
use Destiny\Commerce\OrdersService;
use Doctrine\DBAL\DBALException;

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
     * @return string
     *
     * @throws Exception
     * @throws DBALException
     */
    public function adminUserEdit(array $params, ViewModel $model) {
        FilterParams::required($params, 'id');
        $user = UserService::instance ()->getUserById ( $params ['id'] );
        if (empty ( $user )) {
            throw new Exception ( 'User was not found' );
        }

        $userService = UserService::instance();
        $chatRedisService = ChatRedisService::instance();
        $apiAuthenticationService = ApiAuthenticationService::instance();
        $subscriptionsService = SubscriptionsService::instance();

        $user ['roles'] = $userService->getRolesByUserId($user ['userId']);
        $user ['features'] = $userService->getFeaturesByUserId($user ['userId']);
        $user ['ips'] = $chatRedisService->getIPByUserId($user ['userId']);

        $model->user = $user;
        $model->smurfs = $userService->getUsersByUserIds($chatRedisService->findUserIdsByUsersIp($user ['userId']));
        $model->features = $userService->getNonPseudoFeatures();
        $model->ban = $userService->getUserActiveBan($user ['userId']);
        $model->authSessions = $apiAuthenticationService->getAuthSessionsByUserId($user ['userId']);
        $model->address = $userService->getAddressByUserId($user ['userId']);
        $model->subscriptions = $subscriptionsService->findByUserId($user ['userId']);
        $model->gifts = $subscriptionsService->findCompletedByGifterId($user ['userId']);

        $gifters = [];
        $recipients = [];

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
     *
     * @throws DBALException
     * @throws Exception
     */
    public function adminUserEditProcess(array $params, ViewModel $model) {
        $model->title = 'User';
        
        FilterParams::required($params, 'id');
        
        $authService = AuthenticationService::instance ();
        $userService = UserService::instance ();
        $user = $userService->getUserById ( $params ['id'] );
        if (empty ( $user )) {
            throw new Exception ( 'User was not found' );
        }

        $redirect = 'redirect: /admin/user/'.$user ['userId'].'/edit';
        $username = (isset ($params ['username']) && !empty ($params ['username'])) ? $params ['username'] : $user ['username'];
        $email = (isset ($params ['email']) && !empty ($params ['email'])) ? $params ['email'] : $user ['email'];
        $country = (isset ($params ['country']) && !empty ($params ['country'])) ? $params ['country'] : $user ['country'];
        $allowGifting = (isset ($params ['allowGifting'])) ? $params ['allowGifting'] : $user ['allowGifting'];
        $istwitchsubscriber = (isset ($params ['istwitchsubscriber'])) ? $params ['istwitchsubscriber'] : $user ['istwitchsubscriber'];
        $discordname = (isset ($params ['discordname'])) ? $params ['discordname'] : $user ['discordname'];
        $discorduuid = (isset ($params ['discorduuid'])) ? $params ['discorduuid'] : $user ['discorduuid'];

        if (empty($discordname))
            $discordname = null;
        else if (mb_strlen($discordname) > 36)
            $discordname = mb_substr($discordname, 0, 36);
        
        if (empty($discorduuid))
            $discorduuid = null;
        else if (mb_strlen($discorduuid) > 36)
            $discorduuid = mb_substr($discorduuid, 0, 36);

        $authService->validateEmail ( $email, $user );

        if (! empty ( $country )) {
            $countryArr = Country::getCountryByCode ( $country );
            if (empty ( $countryArr )) {
               throw new Exception ( 'Invalid country' );
            }
            $country = $countryArr ['alpha-2'];
        }

        $dUid = $userService->getUserIdByField('discordname', $params['discordname']);
        if($discordname != null && !empty($dUid) &&intval($dUid) !== intval($user ['userId'])) {
            Session::setErrorBag('Discord name already in use #' . $dUid);
            return $redirect;
        }

        $userData = [
            'username' => $username,
            'country' => $country,
            'email' => $email,
            'allowGifting' => $allowGifting,
            'istwitchsubscriber' => $istwitchsubscriber,
            'discordname' => $discordname,
            'discorduuid' => $discorduuid
        ];

        $conn = Application::getDbConn();
        try {
            $conn->beginTransaction();
            $userService->updateUser ( $user ['userId'], $userData );
            $user = $userService->getUserById ( $params ['id'] );
            if (! isset ( $params ['features'] ))
                $params ['features'] = [];
            $userService->setUserFeatures ( $user ['userId'], $params ['features'] );
            $authService->flagUserForUpdate ( $user ['userId'] );
            $conn->commit();
        } catch (DBALException $e){
            Log::critical("Error updating user", $user);
            $conn->rollBack();
            throw $e;
        }

        Session::setSuccessBag('User profile updated');
        return $redirect;
    }

    /**
     * @Route ("/admin/user/{id}/subscription/add")
     * @Secure ({"ADMIN"})
     * @HttpMethod ({"GET"})
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     *
     * @throws Exception
     * @throws DBALException
     */
    public function subscriptionAdd(array $params, ViewModel $model) {
        FilterParams::required ( $params, 'id');
        
        $userService = UserService::instance ();

        $model->user = $userService->getUserById ( $params ['id'] );
        $model->subscriptions = Config::$a ['commerce'] ['subscriptions'];
        $model->subscription = [
          'subscriptionType' => '',
          'createdDate' => gmdate('Y-m-d H:i:s'),
          'endDate' => gmdate('Y-m-d H:i:s'),
          'status' => 'Active',
          'gifter' => '',
          'recurring' => false
        ];
        
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
     * @return string
     *
     * @throws Exception
     * @throws DBALException
     */
    public function subscriptionEdit(array $params, ViewModel $model) {
        FilterParams::required ( $params, 'id' );
        FilterParams::required ( $params, 'subscriptionId' );
        
        $subscriptionsService = SubscriptionsService::instance ();
        $userService = UserService::instance ();
        $ordersService = OrdersService::instance();
        
        $subscription = [];
        $payments = [];

        if (! empty ( $params ['subscriptionId'] )) {
            $subscription = $subscriptionsService->findById ( $params ['subscriptionId'] );
            $payments = $ordersService->getPaymentsBySubscriptionId ( $subscription ['subscriptionId'] );
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
     *
     * @throws Exception
     * @throws DBALException
     */
    public function subscriptionSave(array $params) {
        FilterParams::required ( $params, 'subscriptionType' );
        FilterParams::required ( $params, 'status' );
        FilterParams::required ( $params, 'createdDate' );
        FilterParams::required ( $params, 'endDate' );
        FilterParams::declared ( $params, 'gifter' );

        $userService = UserService::instance ();
        $subscriptionsService = SubscriptionsService::instance ();
        $subscriptionType = $subscriptionsService->getSubscriptionType($params ['subscriptionType']);

        $subscription = [];
        $subscription ['subscriptionType'] = $subscriptionType ['id'];
        $subscription ['subscriptionTier'] = $subscriptionType ['tier'];
        $subscription ['status'] = $params ['status'];
        $subscription ['createdDate'] = $params ['createdDate'];
        $subscription ['endDate'] = $params ['endDate'];
        $subscription ['userId'] = $params ['id'];
        $subscription ['subscriptionSource'] = (isset ( $params ['subscriptionSource'] ) && ! empty ( $params ['subscriptionSource'] )) ? $params ['subscriptionSource'] : Config::$a ['subscriptionType'];

        if (!empty($params ['gifter'])) {
            if (!is_numeric($params ['gifter'])) {
                $gifter = $userService->getUserByUsername($params['gifter']);
                if (empty($gifter))
                    throw new Exception ('Invalid giftee (user not found)');
                if ($subscription ['userId'] == $gifter['userId'])
                    throw new Exception ('Invalid giftee (cannot gift yourself)');
                $subscription ['gifter'] = $gifter['userId'];
            } else {
                $subscription ['gifter'] = $params['gifter'];
            }
        }

        if (isset ($params ['subscriptionId']) && !empty ($params ['subscriptionId'])) {
            $subscription ['subscriptionId'] = $params ['subscriptionId'];
            $subscriptionId = $subscription ['subscriptionId'];
            $subscriptionsService->updateSubscription($subscription);
            Session::setSuccessBag('Subscription updated!');
        } else {
            $subscriptionId = $subscriptionsService->addSubscription($subscription);
            Session::setSuccessBag('Subscription created!');
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
     *
     * @throws DBALException
     */
    public function authProviderDelete(array $params) {
        $apiAuthService = ApiAuthenticationService::instance();
        $apiAuthService->deleteAuthProfileByUserId($params['id'], $params['provider']);
        Session::setSuccessBag('Authentication profile removed!');
        return 'redirect: /admin/user/' . urlencode($params['id']) . '/edit';
    }

    /**
     * @Route ("/admin/user/{userId}/ban")
     * @Secure ({"ADMIN"})
     * @HttpMethod ({"GET"})
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     *
     * @throws Exception
     * @throws DBALException
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
        $model->ban = [
            'reason' => '',
            'starttimestamp' => $time->format ( 'Y-m-d H:i:s' ),
            'endtimestamp' => ''
        ];
        return 'admin/userban';
    }

    /**
     * @Route ("/admin/user/{userId}/ban")
     * @Secure ({"ADMIN"})
     * @HttpMethod ({"POST"})
     *
     * @param array $params
     * @return string
     *
     * @throws Exception
     * @throws DBALException
     */
    public function insertBan(array $params) {
        if (! isset ( $params ['userId'] ) || empty ( $params ['userId'] )) {
            throw new Exception ( 'userId required' );
        }

        $ban = [];
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
     *
     * @throws Exception
     * @throws DBALException
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
     *
     * @throws Exception
     * @throws DBALException
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

        $ban = [];
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
     *
     * @throws Exception
     * @throws DBALException
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
