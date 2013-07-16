<?php
namespace Destiny\Common\Annotation;

/**
 * @Annotation
 * @Target({"CLASS","METHOD"})
 */
class Secure {
	
	/**
	 * The URL path
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