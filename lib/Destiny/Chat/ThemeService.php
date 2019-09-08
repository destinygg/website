<?php
namespace Destiny\Chat;

use Destiny\Common\Application;
use Destiny\Common\Service;
use Destiny\Common\Utils\Date;
use Doctrine\DBAL\DBALException;
use PDO;

/**
 * @method static ThemeService instance()
 */
class ThemeService extends Service {

    const BASE_THEME_ID = 1;

    /**
     * @throws DBALException
     */
    public function removeThemeById(int $id) {
        $conn = Application::getDbConn();
        $conn->delete('themes', ['id' => $id], [PDO::PARAM_INT]);
    }

    /**
     * @throws DBALException
     */
    public function unsetActiveTheme() {
        $conn = Application::getDbConn();
        $conn->update('themes', ['active' => 0, 'modifiedDate' => Date::getSqlDateTime()], ['active' => 1]);
    }

    /**
     * Check if there is an active theme, if not, sets the base theme as active.
     * @throws DBALException
     */
    public function ensureOneActiveTheme() {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare('SELECT COUNT(*) FROM themes t WHERE t.active = 1');
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $conn->update('themes', ['active' => 1, 'modifiedDate' => Date::getSqlDateTime()], ['id' => self::BASE_THEME_ID]);
        }
    }

    /**
     * @throws DBALException
     */
    public function updateTheme(int $id, array $theme) {
        if (boolval($theme['active'])) {
            $this->unsetActiveTheme();
        }
        $conn = Application::getDbConn();
        $conn->update('themes', [
            'prefix' => $theme['prefix'],
            'label' => $theme['label'],
            'active' => $theme['active'],
            'color' => $theme['color'],
            'modifiedDate' => Date::getSqlDateTime()
        ], ['id' => $id]);
        $this->ensureOneActiveTheme();
    }

    /**
     * @throws DBALException
     */
    public function insertTheme(array $theme): int {
        if (boolval($theme['active'])) {
            $this->unsetActiveTheme();
        }
        $conn = Application::getDbConn();
        $conn->insert('themes', [
            'prefix' => $theme['prefix'],
            'label' => $theme['label'],
            'active' => $theme['active'],
            'color' => $theme['color'],
            'createdDate' => Date::getSqlDateTime(),
            'modifiedDate' => Date::getSqlDateTime()
        ]);
        $id = intval($conn->lastInsertId());
        $this->ensureOneActiveTheme();
        return $id;
    }

    /**
     * @return array|false
     * @throws DBALException
     */
    function findThemeById(int $id) {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare('SELECT t.* FROM themes t WHERE t.id = :id LIMIT 1');
        $stmt->bindValue('id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * @throws DBALException
     */
    function findAllThemes(): array {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare('SELECT * FROM themes');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @throws DBALException
     */
    function existsByPrefix(string $prefix): bool {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare('SELECT COUNT(*) FROM themes t WHERE t.prefix = :prefix LIMIT 1');
        $stmt->bindValue('prefix', $prefix, PDO::PARAM_STR);
        $stmt->execute();
        return intval($stmt->fetchColumn()) > 0;
    }

    /**
     * @return array|false
     * @throws DBALException
     */
    function getActiveTheme() {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare('SELECT t.* FROM themes t WHERE t.active = 1 LIMIT 1');
        $stmt->execute();
        return $stmt->fetch();
    }
}