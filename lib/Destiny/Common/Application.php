<?php
namespace Destiny\Common;

use Destiny\Common\Utils\Http;
use Destiny\Common\Utils\Tpl;
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
        $route = $this->router->findRoute ( $request );
        $conn = $this->getConnection ();
        $model = new ViewModel ();
        $transactional = false;

        if ( $route == null ) {
            $model->title = Http::$HEADER_STATUSES [Http::STATUS_NOT_FOUND];
            $response = new Response ( Http::STATUS_NOT_FOUND );
            $response->setBody ( $this->template ( 'errors/' . Http::STATUS_NOT_FOUND . '.php', $model ) );
            $this->handleResponse ( $response );
        }

        if( $route->isSecure () ){
            $creds = Session::getCredentials ();
            if($creds->isValid() && strcasecmp ( $creds->getUserStatus(), 'Active' ) !== 0){
                $response = new Response ( Http::STATUS_ERROR );
                $model->error = new Exception ( sprintf ( 'User status not active. Status: %s', $creds->getUserStatus() ) );
                $model->code = Http::STATUS_ERROR;
                $model->title = 'Inactive user';
                $response->setBody ( $this->template ( 'errors/' . Http::STATUS_ERROR . '.php', $model ) );
                $this->handleResponse ( $response );
            }
            if (! $this->hasRouteSecurity ( $route, $creds )) {
                $response = new Response ( Http::STATUS_UNAUTHORIZED );
                $model->title = Http::$HEADER_STATUSES [Http::STATUS_UNAUTHORIZED];
                $response->setBody ( $this->template ( 'errors/' . Http::STATUS_UNAUTHORIZED . '.php', $model ) );
                $this->handleResponse ( $response );
            }
        }

        try {

            $transactional = $route->getTransactional();
            $className = $route->getClass ();
            $classMethod = $route->getClassMethod ();
            $classReflection = new \ReflectionClass ( $className );
            $classInstance = $classReflection->newInstance();

            // Order the controller arguments and invoke the controller
            $args = array();
            $methodReflection = $classReflection->getMethod( $classMethod );
            $methodParams = $methodReflection->getParameters();
            foreach ($methodParams as $methodParam) {
                $paramType = $methodParam->getClass();
                if($methodParam->isArray()){
                    // the $params passed into the Controller classes. A merge of the _GET, _POST and variables generated from the route path (e.g. /dog/{id}/cat)
                    $args[] = array_merge (
                        $request->get(),
                        $request->post(),
                        $this->router->getRoutePathParams ( $route, $request->path() )
                    );
                } else if($paramType->isInstance($model)) {
                    $args[] = &$model;
                } else if($paramType->isInstance($request)) {
                    $args[] = &$request;
                }
            }

            if ($transactional)
                $conn->beginTransaction ();

            // Execute the controller
            $response = $methodReflection->invokeArgs ($classInstance, $args);

            if ($transactional && $conn->isTransactionActive() && !$conn->isRollbackOnly())
                $conn->commit ();
            
            if (empty ( $response ))
                throw new Exception ( 'Invalid action response' );
            
            // Redirect response
            if (is_string ( $response ) && substr ( $response, 0, 10 ) === 'redirect: ') {
                $redirect = substr ( $response, 10 );
                $response = new Response ( Http::STATUS_OK );
                $response->setLocation ( $redirect );
            }
            
            // Template response
            if (is_string ( $response )) {
                $tpl = $response . '.php';
                $response = new Response ( Http::STATUS_OK );
                $response->setBody ( $this->template ( $tpl, $model ) );
            }
            
            if (! $response instanceof Response) {
                throw new Exception ( 'Invalid response' );
            }
            
        } catch ( Exception $e ) {
            
            $this->logger->error ( $e->getMessage () . PHP_EOL . $e->getTraceAsString() );
            if ($transactional && $conn->isTransactionActive()) {
                $conn->rollback ();
            }
            $response = new Response ( Http::STATUS_ERROR );
            $model->error = new Exception ( $e->getMessage () );
            $model->code = Http::STATUS_ERROR;
            $model->title = 'Error';
            $response->setBody ( $this->template ( 'errors/' . Http::STATUS_ERROR . '.php', $model ) );
                
        } catch ( \Exception $e ) {

            $this->logger->critical ( $e->getMessage () . PHP_EOL . $e->getTraceAsString() );
            if ($transactional) {
                $conn->rollback ();
            }
            $response = new Response ( Http::STATUS_ERROR );
            $model->error = new Exception ( 'Maximum over-rustle has been achieved' );
            $model->code = Http::STATUS_ERROR;
            $model->title = 'Error';
            $response->setBody ( $this->template ( 'errors/' . Http::STATUS_ERROR . '.php', $model ) );
        }
        
        $this->handleResponse ( $response );
    }

    /**
     * @param Response $response
     * @throws Exception
     * @return void
     */
    private function handleResponse(Response $response) {
        $location = $response->getLocation ();
        if (! empty ( $location )) {
            Http::header ( Http::HEADER_LOCATION, $location );
            exit ();
        }
        $headers = $response->getHeaders ();
        foreach ( $headers as $header ) {
            Http::header ( $header [0], $header [1] );
        }
        Http::status ( $response->getStatus () );
        $body = $response->getBody ();
        if (! empty ( $body )) {
            echo $body;
        }
        exit ();
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
     * Include a template and return a template file
     *
     * @param string $filename
     * @param ViewModel $model
     * @return string
     * @throws Exception
     */
    protected function template($filename, /** @noinspection PhpUnusedParameterInspection */ ViewModel $model) {
        $filename = Tpl::file ( $filename );
        if (! is_file ( $filename )) {
            throw new Exception ( sprintf ( 'Template not found "%s"', pathinfo ( $filename, PATHINFO_FILENAME ) ) );
        }
        $this->logger->debug ( 'Template: ' . $filename );
        ob_start ();
        /** @noinspection PhpIncludeInspection */
        include $filename;
        $contents = ob_get_contents ();
        ob_end_clean ();
        return $contents;
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