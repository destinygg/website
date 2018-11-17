<?php
namespace Destiny\Common\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class PrivateKey {

    /**
     * @var string[]
     */
    public $names;

    /**
     * @param array $params
     */
    public function __construct(array $params = null) {
        if(!empty($params)) {
            $this->names = $params ['value'];
        }
    }

    /**
     * @return string[]
     */
    public function getNames(){
        return $this->names;
    }

}