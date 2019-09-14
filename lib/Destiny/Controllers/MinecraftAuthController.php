<?php
namespace Destiny\Controllers;

use Destiny\Chat\ChatBanService;
use Destiny\Commerce\SubscriptionsService;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\PrivateKey;
use Destiny\Common\Annotation\ResponseBody;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Exception;
use Destiny\Common\Log;
use Destiny\Common\Response;
use Destiny\Common\User\UserFeature;
use Destiny\Common\User\UserService;
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\Http;
use Destiny\Minecraft\MineCraftService;

/**
 * @Controller
 */
class MinecraftAuthController {

    /**
     * @Route ("/auth/minecraft")
     * @HttpMethod ({"GET"})
     * @PrivateKey ({"minecraft"})
     * @ResponseBody
     * @return array|string
     *
     * @throws Exception
     */
    public function authMinecraftGET(Response $response, array $params) {
        Log::info('Minecraft auth [GET]', $params);
        if (empty ($params ['uuid']) || strlen($params ['uuid']) > 36) {
            Log::info('Bad uuid format');
            $response->setStatus(Http::STATUS_BAD_REQUEST);
            return 'uuid';
        }
        if (!preg_match('/^[a-f0-9-]{32,36}$/', $params ['uuid'])) {
            Log::info('Bad uuid format');
            $response->setStatus(Http::STATUS_BAD_REQUEST);
            return 'uuid';
        }
        $userService = UserService::instance();
        $chatBanService = ChatBanService::instance();
        $subscriptionService = SubscriptionsService::instance();
        $userid = $userService->getUserIdByField('minecraftuuid', $params ['uuid']);

        if (!$userid) {
            Log::info('User not found');
            $response->setStatus(Http::STATUS_NOT_FOUND);
            return 'userNotFound';
        }
        if (!empty($params ['ipaddress'])) {
            $ban = $chatBanService->getUserActiveBan($userid, $params ['ipaddress']);
            if (!empty($ban)) {
                Log::info('User banned');
                $response->setStatus(Http::STATUS_FORBIDDEN);
                return 'userBanned';
            }
        }
        $user = $userService->getUserById($userid);
        if (empty ($user)) {
            Log::info('User not found');
            $response->setStatus(Http::STATUS_NOT_FOUND);
            return 'userNotFound';
        }
        $sub = $subscriptionService->getUserActiveSubscription($userid);
        $features = $userService->getFeaturesByUserId($userid);
        if (in_array(UserFeature::MINECRAFTVIP, $features) || boolval($user ['istwitchsubscriber']) || (!empty ($sub) && intval($sub ['subscriptionTier']) >= 1)) {
            if (empty($sub)) {
                $sub = ['endDate' => Date::getDateTime('+1 hour')->format('Y-m-d H:i:s')];
            }
        } else {
            Log::info('Subscription not found');
            $response->setStatus(Http::STATUS_FORBIDDEN);
            return 'subscriptionNotFound';
        }
        Log::info('Auth successful');
        $response->setStatus(Http::STATUS_OK);
        return ['end' => strtotime($sub['endDate']) * 1000];
    }

    /**
     * @Route ("/auth/minecraft")
     * @HttpMethod ({"POST"})
     * @PrivateKey ({"minecraft"})
     * @ResponseBody
     * @return array|string
     *
     * @throws Exception
     */
    public function authMinecraftPOST(Response $response, array $params) {
        Log::info("Minecraft auth [POST]", $params);
        // TODO new MC plugin was having issues with &n which is apparently unavoidable
        if (isset($params['username']) && !isset($params['name'])) {
            $params['name'] = $params['username'];
        }
        if (empty ($params ['uuid']) || strlen($params ['uuid']) > 36) {
            Log::info("Bad uuid format");
            $response->setStatus(Http::STATUS_BAD_REQUEST);
            return 'uuid';
        }
        if (!preg_match('/^[a-f0-9-]{32,36}$/', $params ['uuid'])) {
            Log::info("Bad uuid format");
            $response->setStatus(Http::STATUS_BAD_REQUEST);
            return 'uuid';
        }
        if (empty ($params ['name']) || mb_strlen($params ['name']) > 16) {
            Log::info("Bad name format");
            $response->setStatus(Http::STATUS_BAD_REQUEST);
            return 'name';
        }

        $userService = UserService::instance();
        $chatBanService = ChatBanService::instance();
        $subscriptionService = SubscriptionsService::instance();
        $minecraftService = MineCraftService::instance();

        $userid = $userService->getUserIdByField('minecraftname', $params ['name']);
        if (!$userid) {
            Log::info("user not found");
            $response->setStatus(Http::STATUS_NOT_FOUND);
            return 'nameNotFound';
        }
        if (!empty($params ['ipaddress'])) {
            $ban = $chatBanService->getUserActiveBan($userid, $params ['ipaddress']);
            if (!empty($ban)) {
                Log::info("user banned");
                $response->setStatus(Http::STATUS_FORBIDDEN);
                return 'userBanned';
            }
        }
        $user = $userService->getUserById($userid);
        if (empty ($user)) {
            Log::info("user not found");
            $response->setStatus(Http::STATUS_NOT_FOUND);
            return 'userNotFound';
        }
        $end = null;
        $sub = $subscriptionService->getUserActiveSubscription($userid);
        $features = $userService->getFeaturesByUserId($userid);
        /**
         * If user has MINECRAFTVIP feature
         * or if the user is a twitch subscriber and has a subscription with a tier 1 or higher
         */
        if (in_array(UserFeature::MINECRAFTVIP, $features) || boolval($user ['istwitchsubscriber']) || (!empty ($sub) && intval($sub ['subscriptionTier']) >= 1)) {
            if (empty($sub)) {
                $sub = ['endDate' => Date::getDateTime('+1 hour')->format('Y-m-d H:i:s')];
            }
        } else {
            Log::info("Subscription not found");
            $response->setStatus(Http::STATUS_FORBIDDEN);
            return 'subscriptionNotFound';
        }
        try {
            $success = $minecraftService->setMinecraftUUID($userid, $params['uuid']);
            if (!$success) {
                $existingUserId = $userService->getUserIdByField('minecraftuuid', $params ['uuid']);
                // only fail if the already set uuid is not the same
                if (empty($existingUserId) || $existingUserId != $userid) {
                    Log::info("uuidAlreadySet");
                    $response->setStatus(Http::STATUS_FORBIDDEN);
                    return 'uuidAlreadySet';
                }
            }
        } catch (Exception $e) {
            Log::info("duplicateUUID");
            $response->setStatus(Http::STATUS_BAD_REQUEST);
            return 'duplicateUUID';
        }
        Log::info("Auth successful");
        $response->setStatus(Http::STATUS_OK);
        return [
            'nick' => $user['username'],
            'end' => strtotime($sub['endDate']) * 1000,
        ];
    }

    /**
     * @Route ("/auth/minecraft/update")
     * @HttpMethod ({"GET"})
     * @PrivateKey ({"minecraft"})
     * @ResponseBody
     * @return array|string
     * @throws Exception
     * TODO remove this, minecraft plugin having issues sending post requests.
     */
    public function authMinecraftProcess(Response $response, array $params) {
        Log::info("Minecraft auth [GET => POST]", $params);
        return $this->authMinecraftPOST($response, $params);
    }
}