<?php
namespace Destiny\Common;

use Destiny\Common\Authentication\AbstractAuthHandler;
use Destiny\Common\Authentication\AbstractAuthService;
use Destiny\Common\Session\Session;
use Destiny\Common\User\UserAuthService;

abstract class AdminIntegrationController {

    /**
     * @var AbstractAuthService
     */
    protected $authenticatedService;

    /**
     * @var AbstractAuthHandler
     */
    protected $authHandler;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $index;

    abstract function afterConstruct();

    public function authorize(): string {
        $this->afterConstruct();
        return 'redirect: ' . $this->authHandler->getAuthorizationUrl();
    }

    public function index(ViewModel $model) {
        $this->afterConstruct();
        $model->title = $this->title;
        $defaultUserId = $this->authenticatedService->getDefaultUserId();
        $defaultUser = $this->authenticatedService->getDefaultUser();
        $auth = $this->authenticatedService->getDefaultAuth();
        $model->auth = $auth;
        $model->user = $defaultUser;
        $model->warning = Session::getCredentials()->getUserId() != $defaultUserId ? "You are NOT signed in with the admin user [$defaultUserId]." : null;
    }

    protected function exchange(array $params): string {
        $this->afterConstruct();
        try {
            $user = $this->authenticatedService->getDefaultUser();
            if (empty($user)) {
                Session::setErrorBag("Default user not found");
                return "redirect: $this->index";
            }
            $res = $this->authHandler->exchangeCode($params);
            UserAuthService::instance()->saveUserAuthWithOAuth($res, $user['userId']);
            Session::setSuccessBag("Authorization completed successfully!");
        } catch (Exception $e) {
            Log::error($e->getMessage(), $e->extractRequestResponse());
            Session::setErrorBag($e->getMessage());
        }
        return "redirect: $this->index";
    }

}
