<?php
namespace Destiny\Common;

use Destiny\Common\Utils\Http;
use Destiny\Common\Utils\Options;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Routing\Route;
use Destiny\Common\Routing\Router;
use Doctrine\Common\Annotations\Reader;
use Psr\Log\LoggerInterface;

class Application extends Service {
    
    /**
     * The application
     *
     * @var Application
     */
    protected static $instance = null;
    
    /**
     * Public logger
     *
     * @var LoggerInterface
     */
    public $logger = null;
    
    /**
     * Public logger
     *
     * @var \Doctrine\DBAL\Cache
     */
    public $cacheDriver = null;
    
    /**
     * DB Connection
     *
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;
    
    /**
     * The current session api
     *
     * @var SessionInstance
     */
    protected $session = null;
    
    /**
     * A connected redis instance
     *
     * @var \Redis
     */
    protected $redis = null;
    
    /**
     * The request router
     * @var Router
     */
    protected $router;
    
    /**
     * The request router
     * @var Reader
     */
    protected $annotationReader;
    
    /**
     * The autoloader
     * @var callable
     */
    public $loader;

    /**
     * Since this has to be created instance, only returns never creates
     *
     * @return Application
     */
    public static function instance() {
      return static::$instance;
    }

    /**
     * Construct
     *
     * @param array $args
     */
    public function __construct(array $args= null) {
      self::$instance = $this;
      Options::setOptions ( $this, $args );
    }

    /**
     * Executes the action if a route is found
     */
    public function executeRequest(Request $request) {
      
      $route = $this->router->findRoute ( $request );
      
      $model = new ViewModel ();
      $response = null;
      
      // Maintenance mode
      if(Config::$a['maintenance']){
        $response = new Response ( Http::STATUS_SERVICE_UNAVAILABLE );
        $response->setBody ( $this->template ( 'maintenance.php', $model ) );
        $this->handleResponse ( $response );
      }
      
      // No route found
      if (! $route) {
        $response = new Response ( Http::STATUS_NOT_FOUND );
        $response->setBody ( $this->template ( 'errors/' . Http::STATUS_NOT_FOUND . '.php', $model ) );
        $this->handleResponse ( $response );
      }
      
      // Security checks
      if (! $this->hasRouteSecurity ( $route, Session::getCredentials () )) {
        $response = new Response ( Http::STATUS_UNAUTHORIZED );
        $response->setBody ( $this->template ( 'errors/' . Http::STATUS_UNAUTHORIZED . '.php', $model ) );
        $this->handleResponse ( $response );
      }
      
      try {
        
        // Parameters
        $params = array_merge ( $_GET, $_POST, $route->getPathParams ( $request->path() ) );
        
        // Get and init action class
        $className = $route->getClass ();
        $classMethod = $route->getClassMethod ();
        
        // Init the action class instance
        $classInstance = new $className ();
        
        // Check for @Transactional annotation
        $annotationReader = $this->getAnnotationReader ();
        $transactional = $annotationReader->getMethodAnnotation ( new \ReflectionMethod ( $classInstance, $classMethod ), 'Destiny\Common\Annotation\Transactional' );
        $transactional = (empty($transactional)) ? false : true;

        // If transactional begin a DB transaction before the action begins
        if ($transactional) {
          $conn = $this->getConnection ();
          $conn->beginTransaction ();
        }
          
        // Execute the class method
        $response = $classInstance->$classMethod ( $params, $model, $request );
          
        // Log any errors on the model
        // @TODO neaten this implementation up - better than logging everywhere else
        ///if (! empty ( $model->error ) && is_a ( $model->error, 'Exception' )) {
        /// $this->logger->error ( $model->error->getMessage () );
        //}
        // Check if the response is valid
        if (empty ( $response )) {
          throw new Exception ( 'Invalid action response' );
        }
        
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
        
        // Check the response type
        if (! $response instanceof Response) {
          throw new Exception ( 'Invalid response' );
        }
        
        // Commit the DB transaction
        if ($transactional) {
          $conn->commit ();
        }
        //
      } catch ( Exception $e ) {
        // Destiny\Exceptions are caught and displayed
        
        $this->logger->error ( $e->getMessage () );
        
        if ($transactional) {
          $conn->rollback ();
        }
        
        $response = new Response ( Http::STATUS_ERROR );
        $model->error = new Exception ( $e->getMessage () );
        $model->code = Http::STATUS_ERROR;

        $response->setBody ( $this->template ( 'errors/' . Http::STATUS_ERROR . '.php', $model ) );
          
      } catch ( \Exception $e ) {
        // \Exceptions are caught and generic message is shown
          
        $this->logger->critical ( $e->getMessage () );
        
        if ($transactional) {
          $conn->rollback ();
        }
        
        $response = new Response ( Http::STATUS_ERROR );
        $model->error = new Exception ( 'Maximum over-rustle has been achieved' );
        $model->code = Http::STATUS_ERROR;

        $response->setBody ( $this->template ( 'errors/' . Http::STATUS_ERROR . '.php', $model ) );

      }
      
      // Handle the request response
      $this->handleResponse ( $response );
    }

    /**
     * Handle the Response response
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
     * Check if the security credentials have the correct values for the route
     * @param Route $route
     * @param Route $credentials
     * @return boolean
     */
    private function hasRouteSecurity(Route $route, SessionCredentials $credentials) {
        // Check the route security against the user roles and features
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
     * @return string
     */
    protected function template($filename, ViewModel $model) {
        $filename = Tpl::file ( $filename );
        if (! is_file ( $filename )) {
            throw new Exception ( sprintf ( 'Template not found "%s"', pathinfo ( $filename, PATHINFO_FILENAME ) ) );
        }
        $this->logger->debug ( 'Template: ' . $filename );
        ob_start ();
        include $filename;
        $contents = ob_get_contents ();
        ob_end_clean ();
        return $contents;
    }

    /**
     * Get the active connection
     *
     * @return \Doctrine\DBAL\Connection
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * Set the active connection
     *
     * @param \Doctrine\DBAL\Connection $connection
     */
    public function setConnection(\Doctrine\DBAL\Connection $connection) {
        $this->connection = $connection;
    }

    /**
     * Get the active connection
     *
     * @return \Doctrine\Common\Cache\CacheProvider
     */
    public function getCacheDriver() {
        return $this->cacheDriver;
    }

    /**
     * Set the active connection
     *
     * @param \Doctrine\Common\Cache\CacheProvider $cacheDriver
     */
    public function setCacheDriver(\Doctrine\Common\Cache\CacheProvider $cacheDriver) {
        $this->cacheDriver = $cacheDriver;
    }

    /**
     * Set logger
     *
     * @return LoggerInterface
     */
    public function getLogger() {
        return $this->logger;
    }

    /**
     * Get logger
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger) {
        $this->logger = $logger;
    }

    /**
     * Get the session api
     *
     * @return \Destiny\SessionInstance
     */
    public function getSession() {
        return $this->session;
    }

    /**
     * Set the session api
     *
     * @param SessionInstance $session
     */
    public function setSession(SessionInstance $session) {
        $this->session = $session;
    }


    /**
     * Get the redis instance
     *
     * @return Redis
     */
    public function getRedis() {
        return $this->redis;
    }

    /**
     * Set the redis instance
     *
     * @param Redis $redis
     */
    public function setRedis(\Redis $redis) {
        $this->redis = $redis;
    }

    /**
     * Get the request router
     * @return Router
     */
    public function getRouter() {
        return $this->router;
    }

    /**
     * Set the request router
     * @param Destiny\Router $router
     */
    public function setRouter(Router $router) {
        $this->router = $router;
    }

    /**
     * Get the autoloader
     * @return callable
     */
    public function getLoader() {
        return $this->loader;
    }

    /**
     * Set the autoloader
     * @param callable $loader
     */
    public function setLoader($loader) {
        $this->loader = $loader;
    }

    /**
     * Get the annotation reader
     * @return Reader
     */
    public function getAnnotationReader() {
        return $this->annotationReader;
    }

    /**
     * Set the annotation reader
     * @param Reader $annotationReader
     */
    public function setAnnotationReader(Reader $annotationReader) {
        $this->annotationReader = $annotationReader;
    }

}