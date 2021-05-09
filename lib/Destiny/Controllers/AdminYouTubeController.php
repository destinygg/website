<?php
namespace Destiny\Controllers;

use Destiny\Common\AdminIntegrationController;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Log;
use Destiny\Common\Session\Session;
use Destiny\Common\ViewModel;
use Destiny\Tasks\YouTubeMembersFullSync;
use Destiny\YouTube\YouTubeAdminApiService;
use Destiny\YouTube\YouTubeBroadcasterAuthHandler;

/**
 * @Controller
 */
class AdminYouTubeController extends AdminIntegrationController {
    function afterConstruct() {
        $this->authHandler = YouTubeBroadcasterAuthHandler::instance();
        $this->authenticatedService = YouTubeAdminApiService::instance();
        $this->title = 'YouTube Integration';
        $this->index = '/admin/youtube';
    }

    /**
     * @Route ("/admin/youtube")
     * @Secure ({"ADMIN"})
     * @HttpMethod ({"GET"})
     */
    public function index(ViewModel $model): string {
        parent::index($model);
        return 'admin/youtube';
    }

    /**
     * @Route ("/admin/youtube/authorize")
     * @Secure ({"ADMIN"})
     * @HttpMethod ({"GET"})
     */
    public function authorize(): string {
        return parent::authorize();
    }

    /**
     * @Route ("/admin/youtube/auth")
     * @Secure ({"ADMIN"})
     * @HttpMethod ({"GET"})
     */
    public function exchange(array $params): string {
        $redirectUrl = parent::exchange($params);
        // If the session success bag contains a message, assume auth completed
        // successfully and immediately run the task that syncs memberships and
        // membership levels.
        if (Session::has('modelSuccess')) {
            try {
                (new YouTubeMembersFullSync())->execute();
            } catch (Exception $e) {
                Log::error($e->getMessage());
            }
        }

        return $redirectUrl;
    }
}
