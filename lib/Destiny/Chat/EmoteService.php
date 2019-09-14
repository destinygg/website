<?php
namespace Destiny\Chat;

use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\DBException;
use Destiny\Common\Log;
use Destiny\Common\Service;
use Destiny\Common\Utils\Date;
use Doctrine\DBAL\DBALException;
use Exception;
use PDO;

/**
 * @method static EmoteService instance()
 */
class EmoteService extends Service {

    const EMOTES_DIR = _BASEDIR . '/static/emotes/';

    /**
     * @throws DBException
     */
    public function updateEmote($id, array $emote) {
        try {
            $conn = Application::getDbConn();
            $conn->update('emotes', [
                'imageId' => $emote['imageId'],
                'prefix' => $emote['prefix'],
                'twitch' => $emote['twitch'],
                'draft' => $emote['draft'],
                'styles' => $emote['styles'],
                'theme' => $emote['theme'],
                'modifiedDate' => Date::getSqlDateTime()
            ], ['id' => $id]);
        } catch (DBALException $e) {
            throw new DBException("Error updating emote.", $e);
        }
    }

    /**
     * @throws DBException
     */
    public function insertEmote(array $emote): int {
        try {
            $conn = Application::getDbConn();
            $conn->insert('emotes', [
                'imageId' => $emote['imageId'],
                'prefix' => $emote['prefix'],
                'twitch' => $emote['twitch'],
                'draft' => $emote['draft'],
                'styles' => $emote['styles'],
                'theme' => $emote['theme'],
                'createdDate' => Date::getSqlDateTime(),
                'modifiedDate' => Date::getSqlDateTime()
            ]);
            return intval($conn->lastInsertId());
        } catch (DBALException $e) {
            throw new DBException("Error inserting emote.", $e);
        }
    }

    /**
     * @throws DBException
     */
    function removeEmoteById(int $id) {
        try {
            $conn = Application::getDbConn();
            $conn->delete('emotes', ['id' => $id]);
        } catch (DBALException $e) {
            throw new DBException("Error removing emote.", $e);
        }
    }

    /**
     * @throws DBException
     */
    function removeEmoteByTheme(int $themeId) {
        try {
            $conn = Application::getDbConn();
            $conn->delete('emotes', ['theme' => $themeId]);
        } catch (DBALException $e) {
            throw new DBException("Error removing emote.", $e);
        }
    }

    /**
     * @throws DBException
     */
    function findAllEmotesWithTheme($themeId = null, $publishedOnly = false): array {
        try {
            $conn = Application::getDbConn();
            $stmt = $conn->prepare('
                SELECT
                  e2.*, 
                  i.name as `imageName`, 
                  i.label as `imageLabel`, 
                  i.type as `imageType`, 
                  i.size, 
                  i.width, 
                  i.height 
                FROM (
                  SELECT e.prefix, MAX(e.theme) \'theme\' FROM emotes e
                  WHERE e.theme = :theme OR e.theme = :base
                  '. ($publishedOnly ? ' AND e.draft = 0 ' : '' ) .'
                  GROUP BY e.prefix
                ) e
                JOIN emotes e2 ON e2.prefix = e.prefix 
                AND (e2.theme = e.theme OR e2.theme = :base AND e.theme = :base)
                LEFT JOIN images i ON i.id = e2.imageId
                '. ($publishedOnly ? ' WHERE e2.draft = 0 ' : '' ) .'
                ORDER BY e2.twitch ASC, e2.prefix ASC, e2.id DESC
             ');
            $stmt->bindValue('theme', (int) $themeId, PDO::PARAM_INT);
            $stmt->bindValue('base', ThemeService::BASE_THEME_ID, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (DBALException $e) {
            throw new DBException("Error searching emotes.", $e);
        }
    }

    /**
     * @throws DBException
     */
    function findAllEmotes(): array {
       try {
           $conn = Application::getDbConn();
           $stmt = $conn->prepare('
                SELECT 
                  e.*, 
                  i.name as `imageName`, 
                  i.label as `imageLabel`, 
                  i.type as `imageType`, 
                  i.size, 
                  i.width, 
                  i.height 
                 FROM emotes e 
                 LEFT JOIN images i ON i.id = e.imageId
                 ORDER BY e.twitch ASC, e.prefix ASC, e.id DESC
             ');
           $stmt->execute();
           return $stmt->fetchAll();
       } catch (DBALException $e) {
           throw new DBException("Error searching emotes.", $e);
       }
    }

    /**
     * @return array|false
     * @throws DBException
     */
    function findEmoteById(int $id) {
        try {
            $conn = Application::getDbConn();
            $stmt = $conn->prepare('
                SELECT 
                  e.*, 
                  i.name as `imageName`, 
                  i.label as `imageLabel`, 
                  i.type as `imageType`, 
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
        } catch (DBALException $e) {
            throw new DBException("Error loading emote.", $e);
        }
    }

    /**
     * Save the css and json files set the cache key.
     */
    public function saveStaticFiles() {
        try {
            $cache = Application::getNsCache();
            $cacheKey = round(microtime(true) * 1000) . "." . rand(1000,9999);

            $themeService = ThemeService::instance();
            $theme = $themeService->getActiveTheme();
            $emotes = $this->findAllEmotesWithTheme($theme['id'], true);

            $this->saveStaticCss($cacheKey, $emotes);
            $this->saveStaticJson($cacheKey, $emotes);
            $cache->save('chatCacheKey', $cacheKey);
        } catch (Exception $e) {
            Log::critical($e->getMessage());
        }
    }

    private function saveStaticCss(string $cacheKey, array $emotes) {
        try {
            $filename = self::EMOTES_DIR . 'emotes.css.' . $cacheKey;
            $file = fopen($filename,'w+');
            foreach ($emotes as $v) {
                fwrite($file, $this->buildEmoteCSS($v));
            }
            foreach (array_filter($emotes, function($v) { return !empty($v['styles']); }) as $v) {
                fwrite($file, $this->buildEmoteStyleCSS($v));
            }
            fclose($file);
            rename($filename, self::EMOTES_DIR . 'emotes.css');
        } catch (Exception $e) {
            Log::critical($e->getMessage());
        }
    }

    private function saveStaticJson(string $cacheKey, array $emotes) {
        try {
            $filename = self::EMOTES_DIR . 'emotes.json.' . $cacheKey;
            $file = fopen($filename,'w+');
            fwrite($file, json_encode(array_map(function($emote){ return [
                'prefix' => $emote['prefix'],
                'twitch' => boolval($emote['twitch']),
                'theme' => $emote['theme'],
                'image' => [[
                    'url' => Config::cdnv() . '/emotes/' . $emote['imageName'],
                    'name' => $emote['imageName'],
                    'mime' => $emote['imageType'],
                    'height' => intval($emote['height']),
                    'width' => intval($emote['width']),
                ]]
            ]; }, $emotes)));
            fclose($file);
            rename($filename, self::EMOTES_DIR . 'emotes.json');
        } catch (Exception $e) {
            Log::critical($e->getMessage());
        }
    }

    function buildEmoteStyleCSS(array $emote): string {
        return str_replace('{PREFIX}', $emote['prefix'], $emote['styles']) . PHP_EOL;
    }

    function buildEmoteCSS(array $emote, bool $relative = true): string {
        $name = $emote['prefix'];
        $img = is_array($emote['image'] ?? null) ? $emote['image'][0] : [
            'name' => $emote['imageName'],
            'height' => $emote['height'],
            'width' => $emote['width']
        ];
        $url = $relative ? $img['name'] : Config::cdnv() . '/emotes/' . $img['name'];
        $marginTop = -intval($img['height']);
        $offsetTop = intval($img['height']) * 0.25;
        $cssClass = ".emote.$name";
        $c = "$cssClass {\n";
        $c .= "  background-image: url(\"$url\");\n";
        $c .= "  height: {$img['height']}px;\n";
        $c .= "  width: {$img['width']}px;\n";
        $c .= "}\n";
        $c .= ".msg-chat $cssClass {\n";
        $c .= "  margin-top: {$marginTop}px;\n";
        $c .= "  top: {$offsetTop}px;\n";
        $c .= "}\n";
        return $c;
    }

    /**
     * @throws DBException
     */
    public function isPrefixTaken(string $prefix, int $theme, $exclude = null): bool {
        try {
            $conn = Application::getDbConn();
            $stmt = $conn->prepare('SELECT COUNT(*) FROM emotes e WHERE e.prefix = :prefix AND e.theme = :theme AND e.id != :exclude');
            $stmt->bindValue('exclude', $exclude, PDO::PARAM_INT);
            $stmt->bindValue('prefix', $prefix, PDO::PARAM_STR);
            $stmt->bindValue('theme', $theme, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (DBALException $e) {
            throw new DBException("Error checking emote prefix.", $e);
        }
    }
}