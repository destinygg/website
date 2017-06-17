<?php
namespace Destiny\Controllers;

use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\Log;
use Destiny\Common\Session;
use Destiny\Common\User\UserService;
use Destiny\Common\Utils\Date;
use Destiny\Common\ViewModel;
use Destiny\StreamLabs\StreamLabsAlertsType;
use Destiny\StreamLabs\StreamLabsService;
use Doctrine\DBAL\DBALException;

/**
 * @Controller
 */
class StreamLabsController {

    /**
     * @Route ("/streamlabs/authorize")
     * @Secure ({"ADMIN","STREAMLABS"})
     * @HttpMethod ({"GET"})
     * @return string
     */
    public function authorize() {
        $handler = StreamLabsService::instance();
        return 'redirect: ' . $handler->getAuthenticationUrl();
    }

    /**
     * @Route ("/streamlabs/oauth")
     * @Secure ({"ADMIN","STREAMLABS"})
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
        $streamLabsService = StreamLabsService::instance();
        $provider = $streamLabsService->authProvider;
        $auth = $streamLabsService->authenticate($params ['code']);
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
        Session::setSuccessBag('Connected StreamLabs API');
        return 'redirect: /admin/streamlabs';
    }

    /**
     * @Route ("/admin/streamlabs")
     * @Secure ({"ADMIN","STREAMLABS"})
     *
     * @param ViewModel $model
     * @return string
     * @throws DBALException
     */
    public function streamlabs(ViewModel $model){
        $userService = UserService::instance();
        $model->user = $userService->getUserById(Config::$a['streamlabs']['default_user']);
        $model->auth = $userService->getUserAuthProfile(Config::$a['streamlabs']['default_user'], 'streamlabs');
        return 'admin/streamlabs';
    }

    /**
     * @Route ("/streamlabs/alert/test")
     * @Secure ({"ADMIN","STREAMLABS"})
     * @return string
     */
    public function alertTest(){
        try {
            $response = StreamLabsService::instance()->sendAlert([
                'type' => StreamLabsAlertsType::ALERT_SUBSCRIPTION,
                'message' => '*' . Config::$a['meta']['shortName'] . '* connected...'
            ]);
            $b = \GuzzleHttp\json_decode($response->getBody(), true);
            if(isset($b['success']) && $b['success'] == true)
                Session::setSuccessBag('StreamLabs test alert was successful');
            else
                Session::setErrorBag('StreamLabs test alert was unsuccessful');
        } catch (\Exception $e) {
            Log::error($e);
            Session::setErrorBag('StreamLabs test alert was unsuccessful' . $e);
        }
        return 'redirect: /admin/streamlabs';
    }
}