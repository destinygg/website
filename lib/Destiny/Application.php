<?php
namespace Destiny;

use Destiny\Db\Mysql;
use Destiny\Utils\Http;
use Destiny\Utils\Options;
use Destiny\Utils\String\Params;

class Application extends Service {
	
	public $uri = '';
	public $path = '';
	public $db = null;
	public $params = array ();
	public $exception = null;
	
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
	 * Bind to a pattern, execute if found, or include a template if $fn is a string
	 *
	 * @param string $pattern
	 * @param callable|string $fn
	 */
	public function bind($pattern, $fn) {
		if (preg_match ( $pattern, $this->path ) > 0) {
			try {
				if (is_callable ( $fn )) {
					$fn ( $this, $this->params );
				}
				if (is_string ( $fn )) {
					$this->template ( $fn );
				}
			} catch ( \Exception $e ) {
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
	public function bindNamespace($namespace) {
		$pathinfo = pathinfo ( $this->path );
		if (! empty ( $pathinfo ['filename'] )) {
			// Dirty way to nomalize class path \Namespace\Folder\Class
			$actionPath = $namespace . str_replace ( array ('/','\\\\'), '\\', $pathinfo ['dirname'] . '\\' ) . $pathinfo ['filename'];
			if (class_exists ( $actionPath, true )) {
				try {
					$action = new $actionPath ();
					ob_clean ();
					ob_start ();
					$action->execute ( $this->params );
					ob_flush ();
					exit ();
				} catch ( \Exception $e ) {
					$this->error ( 500, $e );
				}
			}
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
		// Set a copy of the last thrown exception for use in templates
		$this->exception = $e;
		if ($e != null && $code >= Config::$a ['log'] ['level']) {
			$errorLog = new Logger ( Config::$a ['log'] ['path'] . 'error.log' );
			$errorLog->log ( $code . ': ' . $e->getMessage () );
		}
		Http::status ( $code );
		$this->template ( 'errors/' . $code . '.php' );
	}

	/**
	 *
	 * @return the $uri
	 */
	public function getUri() {
		return $this->uri;
	}

	/**
	 *
	 * @param string $uri
	 */
	public function setUri($uri) {
		$this->uri = $uri;
	}

	/**
	 * @return the $path
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * @param string $path
	 */
	public function setPath($path) {
		$this->path = $path;
	}

	/**
	 * @return Mysql
	 */
	public function getDb() {
		return $this->db;
	}

	/**
	 * @param Mysql $db
	 */
	public function setDb($db) {
		$this->db = $db;
	}

	/**
	 * @return array
	 */
	public function getParams() {
		return $this->params;
	}

	/**
	 * @param array $params
	 */
	public function setParams(array $params) {
		$this->params = $params;
	}

	/**
	 * @return \Exception
	 */
	public function getException() {
		if ($this->exception == null) {
			$this->exception = new \Exception ( 'None error' );
		}
		return $this->exception;
	}

	/**
	 * Include a template and exit
	 *
	 * @param string $filename
	 */
	public function template($filename) {
		ob_clean ();
		ob_start ();
		include $filename;
		ob_flush ();
		exit ();
	}

}