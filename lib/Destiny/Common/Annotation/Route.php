<?php
namespace Destiny\Common\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Route {

    public $path = '';

    public function __construct(array $params = null) {
        if (!empty($params)) {
            $this->path = $params['value'];
        }
    }

}