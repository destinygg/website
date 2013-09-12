<?php
namespace Destiny\Action\Admin\User;

use Destiny\Common\Utils\Date;
use Destiny\Common\Service\ChatlogService;
use Destiny\Common\Service\ChatBanService;
use Destiny\Common\Service\UserFeaturesService;
use Destiny\Common\Exception;
use Destiny\Common\Service\UserService;
use Destiny\Common\ViewModel;
use Destiny\Common\Utils\Country;
use Destiny\Common\Service\AuthenticationService;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Annotation\Transactional;

/**
 * @Action
 */
class Edit {

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
	public function executeGet(array $params, ViewModel $model) {
		$model->title = 'User';
		if (! isset ( $params ['id'] ) || empty ( $params ['id'] )) {
			throw new Exception ( 'userId required' );
		}
		$user = UserService::instance ()->getUserById ( $params ['id'] );
		if (empty ( $user )) {
			throw new Exception ( 'User was not found' );
		}
		$user ['roles'] = UserService::instance ()->getUserRolesByUserId ( $user ['userId'] );
		$user ['features'] = UserFeaturesService::instance ()->getUserFeatures ( $user ['userId'] );
		$model->user = $user;
		$model->features = UserFeaturesService::instance ()->getDetailedFeatures ();
		$ban = ChatBanService::instance ()->getUserActiveBan ( $user ['userId'] );
		$banContext = array ();
		if (! empty ( $ban )) {
			$banContext = ChatlogService::instance ()->getChatLogBanContext ( $user ['userId'], Date::getDateTime ( $ban ['starttimestamp'] ), 18 );
		}
		$model->banContext = $banContext;
		$model->ban = $ban;
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
	public function executePost(array $params, ViewModel $model) {
		$model->title = 'User';
		if (! isset ( $params ['id'] ) || empty ( $params ['id'] )) {
			throw new Exception ( 'userId required' );
		}
		
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
		if (! isset ( $params ['features'] )) $params ['features'] = array ();
		$userFeatureService->setUserFeatures ( $user ['userId'], $params ['features'] );
		
		// Roles
		if (! isset ( $params ['roles'] )) $params ['roles'] = array ();
		$userService->setUserRoles ( $user ['userId'], $params ['roles'] );
		
		$authService->flagUserForUpdate ( $user ['userId'] );
		
		$user ['roles'] = $userService->getUserRolesByUserId ( $user ['userId'] );
		$user ['features'] = $userFeatureService->getUserFeatures ( $user ['userId'] );
		$ban = ChatBanService::instance ()->getUserActiveBan ( $user ['userId'] );
		$banContext = array ();
		if (! empty ( $ban )) {
			$banContext = ChatlogService::instance ()->getChatLogBanContext ( $user ['userId'], Date::getDateTime ( $ban ['starttimestamp'] ), 18 );
		}
		$model->banContext = $banContext;
		$model->ban = $ban;
		$model->user = $user;
		$model->features = $userFeatureService->getDetailedFeatures ();
		$model->profileUpdated = true;
		return 'admin/user';
	}

}
