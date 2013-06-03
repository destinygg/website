<?php
namespace Destiny;

/**
 * Simple way of executing actions based on logfiles and cooldowns
 */
class Cron {
	
	/**
	 * A list of actions this cron will run
	 *
	 * @var Array
	 */
	public $actions = array ();
	
	/**
	 * A valid path to a log directory
	 *
	 * @var string
	 */
	public $logPath = '';

	/**
	 * Execute all or one action
	 *
	 * @param array $argv
	 * @throws \Exception
	 */
	public function execute(array $argv = array()) {
		// Check the base log path
		if (! is_dir ( $this->logPath )) {
			throw new \Exception ( 'Log path is not valid ['. $this->logPath .']' );
		}
		$startTime = time ();
		// Record each time this is run
		$cronLog = new Logger ( $this->logPath . 'cron.log' );
		$cronLog->clearLog ( 'Executed cron' ); // to keep it tidy
		if (empty ( $argv )) {
			// If the action was not specified run all the actions in sequence
			foreach ( $this->actions as $i => $action ) {
				// Create/Get a log file based on the action, check its modified date + cooldown
				$log = new Logger ( $this->getLogPath () . strtolower ( $action ['id'] ) . '.log' );
				if ($startTime >= ($log->getLastModified () + $action ['cooldown'])) {
					$this->executeAction ( $action, $log );
				}
			}
		} else {
			// If the action was specified run just the actions without adhering to the cooldown
			foreach ( $this->actions as $i => $action ) {
				if (strcasecmp ( $action ['id'], $argv [0] ) === 0) {
					$log = new Logger ( $this->getLogPath () . strtolower ( $action ['id'] ) . '.log' );
					$this->executeAction ( $action, $log );
				}
			}
		}
	}

	private function executeAction($action, Logger $log) {
		$log->log ( 'Start ' . json_encode ( $action ) );
		try {
			$actionClass = 'Destiny\\Cron\\' . $action ['id'];
			if (class_exists ( $actionClass, true )) {
				$actionObj = new $actionClass ();
				$actionObj->execute ( $log );
			} else {
				throw new \Exception ( 'Could not find action ' . $action ['id'] );
			}
		} catch ( \Exception $e ) {
			$log->log ( $e->getMessage () );
		}
		$log->log ( 'End' );
	}

	public function getActionById($id) {
		foreach ( $this->actions as $i => $action ) {
			if (strcasecmp ( $action ['id'], $id ) === 0) {
				return $action;
			}
		}
		return null;
	}

	public function setLogPath($path) {
		$this->logPath = $path;
	}

	public function getLogPath() {
		return $this->logPath;
	}

	public function add($id, $cooldown = 0) {
		$this->actions [] = array (
				'id' => $id,
				'cooldown' => ( int ) $cooldown 
		);
	}

}