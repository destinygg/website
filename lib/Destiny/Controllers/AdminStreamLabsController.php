<?php
namespace Destiny\Controllers;

use Destiny\Common\AdminIntegrationController;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Config;
use Destiny\Common\Log;
use Destiny\Common\Session\Session;
use Destiny\Common\ViewModel;
use Destiny\StreamLabs\StreamLabsAlertsType;
use Destiny\StreamLabs\StreamLabsAuthHandler;
use Destiny\StreamLabs\StreamLabsService;
use Exception;
use function GuzzleHttp\json_decode;

/**
 * @Controller
 */
class AdminStreamLabsController extends AdminIntegrationController {

    function afterConstruct() {
        $this->authHandler = StreamLabsAuthHandler::instance();
        $this->authenticatedService = StreamLabsService::instance();
        $this->title = 'StreamLabs';
        $this->index = '/admin/streamlabs';
    }

    /**
     * @Route ("/admin/streamlabs")
     * @Secure ({"ADMIN"})
     * @HttpMethod ({"GET"})
     */
    public function index(ViewModel $model): string {
        parent::index($model);
        return 'admin/streamlabs';
    }

    /**
     * @Route ("/admin/streamlabs/authorize")
     * @Secure ({"ADMIN"})
     * @HttpMethod ({"GET"})
     */
    public function authorize(): string {
        return parent::authorize();
    }

    /**
     * @Route ("/auth/streamlabs")
     * @Route ("/admin/streamlabs/auth")
     * @Secure ({"ADMIN"})
     * @HttpMethod ({"GET"})
     */
    public function exchange(array $params): string {
        return parent::exchange($params);
    }

    /**
     * @Route ("/admin/streamlabs/alert/test")
     * @Secure ({"ADMIN"})
     */
    public function alertTest(): string {
        try {
            $response = StreamLabsService::instance()->sendAlert([
                'type' => StreamLabsAlertsType::ALERT_SUBSCRIPTION,
                'message' => '*' . Config::$a['meta']['shortName'] . '* connected...'
            ]);
            $b = json_decode($response->getBody(), true);
            if (isset($b['success']) && $b['success'] == true) {
                Session::setSuccessBag('Test was successful');
            } else {
                Session::setErrorBag('Test was unsuccessful');
            }
        } catch (Exception $e) {
            Log::error($e);
            Session::setErrorBag("Test was unsuccessful. {$e->getMessage()}");
        }
        return 'redirect: /admin/streamlabs';
    }
}