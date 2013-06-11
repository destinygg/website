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
	public static function getInstance() {
		return parent::getInstance ();
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
		$db = Application::getInstance ()->getDb ();
		return $db->select ( 'SELECT userId, settingName, settingValue FROM dfl_users_settings WHERE userId = \'{userId}\'', array (
				'userId' => Session::get ( 'userId' ) 
		) )->fetchRows ();
	}

	/**
	 * Set a setting value
	 *
	 * @param string $name
	 * @param string $value
	 */
	public function setSetting($name, $value) {
		$db = Application::getInstance ()->getDb ();
		$db->insert ( '
			INSERT INTO dfl_users_settings  (userId,settingName,settingValue)
			VALUES (\'{userId}\',\'{settingName}\',\'{settingValue}\')
			ON DUPLICATE KEY UPDATE settingValue = \'{settingValue}\'
		', array (
				'userId' => Session::get ( 'userId' ),
				'settingName' => $name,
				'settingValue' => $value 
		) );
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
		return self::getInstance ()->getSetting ( $name );
	}

	/**
	 * Set and save a user setting
	 *
	 * @param string $name
	 * @param string $value
	 */
	public static function set($name, $value) {
		return self::getInstance ()->setSetting ( $name, $value );
	}

}