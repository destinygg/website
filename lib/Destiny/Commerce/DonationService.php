<?php
namespace Destiny\Commerce;
use Destiny\Common\Application;
use Destiny\Common\Service;
use Destiny\Common\Utils\Date;
use Doctrine\DBAL\DBALException;

/**
 * @method static DonationService instance()
 */
class DonationService extends Service {

    /**
     * @param array $donation
     * @return array
     * @throws DBALException
     */
    public function addDonation(array $donation){
        $conn = Application::getDbConn();
        $conn->insert ( 'donations', $donation);
        $donation['id'] = $conn->lastInsertId ();
        return $donation;
    }

    /**
     * @param array $payment
     * @return int paymentId
     *
     * @throws DBALException
     */
    public function addPayment(array $payment) {
        $conn = Application::getDbConn();
        $conn->insert ( 'dfl_orders_payments', array (
            'donationId' => $payment ['donationId'],
            'amount' => $payment ['amount'],
            'currency' => $payment ['currency'],
            'transactionId' => $payment ['transactionId'],
            'transactionType' => $payment ['transactionType'],
            'paymentType' => $payment ['paymentType'],
            'payerId' => $payment ['payerId'],
            'paymentStatus' => $payment ['paymentStatus'],
            'paymentDate' => $payment ['paymentDate'],
            'createdDate' => Date::getDateTime ( 'NOW' )->format ( 'Y-m-d H:i:s' )
        ) );
        return $conn->lastInsertId ();
    }

    /**
     * @param $id
     * @throws DBALException
     */
    public function removeDonation($id){
        $conn = Application::getDbConn();
        $conn->delete('donations', ['id' => $id], [\PDO::PARAM_INT]);
    }

    /**
     * @param $id
     * @param array $donation
     * @throws DBALException
     */
    public function updateDonation($id, array $donation){
        $conn = Application::getDbConn();
        $conn->update('donations', $donation, ['id' => $id]);
    }

    /**
     * @param $id
     * @return mixed
     * @throws DBALException
     */
    public function findById($id){
        $conn = Application::getDbConn();
        $stmt = $conn->prepare('SELECT * FROM `donations` WHERE `id` = :id LIMIT 1');
        $stmt->bindValue('id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * @param $userId
     * @return mixed
     * @throws DBALException
     */
    public function findByUserId($userId){
        $conn = Application::getDbConn();
        $stmt = $conn->prepare('SELECT * FROM `donations` WHERE `userid` = :userid LIMIT 1');
        $stmt->bindValue('userid', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }


}