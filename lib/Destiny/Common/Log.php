<?php
namespace Destiny\Common;

use Monolog\Logger;

class Log {

    /**
     * @var Logger
     */
    public static $log;

    public static function info(string $message, ?array $ctx = []) {
        self::$log->info($message, $ctx ?? []);
    }

    public static function warn(string $message, ?array $ctx = []) {
        self::$log->warning($message, $ctx ?? []);
    }

    public static function notice(string $message, ?array $ctx = []) {
        self::$log->notice($message, $ctx ?? []);
    }

    public static function debug(string $message, ?array $ctx = []) {
        self::$log->debug($message, $ctx ?? []);
    }

    public static function error(string $message, ?array $ctx = []) {
        self::$log->error($message, $ctx ?? []);
    }

    public static function critical(string $message, ?array $ctx = []) {
        self::$log->critical($message, $ctx ?? []);
    }

}
