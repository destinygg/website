<?php
namespace Destiny\Commerce;

use Destiny\Common\Service;
use Destiny\Common\Application;
use Destiny\Common\Utils\Date;
use Destiny\Common\Config;

/**
 * @method static OrdersService instance()
 */
class OrdersService extends Service {
    
    /**
     * Create a new order and item based on subscription
     *
     * @param array $subscriptionType           
     * @param int $userId           
     * @return array
     */
    public function createSubscriptionOrder(array $subscriptionType, $userId) {
        $ordersService = OrdersService::instance ();
        $order = array ();
        $order ['userId'] = $userId;
        $order ['description'] = $subscriptionType ['tierLabel'];
        $order ['amount'] = $subscriptionType ['amount'];
        $order ['currency'] = Config::$a ['commerce'] ['currency'];
        $order ['orderId'] = $ordersService->addOrder ( $order );
        return $order;
    }

    /**
     * Add a 'New' order
     *
     * @param array $order
     * @return int
     */
    public function addOrder(array $order) {
        $conn = Application::instance ()->getConnection ();
        $conn->insert ( 'dfl_orders', array (
            'userId' => $order ['userId'],
            'amount' => $order ['amount'],
            'currency' => $order ['currency'],
            'description' => $order ['description'],
            'state' => OrderStatus::_NEW,
            'createdDate' => Date::getDateTime ( 'NOW' )->format ( 'Y-m-d H:i:s' ) 
        ) );
        $order ['orderId'] = $conn->lastInsertId ();
        return $order ['orderId'];
    }

    /**
     * @param int $orderId
     * @return mixed
     */
    public function getOrderById($orderId) {
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare ( 'SELECT * FROM dfl_orders WHERE orderId = :orderId LIMIT 0,1' );
        $stmt->bindValue ( 'orderId', $orderId, \PDO::PARAM_INT );
        $stmt->execute ();
        return $stmt->fetch ();
    }

    /**
     * @param int $orderId
     * @param int $userId
     * @return mixed
     */
    public function getOrderByIdAndUserId($orderId, $userId) {
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare ( 'SELECT * FROM dfl_orders WHERE orderId = :orderId AND userId = :userId LIMIT 0,1' );
        $stmt->bindValue ( 'orderId', $orderId, \PDO::PARAM_INT );
        $stmt->bindValue ( 'userId', $userId, \PDO::PARAM_INT );
        $stmt->execute ();
        return $stmt->fetch ();
    }

    /**
     * @param array $profile
     * @return int
     */
    public function addPaymentProfile(array $profile) {
        $conn = Application::instance ()->getConnection ();
        $conn->insert ( 'dfl_orders_payment_profiles', array (
            'userId' => $profile ['userId'],
            'orderId' => $profile ['orderId'],
            'paymentProfileId' => $profile ['paymentProfileId'],
            'state' => $profile ['state'],
            'amount' => $profile ['amount'],
            'currency' => $profile ['currency'],
            'billingFrequency' => $profile ['billingFrequency'],
            'billingPeriod' => $profile ['billingPeriod'],
            'billingStartDate' => $profile ['billingStartDate'],
            'billingNextDate' => $profile ['billingNextDate'] 
        ) );
        return $conn->lastInsertId ();
    }

    /**
     * @param array $ipn
     */
    public function addIpnRecord(array $ipn) {
        $conn = Application::instance ()->getConnection ();
        $conn->insert ( 'dfl_orders_ipn', array (
            'ipnTrackId' => $ipn ['ipnTrackId'],
            'ipnTransactionId' => $ipn ['ipnTransactionId'],
            'ipnTransactionType' => $ipn ['ipnTransactionType'],
            'ipnData' => $ipn ['ipnData'] 
        ) );
    }

    /**
     * This assumes there is only one profile per order
     * - this wont be the case other than when you are in the process of making an order
     *
     * @param int $orderId
     * @return mixed
     */
    public function getPaymentProfileByOrderId($orderId) {
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare ( 'SELECT * FROM dfl_orders_payment_profiles WHERE orderId = :orderId LIMIT 0,1' );
        $stmt->bindValue ( 'orderId', $orderId, \PDO::PARAM_INT );
        $stmt->execute ();
        return $stmt->fetch ();
    }

    /**
     * This uses the PP paymentProfileId, not the autoincrement local Id
     *
     * @param int $paymentProfileId
     * @return mixed
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getPaymentProfileByPaymentProfileId($paymentProfileId) {
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare ( 'SELECT * FROM dfl_orders_payment_profiles WHERE paymentProfileId = :paymentProfileId LIMIT 0,1' );
        $stmt->bindValue ( 'paymentProfileId', $paymentProfileId, \PDO::PARAM_STR );
        $stmt->execute ();
        return $stmt->fetch ();
    }

    /**
     * This uses the PP paymentProfileId, not the autoincrement local Id
     *
     * @param int $profileId
     * @return mixed
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getPaymentProfileById($profileId) {
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare ( 'SELECT * FROM dfl_orders_payment_profiles WHERE profileId = :profileId LIMIT 0,1' );
        $stmt->bindValue ( 'profileId', $profileId, \PDO::PARAM_INT );
        $stmt->execute ();
        return $stmt->fetch ();
    }

    /**
     * @param array $paymentProfile
     */
    public function updatePaymentProfile(array $paymentProfile) {
        $conn = Application::instance ()->getConnection ();
        $conn->update ( 'dfl_orders_payment_profiles', $paymentProfile, array ('profileId' => $paymentProfile['profileId']) );
    }

    /**
     * @param array $order
     */
    public function updateOrder(array $order) {
        $conn = Application::instance ()->getConnection ();
        $conn->update ( 'dfl_orders_payment_profiles', $order, array ('profileId' => $order['orderId']) );
    }

    /**
     * @param array $payment
     */
    public function updatePayment(array $payment) {
        $conn = Application::instance ()->getConnection ();
        $conn->update ( 'dfl_orders_payments', $payment, array ('paymentId' => $payment['paymentId']) );
    }

    /**
     * @param string $transactionId
     * @return mixed
     */
    public function getPaymentByTransactionId($transactionId) {
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare ( 'SELECT * FROM dfl_orders_payments WHERE transactionId = :transactionId LIMIT 0,1' );
        $stmt->bindValue ( 'transactionId', $transactionId, \PDO::PARAM_STR );
        $stmt->execute ();
        return $stmt->fetch ();
    }

    /**
     * @param int $paymentId
     * @return array
     */
    public function getOrderByPaymentId($paymentId) {
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare ( '
            SELECT * FROM dfl_orders AS a
            INNER JOIN dfl_orders_payments AS b ON (b.orderId = a.orderId)
            WHERE b.paymentId = :paymentId
            LIMIT 0,1
        ' );
        $stmt->bindValue ( 'paymentId', $paymentId, \PDO::PARAM_INT );
        $stmt->execute ();
        return $stmt->fetch ();
    }

    /**
     * @todo this returns payments in ASC order, the getPaymentsByUser returns them in DESC order
     *
     * @param int $orderId
     * @param int $limit
     * @param int $start
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getPaymentsByOrderId($orderId, $limit = 100, $start = 0) {
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare ( '
            SELECT payments.* FROM dfl_orders_payments AS `payments`
            INNER JOIN dfl_orders AS `orders` ON (orders.orderId = payments.orderId)
            WHERE orders.orderId = :orderId
            ORDER BY payments.paymentDate ASC
            LIMIT :start,:limit
        ' );
        $stmt->bindValue ( 'orderId', $orderId, \PDO::PARAM_INT );
        $stmt->bindValue ( 'start', $start, \PDO::PARAM_INT );
        $stmt->bindValue ( 'limit', $limit, \PDO::PARAM_INT );
        $stmt->execute ();
        return $stmt->fetchAll ();
    }

    /**
     * @param array $payment
     * @return int paymentId
     */
    public function addOrderPayment(array $payment) {
        $conn = Application::instance ()->getConnection ();
        $conn->insert ( 'dfl_orders_payments', array (
            'orderId' => $payment ['orderId'],
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
     * Returns an easier way to read a billing cycle
     *
     * @param int $frequency
     * @param string $period
     * @return string
     */
    public function buildBillingCycleString($frequency, $period) {
        if ($frequency < 1) {
            return 'Never';
        }
        if ($frequency == 1) {
            return 'Once a ' . strtolower ( $period );
        }
        if ($frequency > 1) {
            return 'Every ' . $frequency . ' ' . strtolower ( $period ) . 's';
        }
        return '';
    }
    
    /**
     * @param int $userId
     * @param array $order
     * @param array $subscriptionType
     * @param \DateTime $billingStartDate
     * @return array
     */
    public function createPaymentProfile($userId, array $order, array $subscriptionType, \DateTime $billingStartDate) {
        $ordersService = OrdersService::instance ();
        $paymentProfile = array ();
        $paymentProfile ['paymentProfileId'] = '';
        $paymentProfile ['userId'] = $userId;
        $paymentProfile ['orderId'] = $order ['orderId'];
        $paymentProfile ['amount'] = $order ['amount'];
        $paymentProfile ['currency'] = $order ['currency'];
        $paymentProfile ['billingFrequency'] = $subscriptionType ['billingFrequency'];
        $paymentProfile ['billingPeriod'] = $subscriptionType ['billingPeriod'];
        $paymentProfile ['billingStartDate'] = $billingStartDate->format ( 'Y-m-d H:i:s' );
        $paymentProfile ['billingNextDate'] = $billingStartDate->format ( 'Y-m-d H:i:s' );
        $paymentProfile ['state'] = PaymentProfileStatus::_NEW;
        $paymentProfile ['profileId'] = $ordersService->addPaymentProfile ( $paymentProfile );
        return $paymentProfile;
    }

}