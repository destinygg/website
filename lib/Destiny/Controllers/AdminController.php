<?php
namespace Destiny\Controllers;

use Destiny\Chat\ChatBanService;
use Destiny\Chat\ChatRedisService;
use Destiny\Commerce\StatisticsService;
use Destiny\Commerce\SubscriptionsService;
use Destiny\Commerce\SubscriptionStatus;
use Destiny\Common\Annotation\Audit;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\ResponseBody;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\Log;
use Destiny\Common\Session\Session;
use Destiny\Common\User\UserRole;
use Destiny\Common\User\UserService;
use Destiny\Common\User\UserStatus;
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\FilterParams;
use Destiny\Common\ViewModel;

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
            return 'redirect: /admin/moderation';
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
     * @Route ("/admin/income")
     * @Secure ({"FINANCE"})
     * @HttpMethod ({"GET","POST"})
     *
     * @param ViewModel $model
     * @return string
     */
    public function income(ViewModel $model) {
        $model->subInfo = [
            [
                'tierLabel' => 'Tier I',
                'oneMonthSubId' => '1-MONTH-SUB',
                'threeMonthSubId' => '3-MONTH-SUB'
            ],
            [
                'tierLabel' => 'Tier II',
                'oneMonthSubId' => '1-MONTH-SUB2',
                'threeMonthSubId' => '3-MONTH-SUB2'
            ],
            [
                'tierLabel' => 'Tier III',
                'oneMonthSubId' => '1-MONTH-SUB3',
                'threeMonthSubId' => '3-MONTH-SUB3'
            ],
            [
                'tierLabel' => 'Tier IV',
                'oneMonthSubId' => '1-MONTH-SUB4',
                'threeMonthSubId' => '3-MONTH-SUB4'
            ]
        ];
        $model->title = 'Income';
        return 'admin/income';
    }

    /**
     * @Route ("/admin/moderation")
     * @Secure ({"MODERATOR"})
     * @HttpMethod ({"GET","POST"})
     *
     * @param ViewModel $model
     * @return string
     */
    public function moderation(ViewModel $model) {
        $model->title = 'Moderation';
        return 'admin/moderation';
    }

    /**
     * @Route ("/admin/subscriptions")
     * @Secure ({"FINANCE"})
     *
     * @throws Exception
     */
    public function listSubscriptions(ViewModel $model, array $params): string {
        if (empty($params['page'])) {
            $params['page'] = 1;
        }
        if (empty($params['size'])) {
            $params['size'] = 60;
        }
        if (empty($params['search'])) {
            $params['search'] = '';
        }
        if (empty($params['tier'])) {
            $params['tier'] = '';
        }
        if (!isset($params['recurring']) || ($params['recurring'] !== '1' && $params['recurring'] !== '0')) {
            $params['recurring'] = '';
        }
        if (empty($params['status'])) {
            $params['status'] = '';
        }
        $subService = SubscriptionsService::instance();
        $model->title = 'Subscribers';
        $model->subscriptions = $subService->searchAll($params);
        $model->sizes = [50, 100, 250, 500];
        $model->search = $params['search'];
        $model->recurring = $params['recurring'];
        $model->status = $params['status'];
        $model->tier = $params['tier'];
        $model->tiers = Config::$a['commerce']['tiers'];
        $model->statuses = [
            SubscriptionStatus::_NEW,
            SubscriptionStatus::ACTIVE,
            SubscriptionStatus::CANCELLED,
            SubscriptionStatus::ERROR,
            SubscriptionStatus::EXPIRED,
            SubscriptionStatus::PENDING
        ];
        return 'admin/subscriptions';
    }

    /**
     * @Route ("/admin/users")
     * @Secure ({"MODERATOR"})
     * @HttpMethod ({"GET","POST"})
     *
     * @throws Exception
     */
    public function listUsers(array $params, ViewModel $model): string {
        if (empty($params ['page'])) {
            $params['page'] = 1;
        }
        if (empty($params ['size'])) {
            $params['size'] = 20;
        }
        if (empty($params ['search'])) {
            $params['search'] = '';
        }
        if (empty($params ['feature'])) {
            $params['feature'] = '';
        }
        if (empty($params ['role'])) {
            $params['role'] = '';
        }
        if (empty($params ['status'])) {
            $params['status'] = 'Active';
        }
        if (empty($params ['sort'])) {
            $params['sort'] = 'id';
        }
        if (empty($params ['order'])) {
            $params['order'] = 'DESC';
        }
        if ($params ['status'] == 'All') {
            $params ['status'] = '';
        }
        $userService = UserService::instance();
        $model->user = Session::getCredentials()->getData();
        $model->features = $userService->getAllFeatures();
        $model->roles = $userService->getAllRoles();
        $model->statuses = ['All', UserStatus::ACTIVE, UserStatus::REDACTED, UserStatus::DELETED];
        $model->users = $userService->searchAll($params);
        $model->sizes = [50, 100, 250, 500];
        $model->size = $params ['size'];
        $model->page = $params ['page'];
        $model->sort = $params ['sort'];
        $model->order = $params ['order'];
        $model->search = $params ['search'];
        $model->feature = $params ['feature'];
        $model->role = $params ['role'];
        $model->status = $params ['status'];
        $model->title = 'Users';
        return 'admin/users';
    }

    /**
     * @Route ("/admin/bans")
     * @Secure ({"MODERATOR"})
     *
     * @throws Exception
     */
    public function adminBans(ViewModel $model): string {
        $model->activeBans = ChatBanService::instance()->getActiveBans();
        $model->title = 'Active Bans';
        return 'admin/bans';
    }

    /**
     * @Route ("/admin/bans/purgeall")
     * @Secure ({"MODERATOR"})
     * @Audit
     *
     * @throws Exception
     */
    public function adminPurgeBans(): string {
        ChatRedisService::instance()->sendPurgeBans();
        ChatBanService::instance()->purgeBans();
        return 'redirect: /admin/bans';
    }

    /**
     * @Route ("/admin/chart/users/{type}")
     * @Secure ({"MODERATOR"})
     * @ResponseBody
     */
    public function chartUsersData(array $params) {
        $statisticsService = StatisticsService::instance();
        $cacheDriver = Application::getNsCache();
        $data = [];
        try {
            FilterParams::required($params, 'type');
            $graphType = strtoupper($params['type']);
            switch ($graphType) {
                case 'NEWUSERSLASTXDAYS':
                    FilterParams::required($params, 'fromDate');
                    FilterParams::required($params, 'toDate');
                    $fromDate = Date::getDateTime($params['fromDate']);
                    $toDate = Date::getDateTime($params['toDate']);
                    $toDate->setTime(23, 59, 59);
                    $key = $graphType . $fromDate->format('Ymdhis') . $toDate->format('Ymdhis');
                    if (!$cacheDriver->contains($key)) {
                        $data = $statisticsService->getNewUsersLastXDays($fromDate, $toDate);
                        $cacheDriver->save($key, $data, 30);
                    } else {
                        $data = $cacheDriver->fetch($key);
                    }
                    break;
                case 'NEWUSERSANDBANSLASTXDAYS':
                    FilterParams::required($params, 'fromDate');
                    FilterParams::required($params, 'toDate');
                    $fromDate = Date::getDateTime($params['fromDate']);
                    $toDate = Date::getDateTime($params['toDate']);
                    $toDate->setTime(23, 59, 59);
                    $key = $graphType . $fromDate->format('Ymdhis') . $toDate->format('Ymdhis');
                    if (!$cacheDriver->contains($key)) {
                        $data = [
                            $statisticsService->getNewUsersLastXDays($fromDate, $toDate),
                            $statisticsService->getBansLastXDays($fromDate, $toDate),
                        ];
                        $cacheDriver->save($key, $data, 30);
                    } else {
                        $data = $cacheDriver->fetch($key);
                    }
                    break;
                case 'NEWUSERSLASTXMONTHS':
                    FilterParams::required($params, 'fromDate');
                    FilterParams::required($params, 'toDate');
                    $fromDate = Date::getDateTime($params['fromDate']);
                    $toDate = Date::getDateTime($params['toDate']);
                    $toDate->setTime(23, 59, 59);
                    $key = $graphType . $fromDate->format('Ymdhis') . $toDate->format('Ymdhis');
                    if (!$cacheDriver->contains($key)) {
                        $data = $statisticsService->getNewUsersLastXMonths($fromDate, $toDate);
                        $cacheDriver->save($key, $data, 30);
                    } else {
                        $data = $cacheDriver->fetch($key);
                    }
                    break;
                case 'NEWUSERSLASTXYEARS':
                    FilterParams::required($params, 'fromDate');
                    FilterParams::required($params, 'toDate');
                    $fromDate = Date::getDateTime($params['fromDate']);
                    $toDate = Date::getDateTime($params['toDate']);
                    $toDate->setTime(23, 59, 59);
                    $key = $graphType . $fromDate->format('Ymdhis') . $toDate->format('Ymdhis');
                    if (!$cacheDriver->contains($key)) {
                        $data = $statisticsService->getNewUsersLastXYears($fromDate, $toDate);
                        $cacheDriver->save($key, $data, 30);
                    } else {
                        $data = $cacheDriver->fetch($key);
                    }
                    break;
            }
        } catch (Exception $e) {
            Log::error('Error loading graph data. ' . $e->getMessage());
        }
        return $data;
    }

    /**
     * @Route ("/admin/chart/finance/{type}")
     * @Secure ({"FINANCE"})
     * @ResponseBody
     *
     * @return array|false
     */
    public function chartFinanceData(array $params) {
        $statisticsService = StatisticsService::instance();
        $cacheDriver = Application::getNsCache();
        $data = [];
        try {
            FilterParams::required($params, 'type');
            $graphType = strtoupper($params['type']);
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
                case 'CURRENTACTIVESUBS':
                    $data = $statisticsService->getActiveSubCounts();
                    break;
                case 'HISTORICALACTIVESUBS':
                    $data = $statisticsService->getHistoricalActiveSubs();
                    break;
            }
        } catch (Exception $e) {
            Log::error('Error loading graph data. ' . $e->getMessage());
        }
        return $data;
    }

    /**
     * @Route ("/admin/audit")
     * @Secure ({"MODERATOR"})
     * @HttpMethod ({"GET"})
     * @throws Exception
     */
    public function auditLogList(ViewModel $model): string {
        $userService = UserService::instance();
        $model->logs = $userService->getAuditLog();
        return 'admin/auditlog';
    }

}
