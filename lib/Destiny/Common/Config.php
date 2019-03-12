<?php
namespace Destiny\Common;

class Config {

    /**
     * @var array
     */
    public static $a = [];

    /**
     * @param array $config
     */
    public static function load(array $config) {
        self::$a = $config;
    }

    /**
     * @return string
     */
    public static function cdn() {
        $domain = self::$a ['cdn'] ['domain'];
        $protocol = self::$a ['cdn'] ['protocol'];
        $port = (isset(self::$a ['cdn'] ['port'])) ? ':' . self::$a ['cdn'] ['port'] : '';
        return (!empty ($domain)) ? $protocol . $domain . $port : '';
    }

    /**
     * @return string
     */
    public static function cdnv() {
        return self::cdn() . '/' . Config::version();
    }

    /**
     * @return string
     */
    public static function cdni() {
        return self::cdn() . self::$a['images']['uri'];
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
        return self::$a['meta']['shortName'] . "/" . self::version();
    }

}