<?php
namespace Destiny\Tasks;

use Destiny\Common\Authentication\RememberMeService;
use Destiny\Commerce\SubscriptionsService;
use TaskInterface;

class SubscriptionExpire implements TaskInterface {

    public function execute() {
        RememberMeService::instance ()->clearExpiredRememberMe ();
        SubscriptionsService::instance ()->expiredSubscriptions ();
    }

}