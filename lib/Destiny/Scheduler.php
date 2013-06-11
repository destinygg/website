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
		$this->loadSchedule ();
	}

	/**
	 * Load the schedule from the data source
	 *
	 * @return void
	 */
	protected function loadSchedule() {
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
	protected function updateTask(array $schedule) {
		$db = Application::getInstance ()->getDb ();
		$db->select ( 'UPDATE dfl_scheduled_tasks SET lastExecuted=\'{lastExecuted}\',executeCount=\'{executeCount}\' WHERE action=\'{action}\'', $schedule )->fetchRows ();
	}

	/**
	 * Insert a task into the db
	 *
	 * @param array $schedule
	 */
	protected function insertTask(array $schedule) {
		$db = Application::getInstance ()->getDb ();
		$db->select ( 'INSERT INTO dfl_scheduled_tasks SET action=\'{action}\',lastExecuted=\'{lastExecuted}\',frequency=\'{frequency}\',period=\'{period}\',executeOnStart=\'{executeOnStart}\',executeCount=\'{executeCount}\'', $schedule )->fetchRows ();
	}

	/**
	 * Execute all or one action
	 *
	 * @param array $argv
	 * @throws \Exception
	 */
	public function execute(array $argv = array()) {
		$this->logger->info ( "Schedule execution starting" );
		if (empty ( $argv )) {
			// If the action was not specified run all the actions in sequence
			foreach ( $this->schedule as $i => $action ) {
				
				// First run
				if ($this->schedule [$i] ['executeCount'] == 0 && $this->schedule [$i] ['executeOnStart']) {
					$this->schedule [$i] ['executeCount'] = intval ( $this->schedule [$i] ['executeCount'] ) + 1;
					$this->schedule [$i] ['lastExecuted'] = date ( \DateTime::ATOM );
					$this->updateTask ( $this->schedule [$i] );
					$this->executeAction ( $this->schedule [$i] ['action'] );
					continue;
				}
				
				// Schedule run
				$nextExecute = new \DateTime ( $this->schedule [$i] ['lastExecuted'] );
				$nextExecute->modify ( '+' . $this->schedule [$i] ['frequency'] . ' ' . $this->schedule [$i] ['period'] );
				if (time () > $nextExecute->getTimestamp ()) {
					$this->schedule [$i] ['executeCount'] = intval ( $this->schedule [$i] ['executeCount'] ) + 1;
					$this->schedule [$i] ['lastExecuted'] = date ( \DateTime::ATOM );
					$this->updateTask ( $this->schedule [$i] );
					$this->executeAction ( $this->schedule [$i] ['action'] );
				}
			}
		} else {
			// If the action was specified run just the actions without adhering to the cooldown
			foreach ( $this->schedule as $i => $action ) {
				if (strcasecmp ( $action ['action'], $argv [0] ) === 0) {
					$this->schedule [$i] ['executeCount'] = intval ( $this->schedule [$i] ['executeCount'] ) + 1;
					$this->schedule [$i] ['lastExecuted'] = date ( \DateTime::ATOM );
					$this->updateTask ( $this->schedule [$i] );
					$this->executeAction ( $action ['action'] );
				}
			}
		}
		$this->logger->debug ( 'Schedule execution complete' );
	}

	/**
	 * Execute schedule task
	 *
	 * @param array $action
	 * @throws \Exception
	 */
	public function executeAction($id) {
		$this->logger->info ( sprintf ( 'Execute: %s', $id ) );
		$actionClass = 'Destiny\\Scheduled\\' . $id;
		try {
			if (class_exists ( $actionClass, true )) {
				$actionObj = new $actionClass ();
				$actionObj->execute ( $this->logger );
			} else {
				throw new \Exception ( sprintf ( 'Action not found: %s', $id ) );
			}
		} catch ( \Exception $e ) {
			$this->logger->error ( $e->getMessage () );
		}
		$this->logger->debug ( sprintf ( 'Execute End: %s', $id ) );
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