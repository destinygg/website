<?php
namespace Destiny\Controllers;

use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\Session;
use Destiny\Common\User\UserService;
use Destiny\Common\Utils\Http;
use Destiny\Common\ViewModel;
use GuzzleHttp\Client;

/**
 * @Controller
 */
class AdminTwitchController {

    private $authProvider = 'twitchbroadcaster';
    private $oauthBase = 'https://api.twitch.tv/kraken/oauth2';

    /**
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    private function getBroadcasterUser() {
        $userService = UserService::instance();
        // todo rename streamlabs to something like broadcaster
        return $userService->getUserById(Config::$a['streamlabs']['default_user']);
    }

    /**
     * @Route ("/admin/twitch")
     * @Secure ({"ADMIN"})
     * @HttpMethod ({"GET"})
     *
     * @param ViewModel $model
     * @return string
     * @throws \Doctrine\DBAL\DBALException
     */
    public function adminTwitch(ViewModel $model) {
        $model->user = $this->getBroadcasterUser();
        return 'admin/twitch';
    }

    /**
     * @Route ("/admin/twitch/authorize")
     * @Secure ({"ADMIN"})
     * @HttpMethod ({"GET"})
     * @return string
     */
    public function authorize() {
        $conf = Config::$a ['oauth_providers'] [$this->authProvider];
        return "redirect: $this->oauthBase/authorize?" . http_build_query([
                'response_type' => 'code',
                'force_verify'  => true,
                'scope'         => join(' ', ['channel_subscriptions', 'channel_check_subscription', 'chat_login']),
                'client_id'     => $conf['client_id'],
                'redirect_uri'  => $conf['redirect_uri'],
            ], null, '&');
    }

    /**
     * @Route ("/admin/twitch/auth")
     * @Secure ({"ADMIN"})
     * @HttpMethod ({"GET"})
     * @param array $params
     * @return string
     */
    public function auth(array $params) {
        try {
            if (!isset ($params['code']) || empty ($params['code'])) {
                throw new Exception ('Authentication failed, invalid or empty code.');
            }
            $conf = Config::$a ['oauth_providers'] [$this->authProvider];
            $client = new Client(['timeout' => 15, 'connect_timeout' => 10, 'http_errors' => false]);
            $response = $client->post("$this->oauthBase/token", [
                'headers' => [
                    'User-Agent' => Config::userAgent(),
                    'Client-ID' => $conf['client_id']
                ],
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $conf['client_id'],
                    'client_secret' => $conf['client_secret'],
                    'redirect_uri' => $conf['redirect_uri'],
                    'code' => $params['code']
                ]
            ]);
            $status = $response->getStatusCode();
            if($status == Http::STATUS_OK) {
                $data = json_decode((string)$response->getBody(), true);
                if (empty ($data) || isset ($data['error']) || !isset ($data['access_token']))
                    throw new Exception ('Invalid access_token response');
                Session::setSuccessBag("Access token granted: ". $data['access_token']);
                return "redirect: /admin/twitch";
            }
            throw new Exception ( 'Bad response from oauth provider' );
        } catch (\Exception $e) {
            Session::setErrorBag($e->getMessage());
            return "redirect: /admin/twitch";
        }
    }

}