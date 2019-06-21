<?php
namespace Destiny\Tasks;

use Destiny\Common\Annotation\Schedule;
use Destiny\Common\Config;
use Destiny\Common\Cron\TaskInterface;
use Destiny\Common\Exception;
use Destiny\Common\Log;
use Destiny\Twitch\TwitchWebHookService;

/**
 * This method periodically subscribes to the twitch web hook api
 * Twitch sends HTTP posts to "us" when stream status changes see @see TwitchWebhookController::notify
 *
 * @Schedule(frequency=1,period="hour")
 */
class TwitchWebhook implements TaskInterface {

    public function execute() {
        try {
            $id = Config::$a['twitch']['id'];
            $twitchWebhookService = TwitchWebHookService::instance();
            $twitchWebhookService->sendSubscriptionRequest(
                TwitchWebHookService::MODE_SUBSCRIBE,
                TwitchWebHookService::TOPIC_STREAM,
                TwitchWebHookService::API_BASE . "/streams?user_id=$id",
                $id
            );
        } catch (Exception $e) {
            Log::error('Error handling twitch hook cron task ' . $e->getMessage());
        }
    }

}