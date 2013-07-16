<?php
namespace Destiny\Common\Annotation;

/**
 * @Annotation
 * @Target({"CLASS","METHOD"})
 */
class Route {
	
	/**
	 * The URL path
	 * @var string
	 */
	public $path;

	/**
	 * The annotation params
	 * @param array $params
	 */
	public function __construct(array $params) {
		$this->path = $params ['value'];
	}

}
?>