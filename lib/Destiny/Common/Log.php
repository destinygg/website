<?php
namespace Destiny\Common;

class Log {

    private static function logger() {
        return Application::instance()->getLogger();
    }

    public static function info($message, array $ctx = []){
        self::logger()->info($message, $ctx);
    }

    public static function warn($message, array $ctx = []){
        self::logger()->warning($message, $ctx);
    }

    public static function notice($message, array $ctx = []){
        self::logger()->notice($message, $ctx);
    }

    public static function debug($message, array $ctx = []){
        self::logger()->debug($message, $ctx);
    }

    public static function error($message, array $ctx = []){
        self::logger()->error($message, $ctx);
    }

    public static function critical($message, array $ctx = []){
        self::logger()->critical($message, $ctx);
    }

}