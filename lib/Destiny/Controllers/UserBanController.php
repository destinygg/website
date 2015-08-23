<?php
namespace Destiny\Controllers;

use Destiny\Common\Utils\Date;
use Destiny\Common\ViewModel;
use Destiny\Common\Exception;
use Destiny\Common\Session;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Annotation\Transactional;
use Destiny\Common\User\UserService;
use Destiny\Common\Authentication\AuthenticationService;

/**
 * @Controller
 */
class UserBanController {

    /**
     * @Route ("/admin/user/{userId}/ban")
     * @Secure ({"ADMIN"})
     * @HttpMethod ({"GET"})
     *
     * @param array $params
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
     */
    public function insertBan(array $params, ViewModel $model) {
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
     * @Transactional
     *
     * @param array $params
     */
    public function updateBan(array $params, ViewModel $model) {
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