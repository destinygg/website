<?php
namespace Destiny\Google;

use Destiny\Common\Authentication\AuthProvider;
use Destiny\Common\Session\Session;

class YouTubeAuthHandler extends GoogleAuthHandler {
    public $authProvider = AuthProvider::YOUTUBE;

    function getAuthorizationUrl(
        $scope = [
            'https://www.googleapis.com/auth/youtube',
            'https://www.googleapis.com/auth/youtube.channel-memberships.creator',
            'https://www.googleapis.com/auth/youtube.force-ssl',
            'https://www.googleapis.com/auth/youtube.readonly'
        ],
        $claims = ''
    ): string {
        return parent::getAuthorizationUrl($scope, $claims);
    }
}
