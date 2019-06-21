<?php
namespace Destiny\Common;

class Config {

    public static $a = [];

    public static function load(array $config = []) {
        self::$a = $config;
    }

    public static function cdn(): string {
        $domain = self::$a ['cdn'] ['domain'];
        $protocol = self::$a ['cdn'] ['protocol'];
        $port = (isset(self::$a ['cdn'] ['port'])) ? ':' . self::$a ['cdn'] ['port'] : '';
        return (!empty ($domain)) ? $protocol . $domain . $port : '';
    }

    public static function cdnv(): string {
        return self::cdn() . '/' . Config::version();
    }

    public static function cdni(): string {
        return self::cdn() . self::$a['images']['uri'];
    }

    public static function version(): string {
        return self::$a ['version'];
    }

    public static function userAgent(): string {
        return self::$a['meta']['shortName'] . "/" . self::version();
    }

}