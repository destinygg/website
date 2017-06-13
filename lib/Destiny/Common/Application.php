<?php
namespace Destiny\Common;

use Destiny\Common\Utils\Http;
use Destiny\Common\Routing\Route;
use Destiny\Common\Routing\Router;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;

/**
 * @method static Application instance()
 */
class Application extends Service {

    /**
     * @var LoggerInterface
     */
    public $logger = null;
    
    /**
     * @var CacheProvider
     */
    public $cacheDriver = null;
    
    /**
     * @var Connection
     */
    protected $connection;
    
    /**
     * @var SessionInstance
     */
    protected $session = null;
    
    /**
     * @var \Redis
     */
    protected $redis = null;
    
    /**
     * @var Router
     */
    protected $router;
    
    /**
     * @var Reader
     */
    protected $annotationReader;
    
    /**
     * @var callable
     */
    public $loader;

    /**
     * @param Request $request
     */
    public function executeRequest(Request $request) {
        $response = new Response();
        $response->setStatus(Http::STATUS_OK);
        $route = $this->router->findRoute($request);
        $model = new ViewModel ();

        if ($route === null) {
            $model->code = Http::STATUS_NOT_FOUND;
            $model->error = new Exception('notfound');
            $response->setStatus(Http::STATUS_NOT_FOUND);
            $response->setBody($this->template('error.php', $model));
            $this->handleResponse($response);
        }

        $useResponseAsBody = $route->getResponseBody();
        if ($route->isSecure()) {
            $creds = Session::getCredentials();
            if ($creds->isValid() && strcasecmp($creds->getUserStatus(), 'Active') !== 0) {
                $model->code = Http::STATUS_FORBIDDEN;
                $model->error = new Exception('inactiveuser');
                $response->setStatus(Http::STATUS_FORBIDDEN);
                $response->setBody(!$useResponseAsBody ? $this->template('error.php', $model) : $model);
                $this->handleResponse($response);
            }
            if (!$this->hasRouteSecurity($route, $creds)) {
                $model->code = Http::STATUS_FORBIDDEN;
                $model->error = new Exception('forbidden');
                $response->setStatus(Http::STATUS_FORBIDDEN);
                $response->setBody(!$useResponseAsBody ? $this->template('error.php', $model) : $model);
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
                if (substr($result, 0, 10) === 'redirect: ') {
                    // Redirect response
                    $redirect = substr($result, 10);
                    $response->setStatus(Http::STATUS_OK);
                    $response->setLocation($redirect);
                } else {
                    // Template response
                    Session::applyBags($model);
                    $response->setStatus(Http::STATUS_OK);
                    $response->setBody($this->template($result . '.php', $model));
                }
            } else if($result !== null) {
                $this->logger->critical($result);
                throw new Exception('invalidresponse');
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            $model->code = Http::STATUS_ERROR;
            $model->error = new Exception ($e->getMessage());
            $response->setStatus(Http::STATUS_ERROR);
            $response->setBody(!$useResponseAsBody ? $this->template('error.php', $model) : $model);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            $model->code = Http::STATUS_ERROR;
            $model->error = new Exception ('Application error');
            $response->setStatus(Http::STATUS_ERROR);
            $response->setBody(!$useResponseAsBody ? $this->template('error.php', $model) : $model);
        }

        $this->handleResponse($response);
    }

    /**
     * @param Response $response
     * @throws Exception
     * @return void
     */
    private function handleResponse(Response $response){
        $location = $response->getLocation();
        if (!empty ($location)) {
            Http::header(Http::HEADER_LOCATION, $location);
            exit;
        }
        Http::status($response->getStatus());
        $headers = $response->getHeaders();
        foreach ($headers as $header) {
            Http::header($header [0], $header [1]);
        }
        $body = $response->getBody();
        if($body !== null && !is_string($body)) {
            Http::header(Http::HEADER_CONTENTTYPE, MimeType::JSON);
            $body = json_encode($body);
        }
        if($body !== null || $body !== ''){
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
     */
    private function executeController(Route $route, Request $request, Response $response, ViewModel $model) {
        $className = $route->getClass();
        $classMethod = $route->getClassMethod();
        $classReflection = new \ReflectionClass ($className);
        $classInstance = $classReflection->newInstance();
        $args = [];
        $methodReflection = $classReflection->getMethod($classMethod);
        $methodParams = $methodReflection->getParameters();
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
     * @return bool
     */
    private function hasRouteSecurity(Route $route, SessionCredentials $credentials) {
        $secure = $route->getSecure ();
        if (! empty ( $secure )) {
            foreach ( $secure as $role ) {
                if (! $credentials->hasRole ( $role )) {
                    return false;
                }
            }
        }
        $features = $route->getFeature ();
        if (! empty ( $features )) {
            foreach ( $features as $feature ) {
                if (! $credentials->hasFeature ( $feature )) {
                    return false;
                }
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
     */
    protected function template($filename, /** @noinspection PhpUnusedParameterInspection */ ViewModel $model) {
        return $model->getContent($filename);
    }

    public function getConnection() {
        return $this->connection;
    }

    public function setConnection(Connection $connection) {
        $this->connection = $connection;
    }

    public function getCacheDriver() {
        return $this->cacheDriver;
    }

    public function setCacheDriver(CacheProvider $cacheDriver) {
        $this->cacheDriver = $cacheDriver;
    }

    public function getLogger() {
        return $this->logger;
    }

    public function setLogger(LoggerInterface $logger) {
        $this->logger = $logger;
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

    public function getAnnotationReader() {
        return $this->annotationReader;
    }

    public function setAnnotationReader(Reader $annotationReader) {
        $this->annotationReader = $annotationReader;
    }

}