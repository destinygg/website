<?php
namespace Destiny\Common;

abstract class Config {
    
    /**
     * @var array
     */
    public static $a = array ();

    /**
     * @param array $config
     */
    public static function load(array $config) {
        self::$a = $config;
    }

    /**
     * @param string $protocol
     * @return string
     */
    public static function cdn($protocol = '//') {
        $domain = self::$a ['cdn'] ['domain'];
        $port = (isset(self::$a ['cdn'] ['port'])) ? ':'.self::$a ['cdn'] ['port'] : '';
        return (! empty ( $domain )) ? $protocol . $domain . $port : '';
    }

    /**
     * @param string $protocol
     * @return string
     */
    public static function cdnv($protocol = '//') {
        return self::cdn ( $protocol ) . '/' . Config::version ();
    }

    /**
     * @return string
     */
    public static function version() {
        return self::$a ['version'];
    }

    /**
     * @return string
     */
    public static function userAgent() {
        return "destiny.gg/" . self::version();
    }

}