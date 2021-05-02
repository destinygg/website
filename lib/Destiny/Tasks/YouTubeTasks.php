<?php
namespace Destiny\Tasks;

use Destiny\Common\Annotation\Schedule;
use Destiny\Common\Cron\TaskInterface;
use Destiny\Common\Log;
use Destiny\YouTube\YouTubeAdminApiService;

/**
 * @Schedule(frequency=1, period="minute")
 */
class YouTubeTasks implements TaskInterface {
    public function execute() {
        Log::debug('Running `YouTubeTasks` task.');
    }
}
