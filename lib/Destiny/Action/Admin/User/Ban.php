<?php
namespace Destiny\Action\Admin\User;

use Destiny\Common\Service\ChatBanService;
use Destiny\Common\Service\ChatlogService;
use Destiny\Common\Utils\Date;
use Destiny\Common\ViewModel;
use Destiny\Common\Service\UserFeaturesService;
use Destiny\Common\Service\AuthenticationService;
use Destiny\Common\Service\UserService;
use Destiny\Common\AppException;
use Destiny\Common\Session;
use Destiny\Common\Utils\Http;
use Destiny\Common\MimeType;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;

/**
 * @Action
 */
class Ban {

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
			throw new AppException ( 'userId required' );
		}
		
		$authService = AuthenticationService::instance ();
		$userService = UserService::instance ();
		$userFeatureService = UserFeaturesService::instance ();
		
		$user = $userService->getUserById ( $params ['userId'] );
		if (empty ( $user )) {
			throw new AppException ( 'User was not found' );
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
			throw new AppException ( 'userId required' );
		}
		
		$ban = array ();
		$ban ['reason'] = $params ['reason'];
		$ban ['userid'] = Session::getCredentials ()->getUserId ();
		$ban ['ipaddress'] = '';
		$ban ['targetuserid'] = $params ['userId'];
		$ban ['starttimestamp'] = Date::getDateTime ( $params ['starttimestamp'] )->format ( 'Y-m-d H:i:s' );
		$ban ['endtimestamp'] = '';
		if (! empty ( $params ['endtimestamp'] )) {
			$ban ['endtimestamp'] = Date::getDateTime ( $params ['endtimestamp'] )->format ( 'Y-m-d H:i:s' );
		}
		$chatBanService = ChatBanService::instance ();
		$ban ['id'] = $chatBanService->insertBan ( $ban );
		AuthenticationService::instance ()->flagUserForUpdate ( $ban ['targetuserid'] );
		
		Http::header ( Http::HEADER_LOCATION, '/admin/user/' . $params ['userId'] . '/ban/' . $ban ['id'] . '/edit' );
		die ();
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
			throw new AppException ( 'id required' );
		}
		if (! isset ( $params ['userId'] ) || empty ( $params ['userId'] )) {
			throw new AppException ( 'userId required' );
		}
		
		$authService = AuthenticationService::instance ();
		$userService = UserService::instance ();
		$userFeatureService = UserFeaturesService::instance ();
		
		$user = $userService->getUserById ( $params ['userId'] );
		if (empty ( $user )) {
			throw new AppException ( 'User was not found' );
		}
		
		$model->user = $user;
		$model->ban = ChatBanService::instance ()->getBanById ( $params ['id'] );
		return 'admin/userban';
	}

	/**
	 * @Route ("/admin/user/{userId}/ban/{id}/update")
	 * @Secure ({"ADMIN"})
	 * @HttpMethod ({"POST"})
	 *
	 * @param array $params
	 */
	public function updateBan(array $params, ViewModel $model) {
		if (! isset ( $params ['id'] ) || empty ( $params ['id'] )) {
			throw new AppException ( 'id required' );
		}
		if (! isset ( $params ['userId'] ) || empty ( $params ['userId'] )) {
			throw new AppException ( 'userId required' );
		}
		
		$chatBanService = ChatBanService::instance ();
		$eBan = $chatBanService->getBanById ( $params ['id'] );
		
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
		$chatBanService->updateBan ( $ban );
		AuthenticationService::instance ()->flagUserForUpdate ( $ban ['targetuserid'] );
		
		Http::header ( Http::HEADER_LOCATION, '/admin/user/' . $params ['userId'] . '/ban/' . $params ['id'] . '/edit' );
		die ();
	}

	/**
	 * @Route ("/admin/user/{userId}/ban/{id}/remove")
	 * @Secure ({"ADMIN"})
	 *
	 * @param array $params
	 */
	public function removeBan(array $params) {
		if (! isset ( $params ['id'] ) || empty ( $params ['id'] )) {
			throw new AppException ( 'id required' );
		}
		if (! isset ( $params ['userId'] ) || empty ( $params ['userId'] )) {
			throw new AppException ( 'userId required' );
		}
		
		$chatBanService = ChatBanService::instance ();
		$ban = $chatBanService->getBanById ( $params ['id'] );
		$ban ['starttimestamp'] = Date::getDateTime ( $ban ['starttimestamp'] );
		$ban ['endtimestamp'] = Date::getDateTime ( 'NOW' );
		$chatBanService->updateBan ( $ban );
		AuthenticationService::instance ()->flagUserForUpdate ( $ban ['targetuserid'] );
		
		Http::header ( Http::HEADER_LOCATION, '/admin/user/' . $params ['userId'] . '/edit' );
		die ();
	}

}