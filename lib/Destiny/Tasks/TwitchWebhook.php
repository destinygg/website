<?php
namespace Destiny\Tasks;

use Destiny\Common\Annotation\Schedule;
use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\Log;
use Destiny\Twitch\TwitchWebHookService;

/**
 * @Schedule(frequency=1,period="hour")
 */
class TwitchWebhook {

    public function execute() {
        try {
            $id = Config::$a['twitch']['id'];
            $twitchWebhookService = TwitchWebHookService::instance();
            $twitchWebhookService->sendSubscriptionRequest(
                TwitchWebHookService::MODE_SUBSCRIBE,
                TwitchWebHookService::TOPIC_STREAM,
                TwitchWebHookService::API_BASE . "/streams?user_id=$id"
            );
        } catch (Exception $e) {
            Log::error('Error handling twitch hook cron task ' . $e->getMessage());
        }
    }

}