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
 * @Schedule(frequency=24,period="hour")
 */
class YouTubeMembersFullSync implements TaskInterface {
    public function execute() {
        if (!Config::$a[AuthProvider::YOUTUBE_BROADCASTER]['sync_memberships']) {
            return;
        }

        $this->syncMembershipLevels();
        $this->syncMemberships();
    }

    private function syncMembershipLevels() {
        $youTubeAdminApiService = YouTubeAdminApiService::instance();
        $membershipLevels = $youTubeAdminApiService->getMembershipLevels();

        $youTubeMembershipService = YouTubeMembershipService::instance();
        $youTubeMembershipService->addMembershipLevels($membershipLevels);
    }

    private function syncMemberships() {
        $youTubeAdminApiService = YouTubeAdminApiService::instance();
        $allMemberships = $youTubeAdminApiService->getAllMemberships();

        $youTubeMembershipService = YouTubeMembershipService::instance();
        $channelIdsToUpdate = $youTubeMembershipService->syncMemberships($allMemberships);
        $userIdsToUpdate = $youTubeMembershipService->getUserIdsForChannelIds($channelIdsToUpdate);

        foreach ($userIdsToUpdate as $userIdToUpdate) {
            AuthenticationService::instance()->flagUserForUpdate($userIdsToUpdate);
        }
    }
}