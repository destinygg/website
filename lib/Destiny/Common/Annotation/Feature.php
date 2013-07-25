<?php
namespace Destiny\Common\Annotation;

/**
 * This annotation relies on the Route annotation being present
 * 
 * @Annotation
 * @Target({"CLASS","METHOD"})
 */
class Feature {
	
	/**
	 * The features list
	 * @var array<string>
	 */
	public $features;

	/**
	 * A list of allowed features
	 * @param array $params
	 */
	public function __construct(array $params) {
		$this->features = $params ['value'];
	}

}
?>