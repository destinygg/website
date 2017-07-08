<?php
namespace Destiny\Chat;

use Destiny\Common\Application;
use Destiny\Common\Config;

class ChatEmotes {

    private static $emotes = array ();

    /**
     * @param string $type twitch|destiny
     * @return array|null
     */
    public static function get($type=null){
        if (self::$emotes == null) {
            $cache = Application::instance()->getCache();
            $key = 'emotes_' . str_replace('.', '', Config::$a['version']);
            $emotes = $cache->fetch($key);
            if (empty ($emotes)) {
                $emotes = json_decode(file_get_contents(_BASEDIR . '/assets/emotes.json'), true);
                $cache->save($key, $emotes, 86400);
            }
            if (is_array($emotes)) {
                self::$emotes = $emotes;
            }
        }
        return $type !== null ? self::$emotes[$type] : self::$emotes;
    }

    /**
     * @param $type
     * @return string
     */
    public static function random($type){
        $e = self::get($type);
        return $e[array_rand($e)];
    }

}