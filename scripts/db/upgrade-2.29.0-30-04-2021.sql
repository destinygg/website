CREATE TABLE IF NOT EXISTS `youtube_membership_levels` (
    `membershipLevelId` varchar(255) NOT NULL,
    `creatorChannelId` varchar(255) NOT NULL,
    `name` varchar(255) NOT NULL,
    PRIMARY KEY (`membershipLevelId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `youtube_members` (
    `memberChannelId` varchar(255) NOT NULL,
    `creatorChannelId` varchar(255) NOT NULL,
    `membershipLevelId` varchar(255) NOT NULL,
    PRIMARY KEY (`memberChannelId`, `creatorChannelId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `users_youtube_channels` (
    `userId` int(14) NOT NULL,
    `channelId` varchar(255) NOT NULL,
    `channelTitle` varchar(255) NOT NULL,
    PRIMARY KEY `channelId` (`channelId`),
    CONSTRAINT `users_youtube_channel_ids_ibfk1` FOREIGN KEY (`userId`) REFERENCES `dfl_users` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

UPDATE `dfl_users_auth`
SET `authProvider` = 'youtubebroadcaster'
WHERE `authProvider` = 'youtube';
