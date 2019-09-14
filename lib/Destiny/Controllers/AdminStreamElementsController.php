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
use Destiny\StreamElements\StreamElementsAuthHandler;
use Destiny\StreamElements\StreamElementsService;
use Exception;

/**
 * @Controller
 */
class AdminStreamElementsController extends AdminIntegrationController {

    function afterConstruct() {
        $this->authHandler = StreamElementsAuthHandler::instance();
        $this->authenticatedService = StreamElementsService::instance();
        $this->title = 'StreamElements';
        $this->index = '/admin/streamelements';
    }

    /**
     * @Route ("/admin/streamelements")
     * @Secure ({"ADMIN"})
     * @HttpMethod ({"GET"})
     */
    public function index(ViewModel $model): string {
        parent::index($model);
        return 'admin/streamelements';
    }

    /**
     * @Route ("/admin/streamelements/authorize")
     * @Secure ({"ADMIN"})
     * @HttpMethod ({"GET"})
     */
    public function authorize(): string {
        return parent::authorize();
    }

    /**
     * @Route ("/auth/streamelements")
     * @Route ("/admin/streamelements/auth")
     * @Secure ({"ADMIN"})
     * @HttpMethod ({"GET"})
     */
    public function exchange(array $params): string {
        return parent::exchange($params);
    }


    /**
     * @Route ("/admin/streamelements/alert/test")
     * @Secure ({"ADMIN"})
     */
    public function alertTest(): string {
        try {
            $response = StreamElementsService::instance()->sendAlert(/*[
                'message' => '*' . Config::$a['meta']['shortName'] . '* connected...'
            ]*/);
            $b = json_decode($response->getBody(), true);
            if (isset($b['channel_id']) && !empty($b['channel_id'])) {
                Session::setSuccessBag('Test was successful');
            } else {
                Session::setErrorBag('Test was unsuccessful');
            }
        } catch (Exception $e) {
            Log::error($e);
            Session::setErrorBag("Test was unsuccessful {$e->getMessage()}");
        }
        return 'redirect: /admin/streamelements';
    }
}