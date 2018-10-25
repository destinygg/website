<?php
namespace Destiny\Controllers;

use Destiny\Commerce\SubscriptionsService;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\ResponseBody;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Config;
use Destiny\Common\Log;
use Destiny\Common\Response;
use Destiny\Common\User\UserFeature;
use Destiny\Common\User\UserService;
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\Http;
use Destiny\Minecraft\MineCraftService;
use Doctrine\DBAL\DBALException;

/**
 * @Controller
 */
class MinecraftAuthController {

    protected function checkPrivateKey(array $params, $type) {
        return isset($params['privatekey']) && Config::$a['privateKeys'][$type] === $params['privatekey'];
    }

    /**
     * @Route ("/auth/minecraft")
     * @HttpMethod ({"GET"})
     * @ResponseBody
     *
     * @param Response $response
     * @param array $params
     * @return array|string
     *
     * @throws DBALException
     */
    public function authMinecraftGET(Response $response, array $params) {
        Log::info('Minecraft auth [GET]', $params);
        if (!$this->checkPrivateKey($params, 'minecraft')) {
            Log::info('Bad key check');
            $response->setStatus(Http::STATUS_BAD_REQUEST);
            return 'privatekey';
        }
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
        $subscriptionService = SubscriptionsService::instance();
        $userid = $userService->getUserIdByField('minecraftuuid', $params ['uuid']);

        if (!$userid) {
            Log::info('User not found');
            $response->setStatus(Http::STATUS_NOT_FOUND);
            return 'userNotFound';
        }
        $ban = $userService->getUserActiveBan($userid, @$params ['ipaddress']);
        if (!empty($ban)) {
            Log::info('User banned');
            $response->setStatus(Http::STATUS_FORBIDDEN);
            return 'userBanned';
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
     * @ResponseBody
     *
     * @param Response $response
     * @param array $params
     * @return array|string
     *
     * @throws DBALException
     */
    public function authMinecraftPOST(Response $response, array $params) {
        Log::info("Minecraft auth [POST]", $params);
        if (!$this->checkPrivateKey($params, 'minecraft')) {
            Log::info("Bad key check");
            $response->setStatus(Http::STATUS_BAD_REQUEST);
            return 'privatekey';
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
        $subscriptionService = SubscriptionsService::instance();
        $minecraftService = MineCraftService::instance();

        $userid = $userService->getUserIdByField('minecraftname', $params ['name']);
        if (!$userid) {
            Log::info("user not found");
            $response->setStatus(Http::STATUS_NOT_FOUND);
            return 'nameNotFound';
        }
        $ban = $userService->getUserActiveBan($userid, @$params ['ipaddress']);
        if (!empty($ban)) {
            Log::info("user banned");
            $response->setStatus(Http::STATUS_FORBIDDEN);
            return 'userBanned';
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
            Log::info("uuidAlreadySet");
            if (!$success) {
                $existingUserId = $userService->getUserIdByField('minecraftuuid', $params ['uuid']);
                // only fail if the already set uuid is not the same
                if (!$existingUserId or $existingUserId != $userid) {
                    Log::info("uuidAlreadySet");
                    $response->setStatus(Http::STATUS_FORBIDDEN);
                    return 'uuidAlreadySet';
                }
            }
        } catch (DBALException $e) {
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
}