<?php
namespace Destiny\Tasks;

use Destiny\Common\Annotation\Schedule;
use Destiny\Common\Authentication\AuthenticationService;
use Destiny\Common\Cron\TaskInterface;
use Destiny\Common\Log;
use Destiny\Common\User\UserFeature;
use Destiny\Common\User\UserService;
use Exception;

/**
 * @Schedule(frequency=5,period="minute")
 */
class BdayCheck implements TaskInterface {

    public function execute() {
        $userService = UserService::instance();
        $authService = AuthenticationService::instance();
        try {
            $users = $userService->findByNewBDay();
            foreach ($users as $user) {
                $userService->addUserFeature($user['userId'], UserFeature::DGGBDAY);
                $authService->flagUserForUpdate($user['userId']);
            }
            $users = $userService->findByExpiredBDay();
            foreach ($users as $user) {
                $userService->removeUserFeature($user['userId'], UserFeature::DGGBDAY);
                $authService->flagUserForUpdate($user['userId']);
            }
        } catch (Exception $e) {
            Log::error("Error checking bdays. " . $e->getMessage());
        }
    }

}