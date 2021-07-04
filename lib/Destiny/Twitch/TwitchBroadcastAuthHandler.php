<?php
namespace Destiny\Twitch;

use Destiny\Common\Authentication\AuthProvider;

/**
 * @method static TwitchBroadcastAuthHandler instance()
 */
class TwitchBroadcastAuthHandler extends TwitchAuthHandler {

    public $authProvider = AuthProvider::TWITCHBROADCAST;

    public function getAuthorizationUrl($scope = ['openid', 'channel:read:subscriptions', 'user:read:email', 'channel_subscriptions', 'channel_check_subscription'], $claims = '{"userinfo":{"picture":null, "email":null, "email_verified":null, "preferred_username": null}}'): string {
        return parent::getAuthorizationUrl($scope, $claims);
    }

}
