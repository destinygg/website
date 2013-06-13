<?php

namespace Destiny;

use Destiny\Db\Mysql;
use Destiny\ViewModel;
use Destiny\Utils\Http;
use Destiny\Utils\Options;
use Destiny\Utils\String\Params;
use Psr\Log\LoggerInterface;

class Application extends Service {
	
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
	 * The global db object
	 *
	 * @var Destiny\Db\Mysql
	 */
	public $db = null;
	
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
	 * The application
	 *
	 * @var Application
	 */
	protected static $instance = null;

	/**
	 * Return the application
	 *
	 * @return Application
	 */
	public static function getInstance() {
		return parent::getInstance ();
	}

	/**
	 * Construct
	 *
	 * @param array $args
	 */
	public function __construct(array $args = null) {
		if (! isset ( $args ['uri'] ) || empty ( $args ['uri'] )) {
			$args ['uri'] = (isset ( $_SERVER ['REQUEST_URI'] )) ? $_SERVER ['REQUEST_URI'] : '';
		}
		if (! isset ( $args ['path'] ) || empty ( $args ['path'] )) {
			$args ['path'] = parse_url ( $args ['uri'], PHP_URL_PATH );
		}
		if (! isset ( $args ['db'] ) || empty ( $args ['db'] )) {
			$args ['db'] = new Mysql ( Config::$a ['db'] );
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
	private function prepareActionPath($namespace, array $pathinfo) {
		return $namespace . str_replace ( array (
				'/',
				'\\\\' 
		), '\\', $pathinfo ['dirname'] . '\\' ) . $pathinfo ['filename'];
	}

	/**
	 * Bind to a pattern, execute if found, or include a template if $fn is a string
	 *
	 * @param string $pattern
	 * @param callable|string $fn
	 */
	public function bind($pattern, $fn) {
		if (preg_match ( $pattern, $this->path ) > 0) {
			try {
				if (is_callable ( $fn )) {
					$this->logger->debug ( 'Bind(Callable): ' . $this->path );
					$fn ( $this, $this->params );
				}
				if (is_string ( $fn )) {
					$this->logger->debug ( 'Bind(Template): ' . $this->path );
					$this->template ( $fn, new ViewModel () );
				}
			} catch ( AppException $e ) {
				$this->logger->error ( $e->getMessage () );
				$this->error ( 500, $e );
			} catch ( \Exception $e ) {
				$this->logger->critical ( $e->getMessage () );
				$this->error ( 500, $e );
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
		$actionPath = $this->prepareActionPath ( $namespace, $pathinfo );
		if (! class_exists ( $actionPath, true )) {
			$this->logger->debug ( sprintf ( 'BindNamespace: Class not found %s', $actionPath ) );
			$this->error ( 404 );
		}
		try {
			$this->logger->debug ( 'Action: ' . $actionPath );
			$action = new $actionPath ();
			ob_clean ();
			ob_start ();
			$model = new ViewModel ();
			$response = $action->execute ( $this->params, $model );
			
			// if a action returns string, try to load it as a template
			if (is_string ( $response )) {
				$tpl = './tpl/' . $response . '.php';
				if (is_file ( $tpl )) {
					$this->template ( $tpl, $model );
				}
			}
			
			// If the response is here, and we have not outputted data. the user would get a blank screen
			throw new AppException ( 'Invalid action response' );
			
			// Else exit
			ob_flush ();
			exit ();
		} catch ( AppException $e ) {
			$this->logger->error ( $e->getMessage () );
			$this->error ( 500, $e );
		} catch ( \Exception $e ) {
			$this->logger->critical ( $e->getMessage () );
			$this->error ( 500, $e );
		}
	}

	/**
	 * Log and throw a response error
	 * Valid responses are 401,403,404,500,503
	 *
	 * @param string $code
	 * @param function $fn
	 * @param Exception $e
	 */
	public function error($code, $e = null) {
		Http::status ( $code );
		
		// Check if accept type is JSON
		if (isset ( $_SERVER ['HTTP_ACCEPT'] )) {
			$accept = new \HTTP_Accept ( $_SERVER ['HTTP_ACCEPT'] );
			if (! in_array ( Mimetype::HTML, $accept->getTypes () )) {
				if (in_array ( Mimetype::JSON, $accept->getTypes () )) {
					Http::header ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
					Http::sendString ( json_encode ( array (
							'success' => false,
							'data' => null,
							'message' => $e->getMessage () 
					) ) );
				}
			}
		}
		
		$this->template ( './errors/' . $code . '.php', new ViewModel ( array (
				'error' => $e,
				'code' => $code 
		) ) );
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
	 * Get the DBL
	 *
	 * @return Mysql
	 */
	public function getDb() {
		return $this->db;
	}

	/**
	 * Set the DBL
	 *
	 * @param Mysql $db
	 */
	public function setDb($db) {
		$this->db = $db;
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
	 * Include a template and exit
	 *
	 * @param string $filename
	 */
	public function template($filename, ViewModel $model) {
		$this->logger->debug ( 'Template: ' . $filename );
		ob_clean ();
		ob_start ();
		include $filename;
		ob_flush ();
		exit ();
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
	 * Get the type of cache
	 *
	 * @todo seems out of place
	 *      
	 * @param string $filename
	 * @return \Destiny\Cache\Apc
	 */
	public function getMemoryCache($filename = null) {
		$params = array ();
		$params ['filename'] = Config::$a ['cache'] ['path'] . $filename . '.tmp';
		return new Config::$a ['cache'] ['memory'] ( $params );
	}

}