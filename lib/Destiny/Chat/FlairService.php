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
 * @method static FlairService instance()
 */
class FlairService extends Service {

    const FLAIRS_DIR = _BASEDIR . '/static/flairs/';

    /**
     * @param $id
     * @param array $flair
     * @throws DBALException
     */
    public function updateFlair($id, array $flair) {
        $conn = Application::getDbConn();
        $conn->update('dfl_features', [
            'featureLabel' => $flair['featureLabel'],
            'imageId' => $flair['imageId'],
            'hidden' => $flair['hidden'],
            'color' => $flair['color'],
            'priority' => $flair['priority'],
            'modifiedDate' => Date::getSqlDateTime()
        ], ['featureId' => $id]);
    }

    /**
     * @throws DBALException
     */
    public function insertFlair(array $flair): int {
        $conn = Application::getDbConn();
        $conn->insert('dfl_features', [
            'featureLabel' => $flair['featureLabel'],
            'featureName' => $flair['featureName'],
            'imageId' => $flair['imageId'],
            'locked' => $flair['locked'],
            'hidden' => $flair['hidden'],
            'color' => $flair['color'],
            'priority' => $flair['priority'],
            'createdDate' => Date::getSqlDateTime(),
            'modifiedDate' => Date::getSqlDateTime()
        ]);
        return intval($conn->lastInsertId());
    }

    /**
     * @throws DBALException
     */
    public function getPublicFlairs(): array {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare('
            SELECT 
              f.featureLabel as `label`, 
              f.featureName as `name`, 
              f.priority as `priority`, 
              f.hidden as `hidden`, 
              f.color as `color`, 
              i.type as `mime`, 
              i.name as `image`, 
              i.width, 
              i.height 
             FROM dfl_features f 
             LEFT JOIN images i ON i.id = f.imageId
             ORDER BY f.priority ASC, f.featureId DESC
         ');
        $stmt->execute();
        return array_map(function($v) {
            return [
                'label' => $v['label'],
                'name' => $v['name'],
                'hidden' => boolval($v['hidden']),
                'priority' => intval($v['priority']),
                'color' => $v['color'],
                'image' => [[
                    'url' => Config::cdnv() . '/flairs/' . $v['image'],
                    'name' => $v['image'],
                    'mime' => $v['mime'],
                    'height' => intval($v['height']),
                    'width' => intval($v['width']),
                ]],
            ];
        }, $stmt->fetchAll());
    }

    /**
     * @throws DBALException
     */
    function getAllFlairNames(): array {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare('SELECT featureName FROM dfl_features');
        $stmt->execute();
        return array_map(function($v){ return $v['featureName']; }, $stmt->fetchAll());
    }

    /**
     * @throws InvalidArgumentException
     * @throws DBALException
     */
    function removeFlairById(int $id) {
        $conn = Application::getDbConn();
        $conn->delete('dfl_features', ['featureId' => $id]);
    }

    /**
     * @throws DBALException
     */
    function findAllFlairs(): array {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare('
            SELECT 
              f.*, 
              i.name as `imageName`, 
              i.label as `imageLabel`, 
              i.size, 
              i.width, 
              i.height 
             FROM dfl_features f 
             LEFT JOIN images i ON i.id = f.imageId
             ORDER BY f.priority ASC, f.featureLabel ASC
         ');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @return array|false
     * @throws DBALException
     */
    function findFlairById(int $id) {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare('
            SELECT 
              f.*, 
              i.name as `imageName`, 
              i.label as `imageLabel`, 
              i.size, 
              i.width, 
              i.height 
             FROM dfl_features f 
             LEFT JOIN images i ON i.id = f.imageId
             WHERE f.featureId = :id
             LIMIT 0,1
         ');
        $stmt->bindValue('id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * @throws DBALException
     */
    function findAvailableFlairNames(): array {
        $features = $this->getAllFlairNames();
        $presets = [];
        for($i=1; $i<=64; $i++) {
            $name = "flair$i";
            if (!in_array($name, $features)) {
                $presets[] = $name;
            }
        }
        return $presets;
    }

    /**
     * Save the css and json files set the cache key.
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
     */
    private function saveStaticCss(string $cacheKey) {
        try {
            $filename = self::FLAIRS_DIR . 'flairs.css.' . $cacheKey;
            $file = fopen($filename,'w+');
            $flairs = array_reverse($this->getPublicFlairs());
            foreach ($flairs as $v) {
                $name = $v['name'];
                $img = $v['image'][0];
                $c = '';
                if ($v['hidden'] == 1) {
                    $c .= ".flair.$name {\n";
                    $c .= "  display: none !important;\n";
                    $c .= "}\n";
                } else {
                    $c .= ".flair.$name {\n";
                    $c .= "  background-image: url(\"{$img['name']}\");\n";
                    $c .= "  height: {$img['height']}px;\n";
                    $c .= "  width: {$img['width']}px;\n";
                    $c .= "  order: {$v['priority']};\n";
                    $c .= "}\n";
                }
                if (!empty($v['color'])) {
                    $c .= ".user.$name {\n";
                    $c .= "  color: {$v['color']};\n";
                    $c .= "}\n";
                }
                $c .= PHP_EOL;
                fwrite($file, $c);
            }
            fclose($file);
            rename($filename, self::FLAIRS_DIR . 'flairs.css');
        } catch (Exception $e) {
            Log::critical($e->getMessage());
        }
    }

    /**
     * Save the static json file
     */
    private function saveStaticJson(string $cacheKey) {
        try {
            $filename = self::FLAIRS_DIR . 'flairs.json.' . $cacheKey;
            $file = fopen($filename,'w+');
            fwrite($file, json_encode($this->getPublicFlairs()));
            fclose($file);
            rename($filename, self::FLAIRS_DIR . 'flairs.json');
        } catch (Exception $e) {
            Log::critical($e->getMessage());
        }
    }
}