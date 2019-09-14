<?php
namespace Destiny\Common;

use Destiny\Common\Session\Session;
use Destiny\Common\Utils\Date;
use Doctrine\DBAL\DBALException;

/**
 * @method static AuditLogger instance()
 */
class AuditLogger extends Service {

    public function logRequest(Request $request): bool {
        try {
            $session = Session::instance();
            if ($session !== null) {
                $creds = $session->getCredentials();
                $conn = Application::getDbConn();
                return $conn->insert('users_audit', [
                    'userid' => $creds->getUserId(),
                    'username' => $creds->getUsername(),
                    'timestamp' => Date::getSqlDateTime(),
                    'requesturi' => $request->uri
                ]) > 0;
            }
        } catch (DBALException $e) {
            Log::error($e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
        return false;
    }

}