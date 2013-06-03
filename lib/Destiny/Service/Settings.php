<?php
namespace Destiny\Service;

use Destiny\Service;
use Destiny\Application;
use Destiny\Config;
use Destiny\Session;

class Settings extends Service {
	
	protected static $instance = null;
	
	/**
	 *
	 * @var CacheApc
	 */
	protected $cache = null;
	
	/**
	 *
	 * @var Array
	 */
	protected $settings = array ();

	/**
	 *
	 * @return ServiceSettings
	 */
	public static function getInstance() {
		return parent::getInstance ();
	}

	/**
	 * Load the initial setting cache
	 *
	 * @return void
	 */
	private function init() {
		if ($this->cache == null) {
			$this->cache = new Config::$a ['cache'] ['memory'] ( array (
					'filename' => 'usersettings' 
			) );
			$this->settings = $this->cache->read ();
			if ($this->settings == null) {
				$this->settings = array ();
			}
		}
		$userId = Session::getUserId ();
		if (! empty ( $userId ) && ! isset ( $this->settings [$userId] )) {
			$this->settings [$userId] = $this->loadSettingsByUser ( $userId );
			$this->cache->write ( $this->settings );
		}
		return $userId;
	}

	/**
	 * Load a settings group by user
	 *
	 * @param int $userId
	 */
	private function loadSettingsByUser($userId) {
		$db = Application::getInstance ()->getDb ();
		return $db->select ( 'SELECT userId, settingName, settingValue FROM dfl_users_settings WHERE userId = \'{userId}\'', array (
				'userId' => intval ( $userId ) 
		) )->fetchRows ();
	}

	/**
	 * Load a settings group by user
	 *
	 * @param int $userId
	 */
	private function saveSettingByUser($userId, $name, $value) {
		$db = Application::getInstance ()->getDb ();
		$db->insert ( '
				INSERT INTO dfl_users_settings 
					(userId,settingName,settingValue)
				VALUES
					(\'{userId}\',\'{settingName}\',\'{settingValue}\')
				ON DUPLICATE KEY UPDATE settingValue = \'{settingValue}\'
			', array (
				'userId' => intval ( $userId ),
				'settingName' => $name,
				'settingValue' => $value 
		) );
	}

	/**
	 * Get a user setting by name
	 *
	 * @param string $name
	 * @return string null
	 */
	public function get($name) {
		$userId = $this->init ();
		if (empty ( $userId ) || empty ( $name )) {
			return null;
		}
		if (isset ( $this->settings [$userId] [$name] )) {
			return $this->settings [$userId] [$name];
		}
		return null;
	}

	/**
	 * Set and save a user setting
	 *
	 * @param string $name
	 * @param string $value
	 */
	public function set($name, $value) {
		$userId = $this->init ();
		if (empty ( $userId ) || empty ( $name )) {
			return;
		}
		$this->settings [$userId] [$name] = $value;
		$this->saveSettingByUser ( $userId, $name, $value );
		$this->cache->write ( $this->settings );
	}

}