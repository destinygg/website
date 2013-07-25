<?php
namespace Destiny\Common\Annotation;

/**
 * This annotation relies on the Route annotation being present
 * 
 * @Annotation
 * @Target({"CLASS","METHOD"})
 */
class Secure {
	
	/**
	 * The roles list
	 * @var array<string>
	 */
	public $roles;

	/**
	 * A list of allowed roles
	 * @param array $params
	 */
	public function __construct(array $params) {
		$this->roles = $params ['value'];
	}

}
?>