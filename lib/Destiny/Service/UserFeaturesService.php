<?php
namespace Destiny\Service;

use Destiny\AppException;
use Destiny\Service;
use Destiny\Application;
use Destiny\Utils\Date;

class UserFeaturesService extends Service {
	
	/**
	 * Singleton instance
	 *
	 * var UserFeaturesService
	 */
	protected static $instance = null;

	/**
	 * Singleton instance
	 *
	 * @return UserFeaturesService
	 */
	public static function instance() {
		return parent::instance ();
	}
	
	/**
	 * The list of features
	 * @var list
	 */
	protected $features = null;

	/**
	 * Get a feature Id by the feature name
	 * @param string $featureName
	 */
	public function getFeatureIdByName($featureName) {
		if ($this->features == null) {
			$conn = Application::instance ()->getConnection ();
			$stmt = $conn->prepare ( 'SELECT featureId, featureName FROM dfl_features ORDER BY featureId ASC' );
			$stmt->execute ();
			$this->features = array ();
			while ( $a = $stmt->fetch () ) {
				$this->features [$a ['featureName']] = $a ['featureId'];
			}
		}
		if (! isset ( $this->features [$featureName] )) {
			throw new AppException ( 'Invalid feature name' );
		}
		return $this->features [$featureName];
	}

	/**
	 * Get a list of user features
	 *
	 * @param int $userId
	 * @return array
	 */
	public function getUserFeatures($userId) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT DISTINCT b.featureName AS `id` FROM dfl_users_features AS a
			INNER JOIN dfl_features AS b ON (b.featureId = a.featureId)
			WHERE userId = :userId
			ORDER BY a.featureId ASC' );
		$stmt->bindValue ( 'userId', $userId, \PDO::PARAM_INT );
		$stmt->execute ();
		$features = array ();
		while ( $feature = $stmt->fetchColumn () ) {
			$features [] = $feature;
		}
		return $features;
	}

	/**
	 * Set a list of user features
	 *
	 * @param int $userId
	 * @param array $features
	 */
	public function setUserFeatures($userId, array $features) {
		$conn = Application::instance ()->getConnection ();
		$this->removeAllUserFeatures ( $userId );
		foreach ( $features as $feature ) {
			$this->addUserFeature ( $userId, $feature ['id'] );
		}
	}

	/**
	 * Add a feature to a user
	 *
	 * @param int $userId
	 * @param string $featureName
	 * @return the specfic feature record id
	 */
	public function addUserFeature($userId, $featureName) {
		$featureId = $this->getFeatureIdByName ( $featureName );
		$conn = Application::instance ()->getConnection ();
		$conn->insert ( 'dfl_users_features', array (
			'userId' => $userId,
			'featureId' => $featureId 
		) );
		return $conn->lastInsertId ();
	}

	/**
	 * Remove a feature from a user
	 *
	 * @param int $userId
	 * @param string $featureName
	 */
	public function removeUserFeature($userId, $featureName) {
		$featureId = $this->getFeatureIdByName ( $featureName );
		$conn = Application::instance ()->getConnection ();
		$conn->delete ( 'dfl_users_features', array (
			'userId' => $userId,
			'featureId' => $featureId 
		) );
	}

	/**
	 * Remove a feature from a user
	 *
	 * @param int $userId
	 * @param string $feature
	 */
	public function removeAllUserFeatures($userId) {
		$conn = Application::instance ()->getConnection ();
		$conn->delete ( 'dfl_users_features', array (
			'userId' => $userId 
		) );
	}

}