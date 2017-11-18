<?php
namespace Destiny\Common;

class ViewModel extends \stdClass {
    
    /**
     * @var array
     */
    protected $vars = [];

    /**
     * ViewModel constructor.
     * @param array|null $params
     */
    public function __construct(array $params = null) {
        if (! empty ( $params )) {
            foreach ( $params as $name => $value ) {
                $this->vars [$name] = $value;
            }
        }
    }

    /**
     * @param $filename
     * @return string
     * @throws \Exception
     */
    public function getContent($filename){
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

    /**
     * @return array
     */
    public function getData() {
        return $this->vars;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public function __set($name, $value) {
        $this->vars [$name] = $value;
        return $value;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function __get($name) {
        return (isset ( $this->vars [$name] )) ? $this->vars [$name] : null;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name) {
        return isset ( $this->vars [$name] );
    }

}