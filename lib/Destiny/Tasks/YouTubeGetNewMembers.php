<?php
namespace Destiny\Tasks;

use Destiny\Common\Annotation\Schedule;
use Destiny\Common\Authentication\AuthenticationService;
use Destiny\Common\Authentication\AuthProvider;
use Destiny\Common\Config;
use Destiny\Common\Cron\TaskInterface;
use Destiny\YouTube\YouTubeAdminApiService;
use Destiny\YouTube\YouTubeMembershipService;

/**
 * @Schedule(frequency=5,period="minute")
 */
class YouTubeGetNewMembers implements TaskInterface {
    public function execute() {
        if (!Config::$a[AuthProvider::YOUTUBE_BROADCASTER]['sync_memberships']) {
            return;
        }

        $youTubeAdminApiService = YouTubeAdminApiService::instance();
        $newMemberships = $youTubeAdminApiService->getNewMemberships();

        $youTubeMembershipService = YouTubeMembershipService::instance();
        $channelIdsToUpdate = $youTubeMembershipService->addMemberships($newMemberships);
        $userIdsToUpdate = $youTubeMembershipService->getUserIdsForChannelIds($channelIdsToUpdate);
        foreach ($userIdsToUpdate as $userIdToUpdate) {
            AuthenticationService::instance()->flagUserForUpdate($userIdsToUpdate);
        }
    }
}