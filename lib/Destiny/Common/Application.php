<?php
namespace Destiny\Common;

use Destiny\Common\ViewModel;
use Destiny\Common\Utils\Http;
use Destiny\Common\Utils\Options;
use Destiny\Common\Utils\String\Params;
use Destiny\Common\Router;
use Doctrine\Common\Cache\CacheProvider;
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
	 * A list of events to show the user
	 *
	 * @array
	 */
	protected $events = array ();
	
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
	public function __construct(array $args = null) {
		self::$instance = $this;
		Options::setOptions ( $this, $args );
	}

	private function checkRouteSecurity(Route $route) {
		// Check the route security against the user roles and features
		$credentials = Session::getCredentials ();
		$secure = $route->getSecure ();
		if (! empty ( $secure )) {
			foreach ( $secure as $role ) {
				if (! $credentials->hasRole ( $role )) {
					return $this->error ( Http::STATUS_UNAUTHORIZED );
				}
			}
		}
		$features = $route->getFeature ();
		if (! empty ( $features )) {
			foreach ( $features as $feature ) {
				if (! $credentials->hasFeature ( $feature )) {
					return $this->error ( Http::STATUS_UNAUTHORIZED );
				}
			}
		}
	}

	/**
	 * Executes the action if a route is found
	 */
	public function executeRequest($uri, $method) {
		$path = parse_url ( $uri, PHP_URL_PATH );
		$route = $this->router->findRoute ( $path, $method );
		
		// No route found
		if (! $route) {
			return $this->error ( Http::STATUS_NOT_FOUND );
		}
		
		// Security checks
		$this->checkRouteSecurity ( $route );
		
		// Combine the get, post and the {param}'s from the string
		$params = array_merge ( $_GET, $_POST, $route->getPathParams ( $path ) );
		
		try {
			// Get and init action class
			$className = $route->getClass ();
			$classMethod = $route->getClassMethod ();
			$model = new ViewModel ();
			$classInstance = new $className ();
			
			// Begin a DB transaction before the action begins
			$connection = $this->getConnection ();
			$connection->beginTransaction ();
			
			// Execute the method, and handle the response
			$response = $classInstance->$classMethod ( $params, $model );
			
			// Commit the DB transaction
			$connection->commit ();
			
			// Check if the response is valid
			if (empty ( $response ) || ! is_string ( $response )) {
				$this->error ( Http::STATUS_NO_CONTENT, new AppException ( 'Invalid response' ) );
			}
			
			// Redirect response
			if (substr ( $response, 0, 10 ) === 'redirect: ') {
				$redirect = substr ( $response, 10 );
				Http::header ( Http::HEADER_LOCATION, substr ( $response, 10 ) );
				return;
			}
			
			// Template response
			$tpl = './tpl/' . $response . '.php';
			if (! is_file ( $tpl )) {
				throw new AppException ( sprintf ( 'Template not found "%s"', pathinfo ( $tpl, PATHINFO_FILENAME ) ) );
			}
			$this->template ( $tpl, $model );
			//
		} catch ( AppException $e ) {
			$this->logger->error ( $e->getMessage () );
			$this->error ( Http::STATUS_ERROR, $e );
		} catch ( \Exception $e ) {
			$this->logger->critical ( $e->getMessage () );
			$this->error ( Http::STATUS_ERROR, new AppException ( 'Maximum over-rustle has been achieved' ) );
		}
	}

	/**
	 * Log and throw a response error
	 * Valid responses are 401,403,404,500
	 *
	 * @param string $code
	 * @param function $fn
	 * @param Exception $e
	 */
	public function error($code, $e = null) {
		Http::status ( $code );
		$this->template ( './tpl/errors/' . $code . '.php', new ViewModel ( array (
			'error' => $e,
			'code' => $code 
		) ) );
	}

	/**
	 * Include a template and exit
	 *
	 * @param string $filename
	 */
	public function template($filename, ViewModel $model) {
		$this->logger->debug ( 'Template: ' . $filename );
		if (Config::$a ['cleanOutputBuffer']) {
			// Catches warnings and output before this points and clears it
			@ob_clean ();
		}
		ob_start ();
		include $filename;
		ob_flush ();
		exit ();
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
	 * Add to the list of events
	 *
	 * @param AppEvent $event
	 */
	public function addEvent(AppEvent $event) {
		$this->events [] = $event;
	}

	/**
	 * Get a list of events that have been raised
	 *
	 * @return array
	 */
	public function getEvents() {
		return $this->events;
	}

	/**
	 * Set the list of events
	 *
	 * @param array $events
	 */
	public function setEvents($events) {
		$this->events = $events;
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