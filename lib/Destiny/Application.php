<?php

namespace Destiny;

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
	 * DB Connection
	 *
	 * @var \Doctrine\DBAL\Connection
	 */
	protected $connection;

	/**
	 * Return the application
	 *
	 * @return Application
	 */
	public static function instance() {
		return parent::instance ();
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
	 * Get the type of cache
	 *
	 * @todo seems out of place
	 *      
	 * @param array|string $params
	 * @return \Destiny\Cache\Apc
	 */
	public function getMemoryCache($params = null) {
		if ($params === null) {
			throw new \InvalidArgumentException ( $params );
		}
		if (is_string ( $params )) {
			$params = array (
					'filename' => Config::$a ['cache'] ['path'] . $params . '.tmp' 
			);
		}
		return new Config::$a ['cache'] ['memory'] ( $params );
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
			
			// Can't send an empty reponse
			throw new AppException ( 'Invalid action response' );
			
			// Else exit
			ob_flush ();
			exit ();
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
		
		// @todo this needs to be refactored
		// Check if accept type is JSON
		if (isset ( $_SERVER ['HTTP_ACCEPT'] )) {
			$accept = new \HTTP_Accept ( $_SERVER ['HTTP_ACCEPT'] );
			$acceptTypes = $accept->getTypes ();
			if (! empty ( $acceptTypes ) && ! in_array ( MimeType::HTML, $acceptTypes )) {
				if (in_array ( MimeType::JSON, $acceptTypes )) {
					Http::header ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
					Http::sendString ( json_encode ( $model->getData () ) );
				}
			}
		}
		
		$this->logger->debug ( 'Template: ' . $filename );
		ob_clean ();
		ob_start ();
		include $filename;
		ob_flush ();
		exit ();
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

}