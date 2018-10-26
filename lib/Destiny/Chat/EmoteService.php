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
            'styles' => $emote['styles'],
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
            'styles' => $emote['styles'],
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
        $this->saveStaticCss();
        $this->saveStaticJson();

        $cache = Application::instance()->getCache();
        $cache->save('chatCacheKey', microtime() . "." . rand(1000, 9999));
    }

    /**
     * Save the static css file
     */
    private function saveStaticCss() {
        try {
            $emotes = $this->getPublicEmotes();
            $css = PHP_EOL;
            $css .= join(PHP_EOL, array_map(function($v) {
                $s = '';
                if (!empty($v['prefix'])) {
                    $s .= <<<EOT
.emote.{$v['prefix']} {
    background-image: url("{$v['image'][0]['name']}");
    margin-top: -{$v['image'][0]['height']}px;
    height: {$v['image'][0]['height']}px;
    width: {$v['image'][0]['width']}px;
}

EOT;
                }
                if (!empty($v['styles'])) {
                    $s .= str_replace('{PREFIX}', $v['prefix'], $v['styles']);
                    $s .= PHP_EOL;
                }
                $s .= PHP_EOL;
                return $s;
            }, $emotes));
            $css .= PHP_EOL;
            $this->saveFileWithLock(self::EMOTES_DIR . 'emotes.css', $css);
        } catch (\Exception $e) {
            Log::critical($e->getMessage());
        }
    }

    /**
     * Save the static json file
     */
    private function saveStaticJson() {
        try {
            $flairs = array_map(function($v){
                unset($v['styles']);
                return $v;
            }, $this->getPublicEmotes());
            $this->saveFileWithLock(self::EMOTES_DIR . 'emotes.json', json_encode($flairs));
        } catch (\Exception $e) {
            Log::critical($e->getMessage());
        }
    }

    /**
     * @param $file
     * @param $data
     * @throws Exception
     */
    private function saveFileWithLock($file, $data) {
        $file = fopen($file,'w+');
        if (flock($file,LOCK_EX)) {
            fwrite($file, $data);
            flock($file,LOCK_UN);
        } else {
            throw new Exception('Error locking file!');
        }
        fclose($file);
    }
}