<?php
namespace Destiny\Commerce;

use DateTime;
use Destiny\Common\Service;
use Destiny\Common\Application;
use Destiny\Common\Utils\Date;

/**
 * @method static StatisticsService instance()
 */
class StatisticsService extends Service {

    public function getRevenueLastXDays ( $days ) {
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare ( '
            SELECT COUNT(*) `total`, SUM(amount) `sum`, DATE_FORMAT(paymentDate, \'%Y-%m-%d\') `date`
            FROM `dfl_orders_payments`
            WHERE paymentStatus = :status
            AND paymentDate BETWEEN CURDATE()-INTERVAL :days DAY AND CURDATE() + INTERVAL 1 DAY
            GROUP BY DATE(paymentDate)
            ORDER BY paymentDate ASC
        ' );
        $stmt->bindValue ( 'days', $days, \PDO::PARAM_INT );
        $stmt->bindValue ( 'status', PaymentStatus::COMPLETED, \PDO::PARAM_STR );
        $stmt->execute ();
        return $stmt->fetchAll ();
    }

    public function getRevenueLastXMonths ( $months ) {
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare ( '
            SELECT COUNT(*) `total`, SUM(amount) `sum`, DATE_FORMAT(paymentDate, \'%Y-%m-01\') `date`
            FROM `dfl_orders_payments`
            WHERE paymentStatus = :status
            AND paymentDate BETWEEN CURDATE()-INTERVAL :months MONTH AND CURDATE() + INTERVAL 1 DAY
            GROUP BY DATE_FORMAT(paymentDate, \'%Y%m\')
            ORDER BY paymentDate ASC
        ' );
        $stmt->bindValue ( 'months', $months, \PDO::PARAM_INT );
        $stmt->bindValue ( 'status', PaymentStatus::COMPLETED, \PDO::PARAM_STR );
        $stmt->execute ();
        return $stmt->fetchAll ();
    }

    public function getRevenueLastXYears( $years ) {
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare ( '
            SELECT COUNT(*) `total`, SUM(amount) `sum`, DATE_FORMAT(paymentDate, \'%Y-01-01\') `date`
            FROM `dfl_orders_payments`
            WHERE paymentDate BETWEEN CURDATE()-INTERVAL :years YEAR AND CURDATE() + INTERVAL 1 DAY
            AND paymentStatus = :status
            GROUP BY DATE_FORMAT(paymentDate, \'%Y\')
            ORDER BY paymentDate ASC
        ' );
        $stmt->bindValue ( 'years', $years, \PDO::PARAM_INT );
        $stmt->bindValue ( 'status', PaymentStatus::COMPLETED, \PDO::PARAM_STR );
        $stmt->execute ();
        return $stmt->fetchAll ();
    }

    public function getNewSubscribersLastXDays ( $days ) {
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare ( '
            SELECT COUNT(*) `total`, DATE_FORMAT(createdDate, \'%Y-%m-%d\') `date`
            FROM `dfl_users_subscriptions` s
            WHERE s.createdDate BETWEEN CURDATE()-INTERVAL :days DAY AND CURDATE() + INTERVAL 1 DAY
            AND s.status IN (\'Expired\',\'Active\',\'Cancelled\')
            GROUP BY DATE(s.createdDate)
            ORDER BY s.createdDate ASC
        ' );
        $stmt->bindValue ( 'days', $days, \PDO::PARAM_INT );
        $stmt->execute ();
        return $stmt->fetchAll ();
    }

    public function getNewTieredSubscribersLastXDays ( DateTime $fromDate, DateTime $toDate ) {
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare ( '
            SELECT COUNT(*) `total`, DATE_FORMAT(createdDate, \'%Y-%m-%d\') `date`, s.subscriptionTier
            FROM `dfl_users_subscriptions` s
            WHERE s.createdDate BETWEEN :fromDate AND :toDate
            AND s.status IN (\'Expired\',\'Active\',\'Cancelled\')
            GROUP BY DATE(s.createdDate), s.subscriptionTier
            ORDER BY s.createdDate ASC
        ' );
        $stmt->bindValue ( 'fromDate', $fromDate->format(Date::FORMAT), \PDO::PARAM_STR );
        $stmt->bindValue ( 'toDate', $toDate->format(Date::FORMAT), \PDO::PARAM_INT );
        $stmt->execute ();
        return $stmt->fetchAll ();
    }

    public function getNewSubscribersLastXMonths ( $months ) {
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare ( '
            SELECT COUNT(*) `total`, DATE_FORMAT(createdDate, \'%Y-%m-01\') `date`
            FROM `dfl_users_subscriptions` s
            WHERE s.createdDate BETWEEN CURDATE()-INTERVAL :months MONTH AND CURDATE() + INTERVAL 1 DAY
            AND s.status IN (\'Expired\',\'Active\',\'Cancelled\')
            GROUP BY DATE_FORMAT(s.createdDate, \'%Y%m\')
            ORDER BY s.createdDate ASC
        ' );
        $stmt->bindValue ( 'months', $months, \PDO::PARAM_INT );
        $stmt->execute ();
        return $stmt->fetchAll ();
    }

    public function getNewSubscribersLastXYears ( $years ) {
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare ( '
            SELECT COUNT(*) `total`, DATE_FORMAT(createdDate, \'%Y-01-01\') `date` FROM `dfl_users_subscriptions` s
            WHERE s.createdDate BETWEEN CURDATE()-INTERVAL :years YEAR AND CURDATE() + INTERVAL 1 DAY
            AND s.status IN (\'Expired\',\'Active\',\'Cancelled\')
            GROUP BY DATE_FORMAT(s.createdDate, \'%Y\')
            ORDER BY s.createdDate ASC
        ' );
        $stmt->bindValue ( 'years', $years, \PDO::PARAM_INT );
        $stmt->execute ();
        return $stmt->fetchAll ();
    }

}