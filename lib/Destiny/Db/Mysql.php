<?php

namespace Destiny\Db;

use Destiny\Application;
use Destiny\Utils\Options;
use Destiny\Utils\String\Params;
use Destiny\AppException;

class Mysql {
	private $database;
	private $username;
	private $password;
	private $host;
	private $connection;

	function __construct(array $options = null) {
		Options::setOptions ( $this, $options );
	}

	public function select($sql, array $params = array()) {
		return new Result ( $this->query ( $sql, $params ) );
	}

	public function insert($sql, array $params = array()) {
		return (! $this->query ( $sql, $params )) ? false : mysql_insert_id ( $this->getConnection ()->getLink () );
	}

	public function update($sql, array $params = array()) {
		return (! $this->query ( $sql, $params )) ? false : mysql_affected_rows ( $this->getConnection ()->getLink () );
	}

	public function query($sql, array $params = array()) {
		$connection = $this->openConnection ();
		$query = Params::apply ( $sql, $params, true );
		$result = mysql_query ( $query, $connection->getLink () );
		if ($result === false) {
			$log = Application::getInstance ()->getLogger ();
			$log->critical ( mysql_error ( $connection->getLink () ) );
			throw new AppException ( 'A databse error has occurred' );
		}
		return $result;
	}

	public function openConnection() {
		$connection = $this->getConnection ();
		if (empty ( $connection )) {
			$connection = new Connection ( $this->getHost (), $this->getUsername (), $this->getPassword () );
			$connection->connectDb ( $this->getDatabase () );
			$this->setConnection ( $connection );
			$this->query ( 'SET CHARACTER SET utf8' );
			$this->query ( 'SET time_zone = \'+00:00\'' );
		}
		return $connection;
	}

	public function closeConnection() {
		$connection = $this->getConnection ();
		if ($connection != null) {
			$connection->close ();
			$this->setConnection ( null );
		}
	}

	public function getDatabase() {
		return $this->database;
	}

	public function setDatabase($database) {
		$this->database = $database;
	}

	public function getUsername() {
		return $this->username;
	}

	public function setUsername($username) {
		$this->username = $username;
	}

	public function getPassword() {
		return $this->password;
	}

	public function setPassword($password) {
		$this->password = $password;
	}

	public function getHost() {
		return $this->host;
	}

	public function setHost($host) {
		$this->host = $host;
	}

	public function getConnection() {
		return $this->connection;
	}

	public function setConnection($connection) {
		$this->connection = $connection;
	}

}
class Result {
	private $result = null;
	private $data = null;

	public function __construct($result) {
		$this->result = $result;
	}

	public function mapRow($fn) {
		$rows = $this->fetchRows ();
		return (count ( $rows ) > 0) ? call_user_func ( $fn, $rows [0] ) : null;
	}

	public function mapRows($fn) {
		$rows = $this->fetchRows ();
		for($i = 0; $i < count ( $rows ); ++ $i) {
			$rows [$i] = call_user_func ( $fn, $rows [$i] );
		}
		return $rows;
	}

	public function fetchRows() {
		if (empty ( $this->data ) && ($this->result != null && ! is_bool ( $this->result ))) {
			$this->data = array ();
			while ( false != ($assoc = mysql_fetch_assoc ( $this->result )) ) {
				$this->data [] = $assoc;
			}
		}
		return $this->data;
	}

	public function fetchRow() {
		if (empty ( $this->data ) && ($this->result != null && ! is_bool ( $this->result ))) {
			$this->data = ($this->result != null) ? mysql_fetch_assoc ( $this->result ) : array ();
		}
		return $this->data;
	}

	public function fetchValue() {
		if (empty ( $this->data ) && ($this->result != null && ! is_bool ( $this->result ))) {
			$row = ($this->result != null) ? mysql_fetch_row ( $this->result ) : array ();
			if (! empty ( $row ) && isset ( $row [0] )) {
				$this->data = $row [0];
			}
		}
		return $this->data;
	}

}
class Connection {
	private $link;
	private $open;
	private $database;

	public function __construct($host, $username, $password = '') {
		$this->setLink ( $this->open ( $host, $username, $password ) );
	}

	public function open($host, $username, $password) {
		$link = mysql_connect ( $host, $username, $password, true );
		if ($link == null) {
			throw new AppException ( 'Could not open DB connection ' . $username . '@' . $host );
		}
		return $link;
	}

	public function close() {
		if (($link = $this->getLink ()) != false) {
			mysql_close ( $this->getLink () );
		}
	}

	public function connectDb($database) {
		if (! mysql_select_db ( $database, $this->getLink () )) {
			throw new AppException ( 'DB Select failed' );
		}
	}

	public function getLink() {
		return $this->link;
	}

	public function setLink($link) {
		$this->link = $link;
	}

	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

}