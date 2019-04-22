<?php
namespace Destiny\Common;

use JsonSerializable;

class Exception extends \Exception implements JsonSerializable {

    public function __construct($message = "", \Exception $previous = null) {
        parent::__construct($message, 0, $previous);
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize() {
        return $this->getMessage();
    }

}