<?php
namespace Destiny\Common\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class ResponseBody {

    /**
     * @param array $params
     */
    public function __construct(array $params) {}

}