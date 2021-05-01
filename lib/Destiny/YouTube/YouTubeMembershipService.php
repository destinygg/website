<?php
namespace Destiny\YouTube;

use Destiny\Common\Application;
use Destiny\Common\Service;
use Destiny\Common\Utils\Compare;
use PDO;

class YouTubeMembershipService extends Service {
    /**
     * @link https://developers.google.com/youtube/v3/docs/membershipsLevels
     * @param array $membershipLevel A `youtube#membershipsLevel` resource.
     * @throws Exception
     */
    public function addMembershipLevel(array $membershipLevel) {
        $db = Application::getDbConn();
        // Update on existing `membershipLevelId` to handle simple name
        // changes.
        $stmt = $db->prepare(
            'INSERT INTO `youtube_membership_levels` (`membershipLevelId`, `creatorChannelId`, `name`)
            VALUES (:membershipLevelId, :creatorChannelId, :name)
            ON DUPLICATE KEY
            UPDATE `name` = :name'
        );
        $stmt->bindValue('membershipLevelId', $membershipLevel['id']);
        $stmt->bindValue('creatorChannelId', $membershipLevel['snippet']['creatorChannelId']);
        $stmt->bindValue('name', $membershipLevel['snippet']['displayName']);
        $stmt->execute();
    }

    /**
     * @throws Exception
     */
    public function addMembershipLevels(array $membershipLevels) {
        foreach ($membershipLevels as $membershipLevel) {
            $this->addMembershipLevel($membershipLevel);
        }
    }

    /**
     * @link https://developers.google.com/youtube/v3/docs/members
     * @param array $membership A `youtube#member` resource.
     * @throws Exception
     */
    public function addMembership(array $membership) {
        $db = Application::getDbConn();
        // Update `membershipLevelId` when there's a record with a matching
        // `memberChannelId` and `creatorChannelId` to handle membership
        // upgrades.
        $stmt = $db->prepare(
            'INSERT INTO `youtube_members` (`memberChannelId`, `creatorChannelId`, `membershipLevelId`)
            VALUES (:memberChannelId, :creatorChannelId, :membershipLevelId)
            ON DUPLICATE KEY
            UPDATE `membershipLevelId` = :membershipLevelId'
        );
        $stmt->bindValue('memberChannelId', $membership['snippet']['memberDetails']['channelId']);
        $stmt->bindValue('creatorChannelId', $membership['snippet']['creatorChannelId']);
        $stmt->bindValue('membershipLevelId', $membership['membershipsDetails']['highestAccessibleLevel']);
        $stmt->execute();
    }

    /**
     * @throws Exception
     */
    public function addMemberships(array $memberships): array {
        $channelIdsToUpdate = [];
        foreach ($memberships as $membership) {
            $this->addMembership($membership);
            $channelIdsToUpdate[] = $membership['snippet']['memberDetails']['channelId'];
        }

        return $channelIdsToUpdate;
    }

    /**
     * Synchronizes the `youtube_members` table to match the supplied array of
     * active memberships.
     *
     * @param array $activeMemberships An array of `youtube#member` resources.
     * @return array An array containing the channel IDs of users whose memberships were updated.
     */
    public function syncMemberships(array $activeMemberships): array {
        $db = Application::getDbConn();

        // Remove irrelevant properties.
        $activeMemberships = array_map(function($membership) {
            return [
                'memberChannelId' => $membership['snippet']['memberDetails']['channelId'],
                'creatorChannelId' =>  $membership['snippet']['creatorChannelId'],
                'membershipLevelId' => $membership['membershipsDetails']['highestAccessibleLevel']
            ];
        }, $activeMemberships);

        $stmt = $db->executeQuery('SELECT * FROM `youtube_members`');
        $membershipsFromDb = $stmt->fetchAll();

        $membershipsToDelete = array_udiff($membershipsFromDb, $activeMemberships, [Compare::class, 'json_compare']);
        foreach ($membershipsToDelete as $membershipToDelete) {
            $db->delete('youtube_members', $membershipToDelete);
        }

        $membershipsToAdd = array_udiff($activeMemberships, $membershipsFromDb, [Compare::class, 'json_compare']);
        foreach ($membershipsToAdd as $membershipToAdd) {
            $db->insert('youtube_members', $membershipToAdd);
        }

        $channelIdsToUpdate = array_unique(array_merge(
            array_map(function($membership) {
                return $membership['memberChannelId'];
            }, $membershipsToDelete),
            array_map(function($membership) {
                return $membership['memberChannelId'];
            }, $membershipsToAdd)
        ));

        return $channelIdsToUpdate;
    }

    /**
     * @return string|false The level ID of the user's active membership, or `false` if the user isn't a member.
     * @throws Exception
     */
    public function getMembershipDetailsForUserId(int $userId) {
        $db = Application::getDbConn();
        $stmt = $db->executeQuery(
            'SELECT `yt_mems`.`membershipLevelId`, `u_yt_chans`.`channelTitle`, `yt_mem_levs`.`name`
            FROM `users_youtube_channels` AS `u_yt_chans`
            INNER JOIN `youtube_members` AS `yt_mems`
                ON `yt_mems`.`memberChannelId` = `u_yt_chans`.`channelId`
            INNER JOIN `youtube_membership_levels` AS `yt_mem_levs`
                ON `yt_mem_levs`.`membershipLevelId` = `yt_mems`.`membershipLevelId`
            WHERE `userId` = ?',
            [$userId],
            [PDO::PARAM_INT]
        );
        return $stmt->fetch();
    }

    /**
     * @param array $channels An array of `youtube#channel` resource arrays.
     * @throws Exception
     */
    public function addChannelsForUserId(array $channels, int $userId) {
        $db = Application::getDbConn();
        foreach ($channels as $channel) {
            // Update `userId` when the `channelId` already exists to prevent
            // account sharing.
            $stmt = $db->prepare(
                'INSERT INTO `users_youtube_channels` (`userId`, `channelId`, `channelTitle`)
                VALUES (:userId, :channelId, :channelTitle)
                ON DUPLICATE KEY
                UPDATE `userId` = :userId, `channelTitle` = :channelTitle'
            );
            $stmt->bindValue('userId', $userId);
            $stmt->bindValue('channelId', $channel['id']);
            $stmt->bindValue('channelTitle', $channel['snippet']['title']);
            $stmt->execute();
        }
    }

    /**
     * @throws Exception
     */
    public function getUserIdsForChannelIds(array $channelIds): array {
        $db = Application::getDbConn();
        $stmt = $db->executeQuery(
            'SELECT DISTINCT `userId`
            FROM `users_youtube_channels`
            WHERE `channelId` IN (?)',
            [$channelIds],
            [\Doctrine\DBAL\Connection::PARAM_STR_ARRAY]
        );
        return $stmt->fetchAll();
    }
}
