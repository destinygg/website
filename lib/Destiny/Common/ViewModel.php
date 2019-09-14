<?php
namespace Destiny\Common;

use JsonSerializable;
use stdClass;

class ViewModel extends stdClass implements JsonSerializable {
    
    /**
     * @var array
     */
    protected $vars = [];

    /**
     * ViewModel constructor.
     */
    public function __construct(array $params = null) {
        if (! empty ( $params )) {
            foreach ( $params as $name => $value ) {
                $this->vars [$name] = $value;
            }
        }
    }

    /**
     * TODO figure a way to remove this
     * @throws ViewModelException
     */
    public function getContent(string $filename): string {
        $path = _BASEDIR . '/views/' . $filename;
        $contents = '';
        try {
            ob_start();
            /** @noinspection PhpIncludeInspection */
            require $path;
            $contents = ob_get_contents();
        } catch (\Exception $e) {
            throw new ViewModelException("Exception thrown in template. [$filename]", $e);
        } finally {
            ob_end_clean ();
        }
        return $contents;
    }

    public function getData(): array {
        return $this->vars;
    }

    public function __set(string $name, $value) {
        $this->vars[$name] = $value;
        return $value;
    }

    public function __get(string $name) {
        return (isset ($this->vars [$name])) ? $this->vars [$name] : null;
    }

    public function __isset(string $name): bool {
        return isset ($this->vars [$name]);
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize(): array {
        return $this->getData();
    }
}