<?php

namespace Destiny;

use Destiny\Utils\Options;
use Psr\Log\LoggerInterface;

/**
 * Simple way of executing actions based on logfiles and cooldowns
 */
class Scheduler {
	
	/**
	 * Public logger
	 *
	 * @var LoggerInterface
	 */
	public $logger = null;
	
	/**
	 * The schedule data
	 *
	 * @var array
	 */
	public $schedule = array ();

	/**
	 * [logger,schedule]
	 *
	 * @param array $args
	 */
	public function __construct(array $args = array()) {
		Options::setOptions ( $this, $args );
	}

	/**
	 * Load the schedule from the data source
	 *
	 * @return void
	 */
	public function loadSchedule() {
		$db = Application::getInstance ()->getDb ();
		foreach ( $this->schedule as $i => $action ) {
			$task = $this->getTask ( $this->schedule [$i] ['action'] );
			if (empty ( $task )) {
				$this->schedule [$i] ['lastExecuted'] = date ( \DateTime::ATOM );
				$this->schedule [$i] ['executeCount'] = 0;
				$this->insertTask ( $this->schedule [$i] );
			} else {
				$this->schedule [$i] = array_merge ( $this->schedule [$i], $task );
			}
		}
	}

	/**
	 * Load a task from the db by action name
	 *
	 * @param string $name
	 */
	protected function getTask($name) {
		$db = Application::getInstance ()->getDb ();
		return $db->select ( 'SELECT * FROM dfl_scheduled_tasks WHERE action=\'' . $name . '\' LIMIT 0,1' )->fetchRow ();
	}

	/**
	 * Update the tasks last run schedule
	 *
	 * @param array $schedule
	 */
	protected function updateTask(array $task) {
		$db = Application::getInstance ()->getDb ();
		$db->select ( 'UPDATE dfl_scheduled_tasks SET lastExecuted=\'{lastExecuted}\',executeCount=\'{executeCount}\' WHERE action=\'{action}\'', $task )->fetchRows ();
	}

	/**
	 * Insert a task into the db
	 *
	 * @param array $schedule
	 */
	protected function insertTask(array $task) {
		$db = Application::getInstance ()->getDb ();
		$db->select ( 'INSERT INTO dfl_scheduled_tasks SET action=\'{action}\',lastExecuted=\'{lastExecuted}\',frequency=\'{frequency}\',period=\'{period}\',executeOnStart=\'{executeOnStart}\',executeCount=\'{executeCount}\'', $task )->fetchRows ();
	}

	/**
	 * Get a registered task by name
	 *
	 * @param string $name
	 * @return array
	 */
	public function getTaskByName($name) {
		foreach ( $this->schedule as $i => $action ) {
			if (strcasecmp ( $action ['action'], $name ) === 0) {
				return $this->schedule [$i];
			}
		}
		return null;
	}

	/**
	 * Executes all the tasks
	 *
	 * @return void
	 */
	public function executeShedule() {
		$this->logger->info ( 'Schedule starting' );
		foreach ( $this->schedule as $i => $action ) {
			// First run
			if ($this->schedule [$i] ['executeCount'] == 0 && $this->schedule [$i] ['executeOnStart']) {
				$this->schedule [$i] ['executeCount'] = intval ( $this->schedule [$i] ['executeCount'] ) + 1;
				$this->schedule [$i] ['lastExecuted'] = date ( \DateTime::ATOM );
				$this->updateTask ( $this->schedule [$i] );
				$this->executeTask ( $this->schedule [$i] );
				continue;
			}
			// Schedule run
			$nextExecute = new \DateTime ( $this->schedule [$i] ['lastExecuted'] );
			$nextExecute->modify ( '+' . $this->schedule [$i] ['frequency'] . ' ' . $this->schedule [$i] ['period'] );
			if (time () > $nextExecute->getTimestamp ()) {
				$this->schedule [$i] ['executeCount'] = intval ( $this->schedule [$i] ['executeCount'] ) + 1;
				$this->schedule [$i] ['lastExecuted'] = date ( \DateTime::ATOM );
				$this->updateTask ( $this->schedule [$i] );
				$this->executeTask ( $this->schedule [$i] );
			}
		}
		$this->logger->debug ( 'Schedule complete' );
	}

	/**
	 * Execute a task by name
	 *
	 * @param string $name
	 */
	public function executeTaskByName($name) {
		$this->logger->info ( sprintf ( 'Schedule task %s', $name ) );
		$task = $this->getTaskByName ( $name );
		if (! empty ( $task )) {
			$task ['executeCount'] = intval ( $task ['executeCount'] ) + 1;
			$task ['lastExecuted'] = date ( \DateTime::ATOM );
			$this->updateTask ( $task );
			$this->executeTask ( $task );
		}
	}

	/**
	 * Execute schedule task
	 *
	 * @param array $task
	 */
	protected function executeTask(array $task) {
		$this->logger->info ( sprintf ( 'Execute start %s', $task ['action'] ) );
		$actionClass = 'Destiny\\Scheduled\\' . $task ['action'];
		if (class_exists ( $actionClass, true )) {
			$actionObj = new $actionClass ();
			$actionObj->execute ( $this->logger );
		} else {
			throw new AppException ( sprintf ( 'Action not found: %s', $task ['action'] ) );
		}
		$this->logger->info ( sprintf ( 'Execute end %s', $task ['action'] ) );
	}

	public function getLogger() {
		return $this->logger;
	}

	public function setLogger(LoggerInterface $logger) {
		$this->logger = $logger;
	}

	public function getSchedule() {
		return $this->schedule;
	}

	public function setSchedule(array $schedule) {
		$this->schedule = $schedule;
	}

}