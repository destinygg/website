<?php
namespace Destiny\Common\Annotation;

/**
 * @Annotation
 * @Target({"CLASS","METHOD"})
 */
class HttpMethod {

    /**
     * @var string[]
     */
    public $allow;

    /**
     * @param array $params
     */
    public function __construct(array $params = null) {
        if (!empty($params)) {
            $this->allow = $params ['value'];
        }
    }

}