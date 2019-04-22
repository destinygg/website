<?php
namespace Destiny\Controllers;

use Destiny\Common\Exception;
use Destiny\Common\Session\Session;
use Destiny\Common\Config;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Chat\ChatRedisService;
use Destiny\Common\Authentication\AuthenticationService;
use Destiny\Common\User\UserService;
use Destiny\Common\Utils\FilterParamsException;
use Doctrine\DBAL\DBALException;

/**
 * @Controller
 */
class ImpersonateController {

    /**
     * @Route ("/impersonate")
     * @HttpMethod ({"GET"})
     *
     * @param array $params
     * @return string
     *
     * @throws Exception
     * @throws DBALException
     */
    public function impersonate(array $params) {
        try {
            Session::start();
            $authService = AuthenticationService::instance();
            $userService = UserService::instance();
            if (!Config::$a['allowImpersonation']) {
                throw new FilterParamsException ('Impersonating is not allowed');
            }
            $userId = $params ['userId'] ?? '';
            $username = $params ['username'] ?? '';
            if (!empty ($userId)) {
                $user = $userService->getUserById($userId);
            } else if (!empty ($username)) {
                $user = $userService->getUserByUsername($username);
            } else {
                throw new FilterParamsException('Invalid userId or username');
            }
            if (empty ($user)) {
                throw new FilterParamsException ('User not found. Try a different userId or username');
            }
            if ($user['userStatus'] === 'Deleted') {
                throw new FilterParamsException ("User status is 'deleted'.");
            }
            $credentials = $authService->buildUserCredentials($user);
            Session::updateCredentials($credentials);
            $redisService = ChatRedisService::instance();
            $redisService->setChatSession($credentials, Session::getSessionId());
            $redisService->sendRefreshUser($credentials);
        } catch (FilterParamsException $e) {
            Session::setErrorBag($e->getMessage());
        }
        return 'redirect: /';
    }

}
