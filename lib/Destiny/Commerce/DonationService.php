<?php
namespace Destiny\Commerce;
use Destiny\Common\Application;
use Destiny\Common\Service;

/**
 * @method static DonationService instance()
 */
class DonationService extends Service {

    public function addDonation(array $donation){
        $conn = Application::instance ()->getConnection ();
        $conn->insert ( 'donations', $donation);
        $donation['id'] = $conn->lastInsertId ();
        return $donation;
    }

    public function removeDonation($id){
        $conn = Application::instance ()->getConnection ();
        $conn->delete('donations', ['id' => $id], [\PDO::PARAM_INT]);
    }

    public function updateDonation($id, array $donation){
        $conn = Application::instance ()->getConnection ();
        $conn->update('donations', $donation, ['id' => $id]);
    }

    public function findById($id){
        $conn = Application::instance()->getConnection();
        $stmt = $conn->prepare('SELECT * FROM `donations` WHERE `id` = :id LIMIT 1');
        $stmt->bindValue('id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function findByUserId($userId){
        $conn = Application::instance()->getConnection();
        $stmt = $conn->prepare('SELECT * FROM `donations` WHERE `userid` = :userid LIMIT 1');
        $stmt->bindValue('userid', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }


}