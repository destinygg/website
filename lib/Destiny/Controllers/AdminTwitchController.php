<?php
namespace Destiny\Controllers;

use Destiny\Common\AdminIntegrationController;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Session\Session;
use Destiny\Common\ViewModel;
use Destiny\Twitch\TwitchAdminService;
use Destiny\Twitch\TwitchBroadcastAuthHandler;

/**
 * @Controller
 */
class AdminTwitchController extends AdminIntegrationController {

    function afterConstruct() {
        $this->authHandler = TwitchBroadcastAuthHandler::instance();
        $this->authenticatedService = TwitchAdminService::instance();
        $this->title = 'Twitch Broadcaster';
        $this->index = '/admin/twitch';
    }

    /**
     * @Route ("/admin/twitch")
     * @Secure ({"ADMIN"})
     * @HttpMethod ({"GET"})
     */
    public function index(ViewModel $model): string {
        parent::index($model);
        return 'admin/twitch';
    }

    /**
     * @Route ("/admin/twitch/authorize")
     * @Secure ({"ADMIN"})
     * @HttpMethod ({"GET"})
     */
    public function authorize(): string {
        return parent::authorize();
    }

    /**
     * @Route ("/admin/twitch/auth")
     * @Secure ({"ADMIN"})
     * @HttpMethod ({"GET"})
     */
    public function exchange(array $params): string {
        return parent::exchange($params);
    }

    /**
     * @Route ("/admin/twitch/alert/test")
     * @Secure ({"ADMIN"})
     */
    public function alertTest(): string {
        Session::setErrorBag('No tests available.');
        return 'redirect: /admin/twitch';
    }

}