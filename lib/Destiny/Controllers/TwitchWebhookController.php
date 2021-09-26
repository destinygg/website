<?php
namespace Destiny\Controllers;

use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\ResponseBody;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Exception;
use Destiny\Common\Log;
use Destiny\Common\Request;
use Destiny\Common\Response;
use Destiny\Common\Utils\Http;
use Destiny\Twitch\TwitchEventSubService;

/**
 * @Controller
 */
class TwitchWebhookController {

    /**
     * Handle incoming twitch webhook callback requests
     *
     * @Route ("/api/twitch/webhook")
     * @HttpMethod ({"POST"})
     * @ResponseBody
     */
    function callback(Response $response, Request $request): ?string {
        try {
            $twitchEventSubService = TwitchEventSubService::instance();

            if ($twitchEventSubService->isCallbackVerificationRequest($request)) {
                $challenge = $twitchEventSubService->handleCallbackVerificationRequest($request);
                return $challenge;
            } else if ($twitchEventSubService->isNotificationRequest($request)) {
                $twitchEventSubService->handleIncomingEvent($request);
            }

            return null;
        } catch (TwitchEventSubSignatureInvalidException $e) {
            $response->setStatus(Http::STATUS_UNAUTHORIZED);
            return null;
        } catch(Exception $e) {
            Log::error("Error handling twitch webhook callback. {$e->getMessage()}");
            $response->setStatus(Http::STATUS_ERROR);
            return null;
        }
    }

}
