<?php

namespace Destiny\Tasks;

use Destiny\Common\Annotation\Schedule;
use Destiny\Common\Application;
use Destiny\Commerce\SubscriptionStatus;
use Destiny\Common\Cron\TaskInterface;
use Destiny\Common\Utils\Options;
use Doctrine\DBAL\DBALException;
use PDO;

/**
 * @Schedule(frequency=1,period="hour")
 */
class RedditSubscribers implements TaskInterface {
    
    public $output;
    
    public function __construct(array $options = []) {
        Options::setOptions ( $this, $options );
    }

    /**
     * @return mixed|void
     * @throws DBALException
     */
    public function execute() {
        if (empty ($this->output))
            return;

        $conn = Application::getDbConn();
        $stmt = $conn->prepare('
            SELECT 
                auth.authId,
                auth.authDetail,
                u.userId,u.username,
                u.email,
                s.subscriptionTier,
                s.subscriptionType,
                s.createdDate,
                s.endDate,
                s.recurring,
                s.status
            FROM dfl_users_subscriptions AS s
            INNER JOIN dfl_users AS u ON (u.userId = s.userId)
            INNER JOIN dfl_users_auth AS auth ON (auth.userId = u.userId AND auth.authProvider = :authProvider)
            WHERE s.status = :subscriptionStatus
            ORDER BY s.createdDate ASC
            LIMIT :start,:limit
        ');

        $stmt->bindValue('subscriptionStatus', SubscriptionStatus::ACTIVE, PDO::PARAM_STR);
        $stmt->bindValue('authProvider', 'reddit', PDO::PARAM_STR);
        $stmt->bindValue('start', 0, PDO::PARAM_INT);
        $stmt->bindValue('limit', 10000, PDO::PARAM_INT);
        $stmt->execute();

        $records = $stmt->fetchAll();
        $json = [];
        foreach ($records as $record) {
            $json [] = [
                'id' => $record ['userId'],
                'username' => $record ['username'],
                'subscription' => [
                    'startDate' => $record ['createdDate'],
                    'endDate' => $record ['endDate'],
                    'recurring' => ($record ['recurring']) ? true : false,
                    'tier' => $record ['subscriptionTier'],
                    'type' => $record ['subscriptionType']
                ],
                'reddit' => [
                    'username' => $record ['authDetail'],
                    'id' => $record ['authId']
                ]
            ];
        }

        $tmpFilename = $this->output . '.' . time();
        file_put_contents($tmpFilename, json_encode($json, JSON_NUMERIC_CHECK));
        if (is_file($this->output)) {
            unlink($this->output);
        }
        rename($tmpFilename, $this->output);
    }
    
    /**
     * @return string
     */
    public function getOutput() {
        return $this->output;
    }
    
    /**
     * @param string $output            
     */
    public function setOutput($output) {
        $this->output = $output;
    }
}