<?php
namespace Destiny\Common\Images;

use Destiny\Common\Application;
use Destiny\Common\Service;
use Destiny\Common\Utils\Date;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use PDO;
use RuntimeException;

/**
 * @method static ImageService instance()
 */
class ImageService extends Service {

    const MAX_FILE_SIZE = 10485760; // 10MB
    const ALLOW_TYPES = [
        'jpg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif'
    ];

    /**
     * @param array $img
     * @param $tag
     * @return string
     * @throws DBALException
     */
    function addImage(array $img, $tag) {
        $conn = Application::getDbConn();
        $conn->insert('images', [
            'label' => $img ['label'],
            'name' => $img ['name'],
            'hash' => $img ['hash'],
            'size' => $img ['size'],
            'type' => $img ['type'],
            'width' => $img ['width'],
            'height' => $img ['height'],
            'tag' => $tag,
            'createdDate' => Date::getSqlDateTime(),
            'modifiedDate' => Date::getSqlDateTime(),
        ]);
        return intval($conn->lastInsertId());
    }

    /**
     * @param $id
     * @return mixed
     * @throws DBALException
     */
    function findImageById($id) {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare('SELECT * FROM `images` WHERE `id` = :id LIMIT 0,1');
        $stmt->bindValue('id', $id, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * @param $name
     * @return mixed
     * @throws DBALException
     */
    function findImageByName($name) {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare('SELECT * FROM `images` WHERE `name` = :name LIMIT 0,1');
        $stmt->bindValue('name', $name, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * @param $id
     * @throws InvalidArgumentException
     * @throws DBALException
     */
    function removeImageById($id) {
        $conn = Application::getDbConn();
        $conn->delete('images', ['id' => $id]);
    }

    /**
     * @return array
     * @throws DBALException
     */
    public function getAllOrphanedImages() {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare('
            SELECT i.* FROM images i 
            LEFT JOIN emotes e ON e.imageId = i.id
            LEFT JOIN dfl_features f ON f.imageId = i.id
            WHERE e.id IS NULL AND f.featureId IS NULL
            AND i.createdDate <= (NOW() - INTERVAL 1 HOUR)
        ');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @param $name
     * @param $destination
     * @return bool
     */
    function removeImageFile($name, $destination) {
        return unlink($destination . $name);
    }

    /**
     * @param array $upload
     * @param $destination
     * @return array
     */
    function upload(array $upload, $destination) {
        try {
            // Undefined | Multiple Files | $_FILES Corruption Attack
            // If this request falls under any of them, treat it invalid.
            if (!isset($upload['error']) || is_array($upload['error'])) {
                throw new RuntimeException('Invalid parameters.');
            }

            switch ($upload['error']) {
                case UPLOAD_ERR_OK:
                    break;
                case UPLOAD_ERR_NO_FILE:
                    throw new RuntimeException('No file sent.');
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    throw new RuntimeException('Exceeded file size limit.');
                default:
                    throw new RuntimeException('Unknown errors.');
            }

            if ($upload['size'] > self::MAX_FILE_SIZE) {
                throw new RuntimeException('Exceeded file size limit.');
            }
            if (false === $ext = array_search($upload['type'], self::ALLOW_TYPES, true)) {
                throw new RuntimeException('Invalid file format.');
            }
            if (false === $md5 = md5_file($upload['tmp_name'])) {
                throw new RuntimeException('Failed to hash image.');
            }

            $name = uniqid() . ".$ext"; // TODO collision
            $info = getimagesize($upload['tmp_name']);

            if (!$info)
                throw new RuntimeException('Failed to extract dimensions.');
            if (is_file($destination . $name) && !unlink($destination . $name))
                throw new RuntimeException('Unable to remove file.');
            if (!move_uploaded_file($upload['tmp_name'], $destination . $name))
                throw new RuntimeException('Failed to move uploaded file.');

            return [
                'label' => basename($upload["name"]),
                'name' => $name,
                'hash' => $md5,
                'size' => $upload['size'],
                'type' => $info ['mime'],
                'width' => $info [0],
                'height' => $info [1],
            ];
        } catch (RuntimeException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * The files array is stored strangely, this is to convert it to
     * [file, file, file ...]
     *
     * @param $vector
     * @return array
     */
    public static function diverseArray($vector) {
        $result = [];
        foreach ($vector as $key1 => $value1) {
            foreach ($value1 as $key2 => $value2) {
                $result[$key2][$key1] = $value2;
            }
        }
        return $result;
    }
}