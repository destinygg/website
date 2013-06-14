<?php

namespace Destiny\Service;

use Destiny\Service;
use Destiny\Application;
use Destiny\Config;
use Destiny\Session;

class Settings extends Service {
	
	/**
	 * The singleton instance
	 *
	 * @var \Destiny\Service\Settings
	 */
	protected static $instance = null;
	
	/**
	 * The default settings
	 *
	 * @var array
	 */
	protected $defaultSettings = array ();
	
	/**
	 * The user settings
	 *
	 * @var array
	 */
	public $settings = array ();

	/**
	 * Return the singleton instance
	 *
	 * @return \Destiny\Service\Settings
	 */
	public static function instance() {
		return parent::instance ();
	}

	/**
	 * Loads the settings SESSION variable
	 */
	public function __construct() {
		$this->defaultSettings = Config::$a ['users'] ['settings'];
		$settings = Session::get ( 'settings' );
		if (is_array ( $settings )) {
			$this->setSettings ( $settings );
		}
	}

	/**
	 * Set all the settings at once
	 *
	 * @param array $settings
	 */
	public function setSettings(array $settings) {
		$this->settings = $this->defaultSettings + $settings;
		Session::set ( 'settings', $this->settings );
	}

	/**
	 * Loads the users settings
	 * puts them on the session for later use
	 *
	 * @param int $userId
	 */
	public function getUserSettings($userId) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( 'SELECT userId, settingName, settingValue FROM dfl_users_settings WHERE userId = :userId' );
		$stmt->bindValue ( 'userId', $userId, \PDO::PARAM_INT );
		$stmt->execute ();
		return $stmt->fetchAll ();
	}

	/**
	 * Set a setting value
	 *
	 * @param string $name
	 * @param string $value
	 */
	public function setSetting($name, $value) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '			
			INSERT INTO dfl_users_settings 
			(userId, settingName, settingValue) VALUES (:userId, :settingName, :settingValue)
			ON DUPLICATE KEY UPDATE settingValue = :settingValue
		' );
		$stmt->bindValue ( 'userId', Session::get ( 'userId' ), \PDO::PARAM_INT );
		$stmt->bindValue ( 'settingName', $name, \PDO::PARAM_STR );
		$stmt->bindValue ( 'settingValue', $value, \PDO::PARAM_STR );
		$stmt->execute ();
		$this->settings [$name] = $value;
		Session::set ( 'settings', $this->settings );
	}

	/**
	 * Get a setting by name
	 *
	 * @param string $name
	 * @return mix
	 */
	public function getSetting($name) {
		if (isset ( $this->settings [$name] )) {
			return $this->settings [$name];
		}
		return null;
	}

	/**
	 * Get a user setting by name
	 *
	 * @param string $name
	 * @return string null
	 */
	public static function get($name) {
		return self::instance ()->getSetting ( $name );
	}

	/**
	 * Set and save a user setting
	 *
	 * @param string $name
	 * @param string $value
	 */
	public static function set($name, $value) {
		return self::instance ()->setSetting ( $name, $value );
	}

}