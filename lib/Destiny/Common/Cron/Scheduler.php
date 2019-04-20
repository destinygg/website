<?php
namespace Destiny\Common\Cron;

use DateTime;
use Destiny\Common\Application;
use Destiny\Common\Exception;
use Destiny\Common\Log;
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\Options;
use Doctrine\DBAL\DBALException;
use PDO;

class Scheduler {

    /**
     * @var array
     */
    public $schedule = [];

    /**
     * @var array
     */
    private $struct = [
        'class' => '',
        'action' => '',
        'lastExecuted' => '',
        'frequency' => '',
        'period' => '',
        'executeCount' => 0
    ];

    /**
     * @param array $args
     */
    public function __construct(array $args = []) {
        Options::setOptions($this, $args);
    }

    /**
     * @return array
     */
    public function getSchedule() {
        return $this->schedule;
    }

    /**
     * @param array $schedule
     */
    public function setSchedule(array $schedule) {
        $this->schedule = $schedule;
    }

    /**
     * @return void
     */
    public function execute() {
        $startTime = microtime(true);
        try {
            Log::info('Schedule starting');
            foreach ($this->schedule as &$task) {
                $taskNeverRun = $task['lastExecuted'] == '';
                $nextExecute = $taskNeverRun ? Date::getDateTime() : Date::getDateTime($task['lastExecuted']);
                $nextExecute->modify('+' . $task['frequency'] . ' ' . $task['period']);
                if ($taskNeverRun || time() > $nextExecute->getTimestamp()) {
                    try {
                        $task['executeCount'] = intval($task['executeCount']) + 1;
                        $task['lastExecuted'] = date(DateTime::ATOM);
                        if($taskNeverRun) {
                            $this->insertTask($task);
                        } else {
                            $this->updateTask($task);
                        }
                        Log::info('Execute start {action}', $task);
                        $this->getTaskClass($task)->execute();
                    } catch (\Exception $e) {
                        Log::error("Error executing task: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
                    }
                    Log::info('Execute end {action}', $task);
                } else {
                    Log::info('Not executed. ' . $task['action'] . ' next: ' . $nextExecute->format('Y-m-d H:i:s'));
                }
            }
            Log::info('Schedule complete');
        } catch (\Exception $e) {
            Log::critical("Error executing tasks: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
        Log::info('Completed in ' . (microtime(true) - $startTime) . ' seconds');
    }

    /**
     * Load tasks from db, and sync with current schedule
     *
     * @throws DBALException
     */
    public function loadTasks(){
        $conn = Application::getDbConn();
        $stmt = $conn->prepare('SELECT * FROM dfl_scheduled_tasks');
        $stmt->execute();
        foreach ($stmt->fetchAll() as $data) {
            if(isset($this->schedule[$data['action']])) {
                $task = &$this->schedule[$data['action']];
                $task = array_merge($task, $data);
            }
        }
        Log::info('Schedule loaded ['. join(',', array_keys($this->schedule)) .']');
    }

    /**
     * @param string $action
     * @param array $task
     */
    public function addTask($action, array $task) {
        $this->schedule[$action] = array_merge($this->struct, $task);
    }

    /**
     * @param array $task
     * @throws DBALException
     */
    protected function updateTask(array $task) {
        $conn = Application::getDbConn();
        $conn->update('dfl_scheduled_tasks', [
            'lastExecuted' => $task ['lastExecuted'],
            'executeCount' => $task ['executeCount']
        ], [
            'action' => $task ['action']
        ], [
            PDO::PARAM_INT,
            PDO::PARAM_STR,
            PDO::PARAM_STR
        ]);
    }

    /**
     * @param array $task
     * @throws DBALException
     */
    protected function insertTask(array $task) {
        $conn = Application::getDbConn();
        $conn->insert('dfl_scheduled_tasks', [
            'action' => $task ['action'],
            'lastExecuted' => $task ['lastExecuted'],
            'frequency' => $task ['frequency'],
            'period' => $task ['period'],
            'executeCount' => $task ['executeCount']
        ], [
            PDO::PARAM_STR,
            PDO::PARAM_STR,
            PDO::PARAM_INT,
            PDO::PARAM_STR,
            PDO::PARAM_INT,
            PDO::PARAM_INT
        ]);
    }

    /**
     * @param array $task
     * @return TaskInterface
     * @throws Exception
     */
    public function getTaskClass(array $task) {
        $class = null;
        if (class_exists($task['class'], true)) {
            $class = new $task['class'] ($task);
        }
        if (!$class) {
            throw new Exception (sprintf('Action not found: %s', $task['class']));
        }
        return $class;
    }

}