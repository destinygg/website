<?php
namespace Destiny\Controllers;

use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\ResponseBody;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Exception;
use Destiny\Common\Log;
use Destiny\Common\Request;
use Destiny\Twitch\TwitchEventSubService;

/**
 * @Controller
 */
class TwitchWebhookController {

    /**
     * Method always returns a 200 response
     * Handle incoming twitch webhook callback requests
     *
     * @Route ("/api/twitch/webhook")
     * @HttpMethod ({"POST"})
     * @ResponseBody
     */
    function callback(Request $request): string {
        try {
            $twitchEventSubService = TwitchEventSubService::instance();

            if ($twitchEventSubService->isCallbackVerificationRequest($request)) {
                $twitchEventSubService->handleCallbackVerificationRequest($request);
            } else if ($twitchEventSubService->isNotificationRequest($request)) {
                $twitchEventSubService->handleIncomingEvent($request);
            } else {
                throw Exception('Invalid request type received.');
            }
        } catch (Exception $e) {
            Log::error("Error handling twitch webhook callback. {$e->getMessage()}");
        }
        return 'error';
    }

}
