<?php
namespace Destiny\Annotation;

/**
 * @Annotation
 * @Target({"CLASS","METHOD"})
 */
class HttpMethod {
	
	/**
	 * A list of allowed HTTP methods
	 * @var array<string>
	 */
	public $allow;

	/**
	 * A list of allowed methods
	 * @param array $params
	 */
	public function __construct(array $params) {
		$this->allow = $params ['value'];
	}

}