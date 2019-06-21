<?php
namespace Destiny\Common\Annotation;

/**
 * @Annotation
 * @Target({"CLASS","METHOD"})
 */
class Schedule {

    /**
     * @var int
     */
    public $frequency;

    /**
     * @var string
     */
    public $period;

    public function __construct(array $params = null) {
        if(!empty($params)) {
            $this->frequency = $params ['frequency'];
            $this->period = $params ['period'];
        }
    }

}