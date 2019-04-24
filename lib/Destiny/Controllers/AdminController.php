<?php
namespace Destiny\Controllers;

use Destiny\Chat\ChatBanService;
use Destiny\Commerce\StatisticsService;
use Destiny\Common\Annotation\Audit;
use Destiny\Common\Annotation\ResponseBody;
use Destiny\Common\Exception;
use Destiny\Common\Log;
use Destiny\Common\Session\Session;
use Destiny\Common\User\UserRole;
use Destiny\Common\Utils\Date;
use Destiny\Common\ViewModel;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Application;
use Destiny\Common\User\UserService;
use Destiny\Commerce\SubscriptionsService;
use Destiny\Chat\ChatRedisService;
use Destiny\Common\Utils\FilterParams;
use Doctrine\DBAL\DBALException;

/**
 * @Controller
 */
class AdminController {

    /**
     * @Route ("/admin")
     * @HttpMethod ({"GET","POST"})
     *
     * @return string
     */
    public function admin() {
        if (Session::hasRole(UserRole::FINANCE))
            return 'redirect: /admin/income';
        else if (Session::hasRole(UserRole::MODERATOR))
            return 'redirect: /admin/users';
        else if (Session::hasRole(UserRole::EMOTES))
            return 'redirect: /admin/emotes';
        else if (Session::hasRole(UserRole::FLAIRS))
            return 'redirect: /admin/flairs';
        else if (Session::hasRole(UserRole::ADMIN))
            return 'redirect: /admin/dashboard';
        else
            return 'redirect: /'; // need an admin dashboard
    }

    /**
     * @Route ("/admin/dashboard")
     * @Secure ({"ADMIN"})
     * @HttpMethod ({"GET"})
     *
     * @param ViewModel $model
     * @return string
     */
    public function dashboard(ViewModel $model) {
        $model->title = 'Dashboard';
        return 'admin/dashboard';
    }

    /**
     * @Route ("/admin/income")
     * @Secure ({"FINANCE"})
     * @HttpMethod ({"GET","POST"})
     *
     * @param ViewModel $model
     * @return string
     */
    public function income(ViewModel $model) {
        $model->title = 'Income';
        return 'admin/income';
    }

    /**
     * @Route ("/admin/subscribers")
     * @Secure ({"FINANCE"})
     *
     * @param ViewModel $model
     * @return string
     *
     * @throws DBALException
     */
    public function adminSubscribers(ViewModel $model) {
        $subService = SubscriptionsService::instance ();
        $model->subscribersT4 = $subService->findByTier ( 4 );
        $model->subscribersT3 = $subService->findByTier ( 3 );
        $model->subscribersT2 = $subService->findByTier ( 2 );
        $model->subscribersT1 = $subService->findByTier ( 1 );
        $model->title = 'Subscribers';
        return 'admin/subscribers';
    }

    /**
     * @Route ("/admin/users")
     * @Secure ({"MODERATOR"})
     * @HttpMethod ({"GET","POST"})
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     *
     * @throws DBALException
     */
    public function users(array $params, ViewModel $model) {
        if (empty ($params ['page']))
            $params ['page'] = 1;
        if (empty ($params ['size']))
            $params ['size'] = 20;
        if (empty ($params ['search']))
            $params ['search'] = '';
        if (empty ($params ['feature']))
            $params ['feature'] = '';

        $model->user = Session::getCredentials()->getData();
        $userService = UserService::instance();

        if (!empty($params ['feature']))
            $model->users = $userService->findByFeature($params ['feature'], intval($params ['size']), intval($params ['page']));
        else if (!empty($params ['search']))
            $model->users = $userService->findBySearch($params ['search'], intval($params ['size']), intval($params ['page']));
        else
            $model->users = $userService->findAll(intval($params ['size']), intval($params ['page']));

        $model->size = $params ['size'];
        $model->page = $params ['page'];
        $model->search = $params ['search'];
        $model->feature = $params ['feature'];
        $model->features = $userService->getAllFeatures();
        $model->title = 'Users';
        return 'admin/users';
    }

    /**
     * @Route ("/admin/bans")
     * @Secure ({"MODERATOR"})
     *
     * @param ViewModel $model
     * @return string
     *
     * @throws DBALException
     */
    public function adminBans(ViewModel $model) {
        $model->activeBans = ChatBanService::instance()->getActiveBans();
        $model->title = 'Active Bans';
        return 'admin/bans';
    }

    /**
     * @Route ("/admin/bans/purgeall")
     * @Secure ({"MODERATOR"})
     * @Audit
     *
     * @throws DBALException
     */
    public function adminPurgeBans() {
        ChatRedisService::instance()->sendPurgeBans();
        ChatBanService::instance()->purgeBans();
        return 'redirect: /admin/bans';
    }

    /**
     * @Route ("/admin/chart/{type}")
     * @Secure ({"FINANCE"})
     * @ResponseBody
     *
     * @param array $params
     * @return array|false|mixed
     *
     * @throws Exception
     */
    public function chartData(array $params){
        FilterParams::required($params, 'type');
        $graphType = strtoupper($params['type']);
        $statisticsService = StatisticsService::instance();
        $cacheDriver = Application::getNsCache();
        $data = [];
        try {
            switch ($graphType) {
                case 'REVENUELASTXDAYS':
                    FilterParams::required($params, 'days');
                    $key = $graphType . intval($params['days']);
                    if (!$cacheDriver->contains($key)) {
                        $data = $statisticsService->getRevenueLastXDays(intval($params['days']));
                        $cacheDriver->save($key, $data, 30);
                    } else {
                        $data = $cacheDriver->fetch($key);
                    }
                    break;
                case 'REVENUELASTXMONTHS':
                    FilterParams::required($params, 'months');
                    $key = $graphType . intval($params['months']);
                    if (!$cacheDriver->contains($key)) {
                        $data = $statisticsService->getRevenueLastXMonths(intval($params['months']));
                        $cacheDriver->save($key, $data, 30);
                    } else {
                        $data = $cacheDriver->fetch($key);
                    }
                    break;
                case 'REVENUELASTXYEARS':
                    FilterParams::required($params, 'years');
                    $key = $graphType . intval($params['years']);
                    if (!$cacheDriver->contains($key)) {
                        $data = $statisticsService->getRevenueLastXYears(intval($params['years']));
                        $cacheDriver->save($key, $data, 30);
                    } else {
                        $data = $cacheDriver->fetch($key);
                    }
                    break;
                case 'NEWSUBSCRIBERSLASTXDAYS':
                    FilterParams::required($params, 'days');
                    $key = $graphType . intval($params['days']);
                    if (!$cacheDriver->contains($key)) {
                        $data = $statisticsService->getNewSubscribersLastXDays(intval($params['days']));
                        $cacheDriver->save($key, $data, 30);
                    } else {
                        $data = $cacheDriver->fetch($key);
                    }
                    break;
                case 'NEWSUBSCRIBERSLASTXMONTHS':
                    FilterParams::required($params, 'months');
                    $key = $graphType . intval($params['months']);
                    if (!$cacheDriver->contains($key)) {
                        $data = $statisticsService->getNewSubscribersLastXMonths(intval($params['months']));
                        $cacheDriver->save($key, $data, 30);
                    } else {
                        $data = $cacheDriver->fetch($key);
                    }
                    break;
                case 'NEWSUBSCRIBERSLASTXYEARS':
                    FilterParams::required($params, 'years');
                    $key = $graphType . intval($params['years']);
                    if (!$cacheDriver->contains($key)) {
                        $data = $statisticsService->getNewSubscribersLastXYears(intval($params['years']));
                        $cacheDriver->save($key, $data, 30);
                    } else {
                        $data = $cacheDriver->fetch($key);
                    }
                    break;
                case 'NEWTIEREDSUBSCRIBERSLASTXDAYS':
                    FilterParams::required($params, 'fromDate');
                    FilterParams::required($params, 'toDate');
                    $fromDate = Date::getDateTime($params['fromDate']);
                    $toDate = Date::getDateTime($params['toDate']);
                    $toDate->setTime(23, 59, 59);
                    $key = $graphType . $fromDate->format('Ymdhis') . $toDate->format('Ymdhis');
                    if (!$cacheDriver->contains($key)) {
                        $data = $statisticsService->getNewTieredSubscribersLastXDays($fromDate, $toDate);
                        $cacheDriver->save($key, $data, 30);
                    } else {
                        $data = $cacheDriver->fetch($key);
                    }
                    break;
                case 'NEWDONATIONSLASTXDAYS':
                    FilterParams::required($params, 'fromDate');
                    FilterParams::required($params, 'toDate');
                    $fromDate = Date::getDateTime($params['fromDate']);
                    $toDate = Date::getDateTime($params['toDate']);
                    $toDate->setTime(23, 59, 59);
                    $key = $graphType . $fromDate->format('Ymdhis') . $toDate->format('Ymdhis');
                    if (!$cacheDriver->contains($key)) {
                        $data = $statisticsService->getNewDonationsLastXDays($fromDate, $toDate);
                        $cacheDriver->save($key, $data, 30);
                    } else {
                        $data = $cacheDriver->fetch($key);
                    }
                    break;
            }
        } catch (\Exception $e) {
            Log::error('Error loading graph data. ' . $e->getMessage());
        }
        return $data;
    }

}
