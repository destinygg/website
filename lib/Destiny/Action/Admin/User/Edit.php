<?php
namespace Destiny\Action\Admin\User;

use Destiny\Common\Application;
use Destiny\Common\Service\UserFeaturesService;
use Destiny\Common\AppException;
use Destiny\Common\Service\UserService;
use Destiny\Common\Session;
use Destiny\Common\SessionCredentials;
use Destiny\Common\ViewModel;
use Destiny\Common\UserRole;
use Destiny\Common\Utils\Country;
use Destiny\Common\Service\ChatIntegrationService;
use Destiny\Common\Service\AuthenticationService;
use Destiny\Common\Service\SubscriptionsService;
use Destiny\Common\Service\Fantasy\GameService;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;

/**
 * @Action
 */
class Edit {

	/**
	 * @Route ("/admin/user")
	 * @Route ("/admin/user/{id}/edit")
	 * @Secure ({"ADMIN"})
	 * @HttpMethod ({"GET"})
	 *
	 * @param array $params
	 * @param ViewModel $model
	 * @throws AppException
	 * @return string
	 */
	public function executeGet(array $params, ViewModel $model) {
		$model->title = 'User';
		if (! isset ( $params ['id'] ) || empty ( $params ['id'] )) {
			throw new AppException ( 'userId required' );
		}
		$user = UserService::instance ()->getUserById ( $params ['id'] );
		if (empty ( $user )) {
			throw new AppException ( 'User was not found' );
		}
		$user ['roles'] = UserService::instance ()->getUserRolesByUserId ( $user ['userId'] );
		$user ['features'] = UserFeaturesService::instance ()->getUserFeatures ( $user ['userId'] );
		$model->user = $user;
		$model->features = UserFeaturesService::instance ()->getFeatures ();
		return 'admin/user';
	}

	/**
	 * @Route ("/admin/user")
	 * @Route ("/admin/user/{id}/edit")
	 * @Secure ({"ADMIN"})
	 * @HttpMethod ({"POST"})
	 *
	 * @param array $params
	 * @param ViewModel $model
	 * @throws AppException
	 * @return string
	 */
	public function executePost(array $params, ViewModel $model) {
		$model->title = 'User';
		if (! isset ( $params ['id'] ) || empty ( $params ['id'] )) {
			throw new AppException ( 'userId required' );
		}
		$user = UserService::instance ()->getUserById ( $params ['id'] );
		if (empty ( $user )) {
			throw new AppException ( 'User was not found' );
		}
		
		$username = (isset ( $params ['username'] ) && ! empty ( $params ['username'] )) ? $params ['username'] : $user ['username'];
		$email = (isset ( $params ['email'] ) && ! empty ( $params ['email'] )) ? $params ['email'] : $user ['email'];
		$country = (isset ( $params ['country'] ) && ! empty ( $params ['country'] )) ? $params ['country'] : $user ['country'];
		
		AuthenticationService::instance ()->validateUsername ( $username, $user );
		AuthenticationService::instance ()->validateEmail ( $email, $user );
		if (! empty ( $country )) {
			$countryArr = Country::getCountryByCode ( $country );
			if (empty ( $countryArr )) {
				throw new AppException ( 'Invalid country' );
			}
			$country = $countryArr ['alpha-2'];
		}
		
		// Data for update
		$userData = array (
			'username' => $username,
			'country' => $country,
			'email' => $email 
		);
		UserService::instance ()->updateUser ( $user ['userId'], $userData );
		$user = UserService::instance ()->getUserById ( $params ['id'] );
		
		// Features
		if (! isset ( $params ['features'] )) $params ['features'] = array ();
		UserFeaturesService::instance ()->setUserFeatures ( $user ['userId'], $params ['features'] );
		
		// Roles
		if (! isset ( $params ['roles'] )) $params ['roles'] = array ();
		UserService::instance ()->setUserRoles ( $user ['userId'], $params ['roles'] );
		
		// Flag a user session for update
		$cache = Application::instance ()->getCacheDriver ();
		$cache->save ( sprintf ( 'refreshusersession-%s', $user ['userId'] ), 1 );
		
		$user ['roles'] = UserService::instance ()->getUserRolesByUserId ( $user ['userId'] );
		$user ['features'] = UserFeaturesService::instance ()->getUserFeatures ( $user ['userId'] );
		$model->user = $user;
		$model->features = UserFeaturesService::instance ()->getFeatures ();
		$model->profileUpdated = true;
		return 'admin/user';
	}

}
