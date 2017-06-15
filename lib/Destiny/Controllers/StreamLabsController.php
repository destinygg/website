<?php
namespace Destiny\Controllers;

use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Exception;
use Destiny\Common\Session;
use Destiny\Common\User\UserService;
use Destiny\Common\Utils\Date;
use Destiny\StreamLabs\StreamLabsService;
use Doctrine\DBAL\DBALException;

/**
 * @Controller
 */
class StreamLabsController {

    /**
     * @Route ("/twitchalerts/authorize")
     * @Secure ({"ADMIN"})
     * @HttpMethod ({"GET"})
     * @return string
     */
    public function authorize() {
        $handler = new StreamLabsService();
        return 'redirect: ' . $handler->getAuthenticationUrl();
    }

    /**
     * @Route ("/twitchalerts/oauth")
     * @Secure ({"ADMIN"})
     * @HttpMethod ({"GET"})
     *
     * @param array $params
     * @return string
     *
     * @throws Exception
     * @throws DBALException
     * @throws \OAuth2\Exception
     * @throws \OAuth2\InvalidArgumentException
     */
    public function oauth(array $params) {
        if (! isset ( $params ['code'] ) || empty ( $params ['code'] )) {
            throw new Exception ( 'Authentication failed, invalid or empty code.' );
        }
        $creds = Session::getCredentials ();
        $twitchAlertsAuthHandler = new StreamLabsService();
        $provider = $twitchAlertsAuthHandler->authProvider;
        $auth = $twitchAlertsAuthHandler->authenticate($params ['code']);
        $userService = UserService::instance();
        $authProfile = $userService->getUserAuthProfile($creds->getUserId(), $provider);
        if(empty($authProfile)){
            $userService->addUserAuthProfile([
                'userId'       => $creds->getUserId(),
                'authProvider' => $provider,
                'authCode'     => $auth['access_token'],
                'refreshToken' => $auth['refresh_token'],
                'authId'       => $creds->getUserId(),
                'authDetail'   => $creds->getEmail()
            ]);
        } else {
            $now = Date::getDateTime ( 'NOW' )->format ( 'Y-m-d H:i:s' );
            $userService->updateUserAuthProfile($creds->getUserId(), $provider, [
                'authCode'     => $auth['access_token'],
                'refreshToken' => $auth['refresh_token'],
                'createdDate'  => $now,
                'modifiedDate' => $now
            ]);
        }
        Session::setSuccessBag('Connected TwitchAlerts API');
        return 'redirect: /admin';
    }
}