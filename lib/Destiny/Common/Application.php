<?php
namespace Destiny\Common;

use Destiny\Common\Session\Session;
use Destiny\Common\Session\SessionCredentials;
use Destiny\Common\Session\SessionInstance;
use Destiny\Common\Utils\Http;
use Destiny\Common\Routing\Route;
use Destiny\Common\Routing\Router;
use Destiny\Common\Utils\RandomString;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\DBAL\Connection;
use function GuzzleHttp\json_encode;

/**
 * @method static Application instance()
 */
class Application extends Service {

    /** @var CacheProvider */
    public $cache1 = null;

    /** @var CacheProvider */
    public $cache2 = null;
    
    /** @var Connection */
    protected $dbal;
    
    /** @var SessionInstance */
    protected $session = null;
    
    /** @var \Redis */
    protected $redis = null;
    
    /** @var Router */
    protected $router;
    
    /** @var callable */
    public $loader;

    /** @return Connection */
    public static function getDbConn(){
        return self::instance()->getDbal();
    }

    /** @return CacheProvider */
    public static function getNsCache(){
        return self::instance()->getCache1();
    }

    /** @return CacheProvider */
    public static function getVerCache(){
        return self::instance()->getCache2();
    }

    /**
     * @param Request $request
     */
    public function executeRequest(Request $request) {
        $response = new Response();
        $route = $this->router->findRoute($request);
        $model = new ViewModel ();

        if ($route === null) {
            $model->code = Http::STATUS_NOT_FOUND;
            $model->error = new Exception('notfound');
            $response->setStatus(Http::STATUS_NOT_FOUND);
            $response->setBody($this->errorTemplate($model));
            $this->handleResponse($response);
        }

        $useResponseAsBody = $route->getResponseBody();
        if ($route->isSecure()) {
            $creds = Session::getCredentials();
            if ($creds->isValid() && strcasecmp($creds->getUserStatus(), 'Active') !== 0) {
                $model->code = Http::STATUS_FORBIDDEN;
                $model->error = new Exception('inactiveuser');
                $response->setStatus(Http::STATUS_FORBIDDEN);
                $response->setBody($this->errorTemplate($model, $useResponseAsBody));
                $this->handleResponse($response);
            }
            if (!$this->hasRouteSecurity($route, $creds, $request)) {
                $model->code = Http::STATUS_FORBIDDEN;
                $model->error = new Exception('forbidden');
                $response->setStatus(Http::STATUS_FORBIDDEN);
                $response->setBody($this->errorTemplate($model, $useResponseAsBody));
                $this->handleResponse($response);
            }
        }

        $url = $route->getUrl();
        if (!empty($url)) {
            $response->setLocation($url);
            $this->handleResponse($response);
        }

        try {
            $result = $this->executeController($route, $request, $response, $model);
            if($useResponseAsBody) {
                // Use result as response body
                $response->setBody($result);
            } else if (is_string($result)) {
                if (substr($result, 0, 9) === 'redirect:') {
                    // Redirect response
                    $redirect = trim(substr($result, 9));
                    $response->setStatus(Http::STATUS_OK);
                    $response->setLocation($redirect);
                } else {
                    // Template response
                    Session::applyBags($model);
                    $response->setStatus(Http::STATUS_OK);
                    $response->setBody($this->template($result . '.php', $model));
                }
            } else if($result !== null) {
                Log::critical("Invalid response " . var_export($result, true));
                throw new Exception('invalidresponse');
            }
        } catch (Exception $e) {
            $id = RandomString::makeUrlSafe(12);
            Log::error("[#$id]" . $e->getMessage() . PHP_EOL . $e->getTraceAsString());
            $model->code = Http::STATUS_ERROR;
            $model->error = $e;
            $model->id = $id;
            $response->setStatus(Http::STATUS_ERROR);
            $response->setBody($this->errorTemplate($model, $useResponseAsBody));
        } catch (\Exception $e) {
            $id = RandomString::makeUrlSafe(12);
            Log::critical("[#$id]" . $e->getMessage() . PHP_EOL . $e->getTraceAsString());
            $model->code = Http::STATUS_ERROR;
            $model->error = new Exception ('Application error', $e);
            $model->id = $id;
            $response->setStatus(Http::STATUS_ERROR);
            $response->setBody($this->errorTemplate($model, $useResponseAsBody));
        }

        $this->handleResponse($response);
    }

    /**
     * @param Response $response
     * @return void
     */
    private function handleResponse(Response $response){
        $location = $response->getLocation();
        if (!empty ($location)) {
            Http::status(Http::STATUS_MOVED_TEMPORARY);
            Http::header(Http::HEADER_LOCATION, $location);
            exit;
        }
        Http::status($response->getStatus());
        $headers = $response->getHeaders();
        foreach ($headers as $header) {
            Http::header($header [0], $header [1]);
        }
        $body = $response->getBody();
        if ($body !== null && !is_string($body)) {
            Http::header(Http::HEADER_CONTENT_TYPE, MimeType::JSON);
            try {
                $body = json_encode($body);
            } catch (\Exception $e) {
                $n = new Exception('Invalid response body.', $e);
                Log::error($n);
            }
        }
        if ($body !== null || $body !== '') {
            echo $body;
        }
        exit;
    }

    /**
     * Runs a controller method
     * Does some magic around what parameters are passed in.
     *
     * @param Route $route
     * @param Request $request
     * @param Response $response
     * @param ViewModel $model
     * @return mixed
     *
     * @throws \Exception
     */
    private function executeController(Route $route, Request $request, Response $response, ViewModel $model) {
        $className = $route->getClass();
        $classMethod = $route->getClassMethod();
        $classReflection = new \ReflectionClass ($className);
        $classInstance = $classReflection->newInstance();
        $methodReflection = $classReflection->getMethod($classMethod);
        $methodParams = $methodReflection->getParameters();
        $args = [];
        foreach ($methodParams as $methodParam) {
            $paramType = $methodParam->getClass();
            if ($methodParam->isArray()) {
                // the $params passed into the Controller classes. A merge of the _GET, _POST and variables generated from the route path (e.g. /dog/{id}/cat)
                $args[] = array_merge(
                    $request->get(),
                    $request->post(),
                    $this->router->getRoutePathParams($route, $request->path())
                );
            } else if ($paramType->isInstance($model)) {
                $args[] = &$model;
            } else if ($paramType->isInstance($request)) {
                $args[] = &$request;
            } else if ($paramType->isInstance($response)) {
                $args[] = &$response;
            }
        }
        return $methodReflection->invokeArgs($classInstance, $args);
    }

    /**
     * @param Route $route
     * @param SessionCredentials $credentials
     * @param Request $request
     * @return bool
     */
    private function hasRouteSecurity(Route $route, SessionCredentials $credentials, Request $request) {
        // has ANY role
        $secure = $route->getSecure();
        if (!empty ($secure)) {
            if (!in_array(true, array_map(function($role) use ($credentials) { return $credentials->hasRole($role); }, $secure))) {
                return false;
            }
        }
        // has ANY feature
        $features = $route->getFeature();
        if (!empty ($features)) {
            if (!in_array(true, array_map(function($feature) use ($credentials) { return $credentials->hasFeature($feature); }, $features))) {
                return false;
            }
        }
        // has ANY private keys
        $keyNames = $route->getPrivateKeys();
        if (!empty($keyNames)) {
            $keyValue = self::getPrivateKeyValueFromRequest($request);
            if (empty($keyValue) || !in_array(true, array_map(function($keyName) use ($keyValue) { return strcmp(Config::$a['privateKeys'][$keyName], $keyValue) === 0; }, $keyNames))) {
                return false;
            }
        }
        return true;
    }

    /**
     * Include a template and return the contents
     *
     * @param string $filename
     * @param ViewModel $model
     * @return string
     * @throws \Exception
     */
    protected function template($filename, ViewModel $model) {
        return $model->getContent($filename);
    }

    /**
     * @param ViewModel $model
     * @param bool $useResponseAsBody
     * @return \Exception|string|array
     */
    protected function errorTemplate(ViewModel $model, $useResponseAsBody = false) {
        try {
            return $useResponseAsBody ? $model : $model->getContent('error.php');
        } catch (\Exception $e) {
            return $e;
        }
    }

    /**
     * @param Request $request
     * @return null|string
     */
    public static function getPrivateKeyValueFromRequest(Request $request) {
        $gets = $request->get();
        $posts = $request->post();
        return isset($gets['privatekey']) ? $gets['privatekey'] : (isset($posts['privatekey']) ? $posts['privatekey'] : null);
    }

    /**
     * @param Request $request
     * @return string|null
     */
    public static function getPrivateKeyNameFromRequest(Request $request){
        $keyValue = self::getPrivateKeyValueFromRequest($request);
        foreach (Config::$a['privateKeys'] as $key => $value) {
            if ($value == $keyValue) {
                return $key;
            }
        }
        return null;
    }

    public function getDbal() {
        return $this->dbal;
    }

    public function setDbal(Connection $dbal) {
        $this->dbal = $dbal;
    }

    public function getCache1() {
        return $this->cache1;
    }

    public function setCache1(CacheProvider $cache) {
        $this->cache1 = $cache;
    }

    public function getCache2() {
        return $this->cache2;
    }

    public function setCache2(CacheProvider $cache) {
        $this->cache2 = $cache;
    }

    public function getSession() {
        return $this->session;
    }

    public function setSession(SessionInstance $session) {
        $this->session = $session;
    }

    public function getRedis() {
        return $this->redis;
    }

    public function setRedis(\Redis $redis) {
        $this->redis = $redis;
    }

    public function getRouter() {
        return $this->router;
    }

    public function setRouter(Router $router) {
        $this->router = $router;
    }

    public function getLoader() {
        return $this->loader;
    }

    public function setLoader($loader) {
        $this->loader = $loader;
    }

}