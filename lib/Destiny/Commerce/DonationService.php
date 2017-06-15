<?php
namespace Destiny\Commerce;
use Destiny\Common\Application;
use Destiny\Common\Service;
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
        $conn = Application::instance ()->getConnection ();
        $conn->insert ( 'donations', $donation);
        $donation['id'] = $conn->lastInsertId ();
        return $donation;
    }

    /**
     * @param $id
     * @throws DBALException
     */
    public function removeDonation($id){
        $conn = Application::instance ()->getConnection ();
        $conn->delete('donations', ['id' => $id], [\PDO::PARAM_INT]);
    }

    /**
     * @param $id
     * @param array $donation
     * @throws DBALException
     */
    public function updateDonation($id, array $donation){
        $conn = Application::instance ()->getConnection ();
        $conn->update('donations', $donation, ['id' => $id]);
    }

    /**
     * @param $id
     * @return mixed
     * @throws DBALException
     */
    public function findById($id){
        $conn = Application::instance()->getConnection();
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
        $conn = Application::instance()->getConnection();
        $stmt = $conn->prepare('SELECT * FROM `donations` WHERE `userid` = :userid LIMIT 1');
        $stmt->bindValue('userid', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }


}