<?php
namespace Destiny\Chat;

use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\Log;
use Destiny\Common\Service;
use Destiny\Common\Utils\Date;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Exception;
use PDO;

/**
 * @method static EmoteService instance()
 */
class EmoteService extends Service {

    const EMOTES_DIR = _BASEDIR . '/static/emotes/';

    /**
     * @param $id
     * @param array $emote
     * @throws DBALException
     */
    public function updateEmote($id, array $emote) {
        $conn = Application::getDbConn();
        $conn->update('emotes', [
            'imageId' => $emote['imageId'],
            'prefix' => $emote['prefix'],
            'twitch' => $emote['twitch'],
            'draft' => $emote['draft'],
            'styles' => $emote['styles'],
            'modifiedDate' => Date::getSqlDateTime()
        ], ['id' => $id]);
    }

    /**
     * @param array $emote
     * @return int
     * @throws DBALException
     */
    public function insertEmote(array $emote) {
        $conn = Application::getDbConn();
        $conn->insert('emotes', [
            'imageId' => $emote['imageId'],
            'prefix' => $emote['prefix'],
            'twitch' => $emote['twitch'],
            'draft' => $emote['draft'],
            'styles' => $emote['styles'],
            'createdDate' => Date::getSqlDateTime(),
            'modifiedDate' => Date::getSqlDateTime()
        ]);
        return intval($conn->lastInsertId());
    }

    /**
     * @param $id
     * @throws InvalidArgumentException
     * @throws DBALException
     */
    function removeEmoteById($id) {
        $conn = Application::getDbConn();
        $conn->delete('emotes', ['id' => $id]);
    }

    /**
     * @return mixed
     * @throws DBALException
     */
    function findAllEmotes() {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare('
            SELECT 
              e.*, 
              i.name as `imageName`, 
              i.label as `imageLabel`, 
              i.size, 
              i.width, 
              i.height 
             FROM emotes e 
             LEFT JOIN images i ON i.id = e.imageId
             ORDER BY e.twitch ASC, e.prefix ASC, e.id DESC
         ');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @param $prefix
     * @return mixed
     * @throws DBALException
     */
    function findEmoteByPrefix($prefix) {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare('
            SELECT 
              e.*, 
              i.name as `imageName`, 
              i.label as `imageLabel`, 
              i.size, 
              i.width, 
              i.height 
             FROM emotes e 
             LEFT JOIN images i ON i.id = e.imageId
             WHERE e.prefix = :prefix
             LIMIT 0,1
         ');
        $stmt->bindValue('prefix', $prefix, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * @param $id
     * @return mixed
     * @throws DBALException
     */
    function findEmoteById($id) {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare('
            SELECT 
              e.*, 
              i.name as `imageName`, 
              i.label as `imageLabel`, 
              i.size, 
              i.width, 
              i.height 
             FROM emotes e 
             LEFT JOIN images i ON i.id = e.imageId
             WHERE e.id = :id
             LIMIT 0,1
         ');
        $stmt->bindValue('id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * @throws DBALException
     */
    public function getPublicEmotes() {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare('
            SELECT 
              e.prefix, 
              e.twitch, 
              e.styles,
              i.name as `image`, 
              i.type as `mime`, 
              i.width, 
              i.height 
             FROM emotes e 
             LEFT JOIN images i ON i.id = e.imageId
             WHERE e.draft = 0
             ORDER BY e.id DESC
         ');
        $stmt->execute();
        return array_map(function($v) {
            return [
                'prefix' => $v['prefix'],
                'twitch' => boolval($v['twitch']),
                'styles' => $v['styles'],
                'image' => [[
                    'url' => Config::cdnv() . '/emotes/' . $v['image'],
                    'name' => $v['image'],
                    'mime' => $v['mime'],
                    'height' => intval($v['height']),
                    'width' => intval($v['width']),
                ]],
            ];
        }, $stmt->fetchAll());
    }

    /**
     * Save the css and json files
     * set the cache key.
     */
    public function saveStaticFiles() {
        $cache = Application::getNsCache();
        $cacheKey = round(microtime(true) * 1000) . "." . rand(1000,9999);
        $this->saveStaticCss($cacheKey);
        $this->saveStaticJson($cacheKey);
        $cache->save('chatCacheKey', $cacheKey);
    }

    /**
     * Save the static css file
     * @param $cacheKey
     */
    private function saveStaticCss($cacheKey) {
        try {
            $filename = self::EMOTES_DIR . 'emotes.css.' . $cacheKey;
            $file = fopen($filename,'w+');

            $emotes = array_filter($this->getPublicEmotes(), function($v) {
                return !empty($v['prefix']);
            });
            $styles = array_filter($emotes, function($v) {
                return !empty($v['styles']);
            });

            // Emote
            foreach ($emotes as $v) {
                $name = $v['prefix'];
                $img = $v['image'][0];
                $marginTop = -intval($img['height']);
                $offsetTop = intval($img['height']) * 0.25;
                $c = ".emote.$name {\n";
                $c .= "  background-image: url(\"{$img['name']}\");\n";
                $c .= "  height: {$img['height']}px;\n";
                $c .= "  width: {$img['width']}px;\n";
                $c .= "}\n";
                $c .= ".msg-chat .emote.$name {\n";
                $c .= "  margin-top: {$marginTop}px;\n";
                $c .= "  top: {$offsetTop}px;\n";
                $c .= "}\n";
                fwrite($file, $c);
            }

            // Styles
            foreach ($styles as $v) {
                $c = str_replace('{PREFIX}', $v['prefix'], $v['styles']) . PHP_EOL;
                fwrite($file, $c);
            }

            fclose($file);
            rename($filename, self::EMOTES_DIR . 'emotes.css');
        } catch (Exception $e) {
            Log::critical($e->getMessage());
        }
    }

    /**
     * Save the static json file
     * @param $cacheKey
     */
    private function saveStaticJson($cacheKey) {
        try {
            $filename = self::EMOTES_DIR . 'emotes.json.' . $cacheKey;
            $file = fopen($filename,'w+');
            fwrite($file, json_encode(array_map(function($v){
                unset($v['styles']);
                return $v;
            }, $this->getPublicEmotes())));
            fclose($file);
            rename($filename, self::EMOTES_DIR . 'emotes.json');
        } catch (Exception $e) {
            Log::critical($e->getMessage());
        }
    }
}