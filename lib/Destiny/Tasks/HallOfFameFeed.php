<?php
namespace Destiny\Tasks;

use Destiny\Common\Annotation\Schedule;
use Destiny\Common\Application;
use Destiny\Common\TaskInterface;
use Destiny\Chat\ChatIntegrationService;

/**
 * @Schedule(frequency=1,period="minute")
 */
class HallOfFameFeed implements TaskInterface {

    public function execute() {
        $chatIntegrationService = ChatIntegrationService::instance();
        $response['top'] = $chatIntegrationService->getChatCombos();
        $response['recent'] = $chatIntegrationService->getChatCombos(true);
        if (!empty($response))
            Application::instance()->getCacheDriver()->save('chatcombos', $response);
    }

}