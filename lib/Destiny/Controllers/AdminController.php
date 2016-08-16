<?php
namespace Destiny\Controllers;

use Destiny\Commerce\StatisticsService;
use Destiny\Common\Session;
use Destiny\Common\User\UserFeaturesService;
use Destiny\Common\User\UserRole;
use Destiny\Common\Utils\Date;
use Destiny\Common\ViewModel;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Application;
use Destiny\Common\Response;
use Destiny\Common\Utils\Http;
use Destiny\Common\MimeType;
use Destiny\Common\User\UserService;
use Destiny\Commerce\SubscriptionsService;
use Destiny\Chat\ChatIntegrationService;
use Destiny\Common\Utils\FilterParams;

/**
 * @Controller
 */
class AdminController {

    /**
     * @Route ("/admin")
     * @Secure ({"ADMIN"})
     * @HttpMethod ({"GET","POST"})
     *
     * @return string
     */
    public function admin() {
        if (Session::hasRole(UserRole::FINANCE))
            return 'redirect: /admin/income';
        else
            return 'redirect: /admin/users';
    }

    /**
     * @Route ("/admin/income")
     * @Secure ({"ADMIN","FINANCE"})
     * @HttpMethod ({"GET","POST"})
     *
     * @return string
     */
    public function income() {
        return 'admin/income';
    }

    /**
     * @Route ("/admin/users")
     * @Secure ({"ADMIN"})
     * @HttpMethod ({"GET","POST"})
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     */
    public function users(array $params, ViewModel $model) {
        if (empty ( $params ['page'] ))
            $params ['page'] = 1;
        if (empty ( $params ['size'] ))
            $params ['size'] = 20;
        if (empty ( $params ['search'] ))
            $params ['search'] = '';
        if (empty ( $params ['feature'] ))
            $params ['feature'] = '';

        $model->title = 'Administration';
        $model->user = Session::getCredentials ()->getData ();

        if (!empty($params ['feature']))
            $model->users = UserService::instance()->findByFeature($params ['feature'], intval($params ['size']), intval($params ['page']));
        else if (!empty($params ['search']))
            $model->users = UserService::instance()->findBySearch($params ['search'], intval($params ['size']), intval($params ['page']));
        else
            $model->users = UserService::instance()->findAll(intval($params ['size']), intval($params ['page']));

        $model->size = $params ['size'];
        $model->page = $params ['page'];
        $model->search = $params ['search'];
        $model->feature = $params ['feature'];

        $userFeaturesService = UserFeaturesService::instance ();
        $model->features = $userFeaturesService->getNonPseudoFeatures ();

        $model->title = 'Admin';
        return 'admin/users';
    }

    /**
     * @Route ("/admin/subscribers")
     * @Secure ({"ADMIN"})
     *
     * @param ViewModel $model
     * @return string
     */
    public function adminSubscribers(ViewModel $model) {
        $subService = SubscriptionsService::instance ();
        $model->subscribersT4 = $subService->getSubscriptionsByTier ( 4 );
        $model->subscribersT3 = $subService->getSubscriptionsByTier ( 3 );
        $model->subscribersT2 = $subService->getSubscriptionsByTier ( 2 );
        $model->subscribersT1 = $subService->getSubscriptionsByTier ( 1 );
        $model->title = 'Subscribers';
        return 'admin/subscribers';
    }

    /**
     * @Route ("/admin/bans")
     * @Secure ({"ADMIN"})
     *
     * @param ViewModel $model
     * @return string
     */
    public function adminBans(ViewModel $model) {
        $chatService = ChatIntegrationService::instance ();
        $model->activeBans = $chatService->getActiveBans();
        $model->title = 'Active Bans';
        return 'admin/bans';
    }

    /**
     * @Route ("/admin/bans/purgeall")
     * @Secure ({"ADMIN"})
     */
    public function adminPurgeBans() {
        $chatService = ChatIntegrationService::instance ();
        $chatService->purgeBans();
        return 'redirect: /admin/bans';
    }

    /**
     * @Route ("/admin/chart/{type}")
     * @Secure ({"ADMIN"})
     *
     * @param array $params
     * @return Response
     */
    public function chartData(array $params){
        FilterParams::required($params, 'type');
        $statisticsService = StatisticsService::instance();
        $cacheDriver = Application::instance()->getCacheDriver ();
        $data = array();
        switch(strtoupper($params['type'])){
            case 'REVENUELASTXDAYS':
                FilterParams::required($params, 'days');
                $key = 'RevenueLastXDays '. intval($params['days']);
                if(!$cacheDriver->contains($key)){
                    $data = $statisticsService->getRevenueLastXDays( intval($params['days']) );
                    $cacheDriver->save($key, $data, 30);
                } else {
                    $data = $cacheDriver->fetch($key);
                }
                break;
            case 'REVENUELASTXMONTHS':
                FilterParams::required($params, 'months');
                $key = 'RevenueLastXMonths '. intval($params['months']);
                if(!$cacheDriver->contains($key)){
                    $data = $statisticsService->getRevenueLastXMonths( intval($params['months']) );
                    $cacheDriver->save($key, $data, 30);
                } else {
                    $data = $cacheDriver->fetch($key);
                }
                break;
            case 'REVENUELASTXYEARS':
                FilterParams::required($params, 'years');
                $key = 'RevenueLastXYears '. intval($params['years']);
                if(!$cacheDriver->contains($key)){
                    $data = $statisticsService->getRevenueLastXYears( intval($params['years']) );
                    $cacheDriver->save($key, $data, 30);
                } else {
                    $data = $cacheDriver->fetch($key);
                }
                break;
            case 'NEWSUBSCRIBERSLASTXDAYS':
                FilterParams::required($params, 'days');
                $key = 'NewSubscribersLastXDays '. intval($params['days']);
                if(!$cacheDriver->contains($key)){
                    $data = $statisticsService->getNewSubscribersLastXDays( intval($params['days']) );
                    $cacheDriver->save($key, $data, 30);
                } else {
                    $data = $cacheDriver->fetch($key);
                }
                break;
            case 'NEWSUBSCRIBERSLASTXMONTHS':
                FilterParams::required($params, 'months');
                $key = 'NewSubscribersLastXMonths '. intval($params['months']);
                if(!$cacheDriver->contains($key)){
                    $data = $statisticsService->getNewSubscribersLastXMonths( intval($params['months']) );
                    $cacheDriver->save($key, $data, 30);
                } else {
                    $data = $cacheDriver->fetch($key);
                }
                break;
            case 'NEWSUBSCRIBERSLASTXYEARS':
                FilterParams::required($params, 'years');
                $key = 'NewSubscribersLastXYears '. intval($params['years']);
                if(!$cacheDriver->contains($key)){
                    $data = $statisticsService->getNewSubscribersLastXYears( intval($params['years']) );
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
                $key = 'NewTieredSubscribersLastXDays'. $fromDate->format('Ymdhis'). $toDate->format('Ymdhis');
                if(!$cacheDriver->contains($key)){
                    $data = $statisticsService->getNewTieredSubscribersLastXDays( $fromDate, $toDate );
                    $cacheDriver->save($key, $data, 30);
                } else {
                    $data = $cacheDriver->fetch($key);
                }
                break;
        }
        $response = new Response ( Http::STATUS_OK, json_encode ( $data ) );
        $response->addHeader ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
        return $response;
    }

}
