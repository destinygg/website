<?php
namespace Destiny\Common\Images;

use Destiny\Common\Application;
use Destiny\Common\DBException;
use Destiny\Common\Service;
use Destiny\Common\Utils\Date;
use Doctrine\DBAL\DBALException;
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
     * @throws DBException
     */
    function insertImage(array $img, string $tag = ''): int {
        try {
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
        } catch (DBALException $e) {
            throw new DBException("Error inserting image.", $e);
        }
    }

    /**
     * @return array|false
     * @throws DBException
     */
    function findImageById(int $id) {
        try {
            $conn = Application::getDbConn();
            $stmt = $conn->prepare('SELECT * FROM `images` WHERE `id` = :id LIMIT 1');
            $stmt->bindValue('id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch();
        } catch (DBALException $e) {
            throw new DBException("Error loading image.", $e);
        }
    }

    /**
     * @throws DBException
     */
    function removeImageById($id) {
        try {
            $conn = Application::getDbConn();
            $conn->delete('images', ['id' => $id]);
        } catch (DBALException $e) {
            throw new DBException("Error removing image.", $e);
        }
    }

    /**
     * @throws DBException
     */
    public function getAllOrphanedImages(): array {
        try {
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
        } catch (DBALException $e) {
            throw new DBException("Error loading orphaned images.", $e);
        }
    }

    function removeImageFile(array $image, string $path): bool {
        return unlink($path . $image['name']);
    }

    function copyImageFile(array $image, string $path): string {
        $ext = array_search($image['type'], self::ALLOW_TYPES, true);
        $newName = uniqid() . ".$ext";
        copy($path . $image['name'], $path . $newName);
        return $newName;
    }

    function upload(array $upload, string $destination = null): array {
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

            if (!file_exists($destination))
                mkdir($destination);
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
     */
    public static function diverseArray(array $vector): array {
        $result = [];
        foreach ($vector as $key1 => $value1) {
            foreach ($value1 as $key2 => $value2) {
                $result[$key2][$key1] = $value2;
            }
        }
        return $result;
    }
}
