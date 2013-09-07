<?php
namespace Destiny\Common\Service;

use Destiny\Common\Exception;
use Destiny\Common\Service;
use Destiny\Common\Application;
use Destiny\Common\Utils\Date;

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
	 * Return a list of features
	 * @return array<featureName, featureId>
	 */
	public function getFeatures() {
		if ($this->features == null) {
			$conn = Application::instance ()->getConnection ();
			$stmt = $conn->prepare ( 'SELECT featureId, featureName FROM dfl_features ORDER BY featureId ASC' );
			$stmt->execute ();
			$this->features = array ();
			while ( $a = $stmt->fetch () ) {
				$this->features [$a ['featureName']] = $a ['featureId'];
			}
		}
		return $this->features;
	}

	/**
	 * Return the full list of features
	 * @return array
	 */
	public function getDetailedFeatures() {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( 'SELECT featureId, featureName, featureLabel FROM dfl_features ORDER BY featureId ASC' );
		$stmt->execute ();
		$features = array ();
		while ( $a = $stmt->fetch () ) {
			$features [$a ['featureName']] = $a;
		}
		return $features;
	}

	/**
	 * Get a feature Id by the feature name
	 * @param string $featureName
	 */
	public function getFeatureIdByName($featureName) {
		$features = $this->getFeatures ();
		if (! isset ( $features [$featureName] )) {
			throw new Exception ( sprintf ( 'Invalid feature name %s', $featureName ) );
		}
		return $features [$featureName];
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
		$this->removeAllUserFeatures ( $userId );
		foreach ( $features as $feature ) {
			$this->addUserFeature ( $userId, $feature );
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