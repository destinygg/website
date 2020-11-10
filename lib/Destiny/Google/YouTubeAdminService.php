<?php
namespace Destiny\Google;

use Destiny\Common\Authentication\AbstractAuthService;
use Destiny\Common\Authentication\AuthProvider;

class YouTubeAdminService extends AbstractAuthService {
    public $provider = AuthProvider::YOUTUBE;

    function afterConstruct() {
        parent::afterConstruct();
        $this->authHandler = YouTubeAuthHandler::instance();
    }
}
