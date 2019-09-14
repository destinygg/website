<?php
namespace Destiny\Chat;

use Destiny\Common\Application;
use Destiny\Common\DBException;
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
     * @throws DBException
     */
    public function removeThemeById(int $id) {
        try {
            $conn = Application::getDbConn();
            $conn->delete('themes', ['id' => $id], [PDO::PARAM_INT]);
        } catch (DBALException $e) {
            throw new DBException("Error removing theme.", $e);
        }
    }

    /**
     * @throws DBException
     */
    public function unsetActiveTheme() {
        try {
            $conn = Application::getDbConn();
            $conn->update('themes', ['active' => 0, 'modifiedDate' => Date::getSqlDateTime()], ['active' => 1]);
        } catch (DBALException $e) {
            throw new DBException("Error removing active theme.", $e);
        }
    }

    /**
     * Check if there is an active theme, if not, sets the base theme as active.
     * @throws DBException
     */
    public function ensureOneActiveTheme() {
        try {
            $conn = Application::getDbConn();
            $stmt = $conn->prepare('SELECT COUNT(*) FROM themes t WHERE t.active = 1');
            $stmt->execute();
            if ($stmt->fetchColumn() == 0) {
                $conn->update('themes', ['active' => 1, 'modifiedDate' => Date::getSqlDateTime()], ['id' => self::BASE_THEME_ID]);
            }
        } catch (DBALException $e) {
            throw new DBException("Error setting one active theme.", $e);
        }
    }

    /**
     * @throws DBException
     */
    public function updateTheme(int $id, array $theme) {
        try {
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
        } catch (DBALException $e) {
            throw new DBException("Error updating theme.", $e);
        }
    }

    /**
     * @throws DBException
     */
    public function insertTheme(array $theme): int {
        try {
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
        } catch (DBALException $e) {
            throw new DBException("Error inserting theme.", $e);
        }
    }

    /**
     * @return array|false
     * @throws DBException
     */
    function findThemeById(int $id) {
        try {
            $conn = Application::getDbConn();
            $stmt = $conn->prepare('SELECT t.* FROM themes t WHERE t.id = :id LIMIT 1');
            $stmt->bindValue('id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch();
        } catch (DBALException $e) {
            throw new DBException("Error loading theme.", $e);
        }
    }

    /**
     * @throws DBException
     */
    function findAllThemes(): array {
        try {
            $conn = Application::getDbConn();
            $stmt = $conn->prepare('SELECT * FROM themes');
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (DBALException $e) {
            throw new DBException("Error loading theme.", $e);
        }
    }

    /**
     * @throws DBException
     */
    function existsByPrefix(string $prefix): bool {
        try {
            $conn = Application::getDbConn();
            $stmt = $conn->prepare('SELECT COUNT(*) FROM themes t WHERE t.prefix = :prefix LIMIT 1');
            $stmt->bindValue('prefix', $prefix, PDO::PARAM_STR);
            $stmt->execute();
            return intval($stmt->fetchColumn()) > 0;
        } catch (DBALException $e) {
            throw new DBException("Error loading theme.", $e);
        }
    }

    /**
     * @return array|false
     * @throws DBException
     */
    function getActiveTheme() {
        try {
            $conn = Application::getDbConn();
            $stmt = $conn->prepare('SELECT t.* FROM themes t WHERE t.active = 1 LIMIT 1');
            $stmt->execute();
            return $stmt->fetch();
        } catch (DBALException $e) {
            throw new DBException("Error loading active theme.", $e);
        }
    }
}