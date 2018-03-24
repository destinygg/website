<?php
namespace Destiny\Controllers;

use Destiny\Common\Exception;
use Destiny\Common\Session;
use Destiny\Common\Config;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Chat\ChatRedisService;
use Destiny\Common\Authentication\AuthenticationService;
use Destiny\Common\User\UserService;
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
        if (! Config::$a ['allowImpersonation']) {
            throw new Exception ( 'Impersonating is not allowed' );
        }
        $userId = (isset ( $params ['userId'] ) && ! empty ( $params ['userId'] )) ? $params ['userId'] : '';
        $username = (isset ( $params ['username'] ) && ! empty ( $params ['username'] )) ? $params ['username'] : '';
        if (empty ( $userId ) && empty ( $username )) {
            throw new Exception ( '[username] or [userId] required' );
        }
        $authService = AuthenticationService::instance ();
        $userService = UserService::instance ();
        if (! empty ( $userId )) {
            $user = $userService->getUserById ( $userId );
        } else if (! empty ( $username )) {
            $user = $userService->getUserByUsername ( $username );
        }
        
        if (empty ( $user )) {
            throw new Exception ( 'User not found. Try a different userId or username' );
        }
        
        $credentials = $authService->buildUserCredentials ( $user, 'impersonating' );
        Session::start ();
        Session::updateCredentials ( $credentials );
        ChatRedisService::instance ()->setChatSession ( $credentials, Session::getSessionId () );
        $follow = $params['follow'];
        if (!empty ($follow) && substr($follow, 0, 1) == '/') {
            return 'redirect: ' . $follow;
        }
        return 'redirect: /';
    }

}
