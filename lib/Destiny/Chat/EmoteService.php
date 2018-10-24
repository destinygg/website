<?php
namespace Destiny\Chat;

use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\Log;
use Destiny\Common\Service;
use Destiny\Common\Utils\Date;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception\InvalidArgumentException;

/**
 * @method static EmoteService instance()
 */
class EmoteService extends Service {

    const EMOTES_DIR = _BASEDIR . '/static/emotes/';

    /**
     * @param $id
     * @param array $emote
     */
    public function updateEmote($id, array $emote) {
        $conn = Application::getDbConn();
        $conn->update('emotes', [
            'imageId' => $emote['imageId'],
            'prefix' => $emote['prefix'],
            'twitch' => $emote['twitch'],
            'draft' => $emote['draft'],
            'modifiedDate' => Date::getSqlDateTime()
        ], ['id' => $id]);
    }

    /**
     * @param array $emote
     * @return int
     */
    public function insertEmote(array $emote) {
        $conn = Application::getDbConn();
        $conn->insert('emotes', [
            'imageId' => $emote['imageId'],
            'prefix' => $emote['prefix'],
            'twitch' => $emote['twitch'],
            'draft' => $emote['draft'],
            'createdDate' => Date::getSqlDateTime(),
            'modifiedDate' => Date::getSqlDateTime()
        ]);
        return intval($conn->lastInsertId());
    }

    /**
     * @param $id
     * @throws InvalidArgumentException
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
        $stmt->bindValue('prefix', $prefix, \PDO::PARAM_STR);
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
        $stmt->bindValue('id', $id, \PDO::PARAM_INT);
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
              i.name as `image`, 
              i.type as `mime`, 
              i.width, 
              i.height 
             FROM emotes e 
             LEFT JOIN images i ON i.id = e.imageId
             WHERE e.draft = 0
             ORDER BY e.twitch ASC, e.prefix ASC, e.id DESC
         ');
        $stmt->execute();
        return array_map(function($v) {
            return [
                'prefix' => $v['prefix'],
                'twitch' => boolval($v['twitch']),
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

    public function saveStaticFiles() {
        $this->saveStaticCss();
        $this->saveStaticJson();

        $cache = Application::instance()->getCache();
        $cache->save('chatCacheKey', md5_file(self::EMOTES_DIR . 'emotes.css'));
    }

    private function saveStaticCss() {
        try {
            $emotes = $this->getPublicEmotes();
            $css = PHP_EOL;
            $css.= join(PHP_EOL, array_map(function($v){
                $s = '.emote.' . $v['prefix'] .' { ' . PHP_EOL;
                $s.= "\t". 'background-image: url("'. $v['image'][0]['name'] .'");' . PHP_EOL;
                $s.= "\t". 'width: '. $v['image'][0]['width'] .'px;' . PHP_EOL;
                $s.= "\t". 'height: '. $v['image'][0]['height'] .'px;' . PHP_EOL;
                $s.= "\t". 'margin-top: -'. $v['image'][0]['height'] .'px;' . PHP_EOL;
                $s.= '}' . PHP_EOL;
                return $s;
            }, $emotes));
            $css.= PHP_EOL;

            $file = fopen(self::EMOTES_DIR . 'emotes.css', 'w+');
            if (flock($file, LOCK_EX)) {
                fwrite($file, $css);
                flock($file, LOCK_UN);
            } else {
                throw new Exception('Error locking file!');
            }
            fclose($file);
        } catch (\Exception $e) {
            Log::critical($e->getMessage());
        }
    }

    private function saveStaticJson() {
        try {
            $flairs = $this->getPublicEmotes();
            $file = fopen(self::EMOTES_DIR . 'emotes.json','w+');
            if (flock($file,LOCK_EX)) {
                fwrite($file,json_encode($flairs));
                flock($file,LOCK_UN);
            } else {
                throw new Exception('Error locking file!');
            }
            fclose($file);
        } catch (\Exception $e) {
            Log::critical($e->getMessage());
        }
    }
}