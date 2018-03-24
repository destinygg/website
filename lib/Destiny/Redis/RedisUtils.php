<?php

namespace Destiny\Redis;

use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\Exception;

class RedisUtils {

    /**
     * Loads the given redis script if needed and calls it with the $arguments param
     *
     * @param string $scriptname
     * @param array $argument
     * @return array $users The users found
     * @throws Exception
     */
    public static function callScript($scriptname, $argument) {
        $redis = Application::instance()->getRedis();
        $dir = Config::$a['redis']['scriptdir'];
        $hash = @file_get_contents($dir . $scriptname . '.hash');
        if ($hash) {
            $ret = $redis->evalSha($hash, $argument);
            if ($ret) return $ret;
        }
        $hash = $redis->script('load', file_get_contents($dir . $scriptname . '.lua'));
        if (!$hash) {
            throw new Exception('Unable to load script');
        }
        if (!file_put_contents($dir . $scriptname . '.hash', $hash)) {
            throw new Exception('Unable to save hash');
        }
        return $redis->evalSha($hash, $argument);
    }

}