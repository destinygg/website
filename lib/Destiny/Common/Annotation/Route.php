<?php
namespace Destiny\Common\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Route {

    /**
     * @var string
     */
    public $path;

    /**
     * @param array $params
     */
    public function __construct(array $params = null) {
        if (!empty($params)) {
            $this->path = $params ['value'];
        }
    }

    /**
     * @return string
     */
    public function getPath() {
        return $this->path;
    }

}