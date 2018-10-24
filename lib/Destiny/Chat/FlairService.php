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
 * @method static FlairService instance()
 */
class FlairService extends Service {

    const FLAIRS_DIR = _BASEDIR . '/static/flairs/';

    /**
     * TODO need to add `locked` field?
     * @param $id
     * @param array $flair
     */
    public function updateFlair($id, array $flair) {
        $conn = Application::getDbConn();
        $conn->update('dfl_features', [
            'imageId' => $flair['imageId'],
            'featureLabel' => $flair['featureLabel'],
            'modifiedDate' => Date::getSqlDateTime()
        ], ['featureId' => $id]);
    }

    /**
     * @param array $flair
     * @return int
     */
    public function insertFlair(array $flair) {
        $conn = Application::getDbConn();
        $conn->insert('dfl_features', [
            'imageId' => $flair['imageId'],
            'featureLabel' => $flair['featureLabel'],
            'featureName' => $flair['featureName'],
            'locked' => $flair['locked'],
            'createdDate' => Date::getSqlDateTime(),
            'modifiedDate' => Date::getSqlDateTime()
        ]);
        return intval($conn->lastInsertId());
    }

    /**
     * @throws DBALException
     */
    public function getPublicFlairs() {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare('
            SELECT 
              f.featureLabel as `label`, 
              f.featureName as `name`, 
              i.type as `mime`, 
              i.name as `image`, 
              i.width, 
              i.height 
             FROM dfl_features f 
             LEFT JOIN images i ON i.id = f.imageId
             ORDER BY f.locked DESC, f.featureId DESC
         ');
        $stmt->execute();
        return array_map(function($v) {
            return [
                'label' => $v['label'],
                'name' => $v['name'],
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
     * @return array
     * @throws DBALException
     */
    function getAllFlairNames() {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare('SELECT featureName FROM dfl_features');
        $stmt->execute();
        return array_map(function($v){ return $v['featureName']; }, $stmt->fetchAll());
    }

    /**
     * @param $id
     * @throws InvalidArgumentException
     */
    function removeFlairById($id) {
        $conn = Application::getDbConn();
        $conn->delete('dfl_features', ['featureId' => $id]);
    }

    /**
     * @return mixed
     * @throws DBALException
     */
    function findAllFlairs() {
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
             ORDER BY f.locked DESC, f.featureId DESC
         ');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @param $id
     * @return mixed
     * @throws DBALException
     */
    function findFlairById($id) {
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
        $stmt->bindValue('id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * @param $name
     * @return mixed
     * @throws DBALException
     */
    function findFlairByName($name) {
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
             WHERE f.featureName = :name
             LIMIT 0,1
         ');
        $stmt->bindValue('name', $name, \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * @return array
     * @throws DBALException
     */
    function findAvailableFlairNames() {
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

    public function saveStaticFiles() {
        $this->saveStaticCss();
        $this->saveStaticJson();

        $cache = Application::instance()->getCache();
        $cache->save('chatCacheKey', md5_file(self::FLAIRS_DIR . 'flairs.css'));
    }

    private function saveStaticCss() {
        try {
            $flairs = $this->getPublicFlairs();
            $css = PHP_EOL;
            $css .= join(PHP_EOL, array_map(function($v) {
                $s = '.flair.' . $v['name'] . ' { ' . PHP_EOL;
                $s .= "\t" . 'background-image: url("' . $v['image'][0]['name'] . '");' . PHP_EOL;
                $s .= "\t" . 'width: ' . $v['image'][0]['width'] . 'px;' . PHP_EOL;
                $s .= "\t" . 'height: ' . $v['image'][0]['height'] . 'px;' . PHP_EOL;
                $s .= '}' . PHP_EOL;
                return $s;
            }, $flairs));
            $css .= PHP_EOL;

            $file = fopen(self::FLAIRS_DIR . 'flairs.css', 'w+');
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
            $flairs = $this->getPublicFlairs();
            $file = fopen(self::FLAIRS_DIR . 'flairs.json','w+');
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