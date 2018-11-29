<?php
namespace Destiny\Common;

use Monolog\Logger;

class Log {

    /**
     * @var Logger
     */
    public static $log;

    /**
     * @param string $message
     * @param array $ctx
     */
    public static function info($message, array $ctx = []){
        self::$log->info($message, $ctx);
    }

    /**
     * @param string $message
     * @param array $ctx
     */
    public static function warn($message, array $ctx = []){
        self::$log->warning($message, $ctx);
    }

    /**
     * @param string $message
     * @param array $ctx
     */
    public static function notice($message, array $ctx = []){
        self::$log->notice($message, $ctx);
    }

    /**
     * @param string $message
     * @param array $ctx
     */
    public static function debug($message, array $ctx = []){
        self::$log->debug($message, $ctx);
    }

    /**
     * @param string $message
     * @param array $ctx
     */
    public static function error($message, array $ctx = []){
        self::$log->error($message, $ctx);
    }

    /**
     * @param string $message
     * @param array $ctx
     */
    public static function critical($message, array $ctx = []){
        self::$log->critical($message, $ctx);
    }

}