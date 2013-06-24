<?php

namespace Destiny;

use Destiny\ViewModel;
use Destiny\Utils\Http;
use Destiny\Utils\Options;
use Destiny\Utils\String\Params;
use Psr\Log\LoggerInterface;
use Doctrine\Common\Cache\CacheProvider;
use \PHPMailer;

class Application extends Service {
	
	/**
	 * The application
	 *
	 * @var Application
	 */
	protected static $instance = null;
	
	/**
	 * The current full url
	 *
	 * @var string
	 */
	public $uri = '';
	
	/**
	 * The current URL path
	 *
	 * @var string
	 */
	public $path = '';
	
	/**
	 * _REQUEST variables, as well as mapped request variables
	 *
	 * @var array
	 */
	public $params = array ();
	
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
		if (! isset ( $args ['uri'] ) || empty ( $args ['uri'] )) {
			$args ['uri'] = (isset ( $_SERVER ['REQUEST_URI'] )) ? $_SERVER ['REQUEST_URI'] : '';
		}
		if (! isset ( $args ['path'] ) || empty ( $args ['path'] )) {
			$args ['path'] = parse_url ( $args ['uri'], PHP_URL_PATH );
		}
		$this->params = array_merge ( $_GET, $_POST );
		Options::setOptions ( $this, $args );
	}

	/**
	 * Dirty way to nomalize class path \Namespace\Folder\Class
	 * Make a url / path request a class / namespace path
	 *
	 * @param string $namespace
	 * @param array $pathinfo
	 * @return string
	 */
	public function prepareActionPath($namespace, array $pathinfo) {
		$arr = explode ( '/', $pathinfo ['dirname'] );
		foreach ( $arr as $i => $v ) {
			$arr [$i] = ucfirst ( $v );
		}
		return $namespace . str_replace ( array (
				'/',
				'\\\\' 
		), '\\', join ( '\\', $arr ) . '\\' ) . ucwords ( $pathinfo ['filename'] );
	}

	/**
	 * Bind to a pattern, execute if found, or include a template if $fn is a string
	 *
	 * @param string $pattern
	 * @param callable $fn
	 */
	public function bind($pattern, $fn) {
		$pathParams = array ();
		if (preg_match ( $pattern, $this->path, $pathParams ) > 0) {
			try {
				array_shift ( $pathParams );
				$params = array_merge ( $this->params, $pathParams );
				$this->logger->debug ( 'Bind(Callable): ' . $this->path );
				$fn ( $this, $params );
			} catch ( AppException $e ) {
				$this->logger->error ( $e->getMessage () );
				$this->error ( Http::STATUS_ERROR, $e );
			} catch ( \Exception $e ) {
				$this->logger->critical ( $e->getMessage () );
				$this->error ( Http::STATUS_ERROR, new \Exception ( 'Something went real wrong..' ) );
			}
		}
	}

	/**
	 * Converts the URL path to a class path e.g.
	 * $namespace\Folder\Class
	 * Executes the action if found
	 *
	 * @param string $namespace
	 */
	public function bindNamespace($namespace, $default = null) {
		$pathinfo = pathinfo ( $this->path );
		if (empty ( $pathinfo ['filename'] ) && ! empty ( $default )) {
			$pathinfo ['filename'] = $default;
		}
		$className = $this->prepareActionPath ( $namespace, $pathinfo );
		if (! class_exists ( $className, true )) {
			$this->logger->debug ( sprintf ( 'BindNamespace: Class not found %s', $className ) );
			$this->error ( 404 );
		}
		$this->executeAction ( new $className (), $this->params );
	}

	/**
	 * Execute an action
	 *
	 * @param obj $className
	 * @param array $params
	 * @throws AppException
	 */
	public function executeAction($class, array $params) {
		try {
			$this->logger->debug ( 'Action: ' . get_class ( $class ) );
			$action = new $class ();
			$model = new ViewModel ();
			
			// Tries to run "executeGET | executePOST | execute" on action
			if (method_exists ( $action, 'execute' . $_SERVER ['REQUEST_METHOD'] )) {
				$response = $action->{'execute' . $_SERVER ['REQUEST_METHOD']} ( $params, $model );
			} elseif (method_exists ( $action, 'execute' )) {
				$response = $action->execute ( $params, $model );
			} else {
				throw new AppException ( 'Action method not found' );
			}
			
			// if a action returns string, try to load it as a template
			if (is_string ( $response )) {
				$tpl = './tpl/' . $response . '.php';
				if (! is_file ( $tpl )) {
					throw new AppException ( sprintf ( 'Template not found "%s"', pathinfo ( $tpl, PATHINFO_FILENAME ) ) );
				}
				$this->template ( $tpl, $model );
			}
			
			// Can't send an empty reponse
			throw new AppException ( 'Invalid action response' );
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
		$this->template ( './errors/' . $code . '.php', new ViewModel ( array (
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
		@ob_clean ();
		ob_start ();
		include $filename;
		ob_flush ();
		exit ();
	}

	/**
	 * Parse and return a template
	 *
	 * @param string $filename
	 * @param ViewModel $model
	 * @return string
	 */
	public function templateContent($filename, ViewModel $model) {
		$this->logger->debug ( 'Template: ' . $filename );
		@ob_clean ();
		ob_start ();
		include $filename;
		$content = ob_get_contents ();
		ob_end_clean ();
		return $content;
	}

	/**
	 * Get the URL
	 *
	 * @return the $uri
	 */
	public function getUri() {
		return $this->uri;
	}

	/**
	 * Set the URL
	 *
	 * @param string $uri
	 */
	public function setUri($uri) {
		$this->uri = $uri;
	}

	/**
	 * Get the path
	 *
	 * @return the $path
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * Set the path
	 *
	 * @param string $path
	 */
	public function setPath($path) {
		$this->path = $path;
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
	 * Get request params
	 *
	 * @return array
	 */
	public function getParams() {
		return $this->params;
	}

	/**
	 * Set request params
	 *
	 * @param array $params
	 */
	public function setParams(array $params) {
		$this->params = $params;
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

}