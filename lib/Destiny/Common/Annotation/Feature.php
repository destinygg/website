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
     * @var string[]
     */
    public $features;

    /**
     * @param array $params
     */
    public function __construct(array $params) {
        $this->features = $params ['value'];
    }

}