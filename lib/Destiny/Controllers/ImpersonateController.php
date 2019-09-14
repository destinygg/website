<?php
namespace Destiny\Controllers;

use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Authentication\AuthenticationService;
use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\Session\Session;
use Destiny\Common\User\UserService;
use Destiny\Common\User\UserStatus;
use Destiny\Common\Utils\FilterParamsException;

/**
 * @Controller
 */
class ImpersonateController {

    /**
     * @Route ("/impersonate")
     * @HttpMethod ({"GET"})
     */
    public function impersonate(array $params): string {
        try {
            Session::start();
            $authService = AuthenticationService::instance();
            $userService = UserService::instance();
            if (!Config::$a['allowImpersonation']) {
                throw new FilterParamsException ('Impersonating is not allowed');
            }
            $userId = $params ['userId'] ?? '';
            $username = $params ['username'] ?? '';
            if (!empty($userId)) {
                $user = $userService->getUserById($userId);
            } else if (!empty($username)) {
                $user = $userService->getUserByUsername($username);
            } else {
                throw new FilterParamsException('Invalid userId or username');
            }
            if (empty($user)) {
                throw new FilterParamsException ('User not found. Try a different userId or username');
            }
            if ($user['userStatus'] != UserStatus::ACTIVE) {
                throw new FilterParamsException ("Invalid user status [${user['userStatus']}]");
            }
            $authService->updateWebSession($user);

        } catch (Exception $e) {
            Session::setErrorBag($e->getMessage());
        }
        return 'redirect: /';
    }

}
