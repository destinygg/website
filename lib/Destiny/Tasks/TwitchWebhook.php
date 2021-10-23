<?php
namespace Destiny\Tasks;

use Destiny\Common\Annotation\Schedule;
use Destiny\Common\Config;
use Destiny\Common\Cron\TaskInterface;
use Destiny\Common\Exception;
use Destiny\Common\Log;
use Destiny\Twitch\TwitchEventSubService;

/**
 * This method periodically subscribes to the twitch web hook api
 * Twitch sends HTTP posts to "us" when stream status changes see @see TwitchWebhookController::notify
 *
 * @Schedule(frequency=1,period="hour")
 */
class TwitchWebhook implements TaskInterface {
    const SUPPORTED_EVENTS = [
        TwitchEventSubService::EVENT_STREAM_ONLINE,
        TwitchEventSubService::EVENT_STREAM_OFFLINE
    ];

    public function execute() {
        try {
            $twitchUserId = Config::$a['twitch']['id'];
            $twitchEventSubService = TwitchEventSubService::instance();

            $twitchEventSubService->pruneInactiveSubscriptions();

            $activeSubEvents = $twitchEventSubService->getActiveSubscriptionEventTypes();
            $jsonActiveSubEvents = json_encode($activeSubEvents);
            Log::debug("Active sub events are `$jsonActiveSubEvents`.");
            foreach (self::SUPPORTED_EVENTS as $eventType) {
                if (!in_array($eventType, $activeSubEvents)) {
                    $twitchEventSubService->subscribe(
                        $eventType,
                        $twitchUserId
                    );
                }
            }
        } catch (Exception $e) {
            Log::error("Error running TwitchWebhook task: {$e->getMessage()}", $e->extractRequestResponse());
        }
    }

}
