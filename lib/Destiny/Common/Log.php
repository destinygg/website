<?php
namespace Destiny\Common;

class Log {

    private static function logger() {
        return Application::instance()->getLogger();
    }

    public static function info($message, array $context = array()){
        self::logger()->info($message, $context);
    }

    public static function warn($message, array $context = array()){
        self::logger()->warning($message, $context);
    }

    public static function notice($message, array $context = array()){
        self::logger()->notice($message, $context);
    }

    public static function debug($message, array $context = array()){
        self::logger()->debug($message, $context);
    }

    public static function error($message, array $context = array()){
        self::logger()->error($message, $context);
    }

    public static function critical($message, array $context = array()){
        self::logger()->critical($message, $context);
    }

}