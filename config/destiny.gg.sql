/*!40101 SET NAMES utf8 */;
/*!40101 SET SQL_MODE=''*/;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

CREATE TABLE `bans` (
  `id` int(14) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(14) unsigned NOT NULL,
  `targetuserid` int(14) unsigned NOT NULL,
  `ipaddress` text,
  `reason` text NOT NULL,
  `starttimestamp` datetime NOT NULL,
  `endtimestamp` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `targetuserid` (`targetuserid`),
  KEY `endtimestamp` (`endtimestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `dfl_features` (
  `featureId` int(14) NOT NULL AUTO_INCREMENT,
  `featureName` varchar(100) NOT NULL,
  `featureLabel` varchar(100) NOT NULL,
  `imageId` int(14) DEFAULT NULL,
  `locked` tinyint(1) NOT NULL,
  `hidden` tinyint(1) NOT NULL,
  `priority` tinyint(2) NOT NULL,
  `color` varchar(16) DEFAULT NULL,
  `createdDate` datetime NOT NULL,
  `modifiedDate` datetime NOT NULL,
  PRIMARY KEY (`featureId`),
  UNIQUE KEY `featureName` (`featureName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `dfl_orders_ipn` (
  `id` int(14) NOT NULL AUTO_INCREMENT,
  `ipnTrackId` varchar(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `ipnTransactionType` varchar(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `ipnTransactionId` varchar(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `ipnData` text CHARACTER SET utf8mb4,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `dfl_orders_payments` (
  `paymentId` int(14) NOT NULL AUTO_INCREMENT,
  `donationId` int(14) DEFAULT NULL,
  `subscriptionId` int(14) DEFAULT NULL,
  `amount` float DEFAULT NULL,
  `currency` varchar(4) DEFAULT NULL,
  `transactionId` varchar(50) DEFAULT NULL,
  `transactionType` varchar(50) DEFAULT NULL,
  `paymentType` varchar(50) DEFAULT NULL,
  `payerId` varchar(50) DEFAULT NULL,
  `paymentStatus` varchar(50) DEFAULT NULL,
  `paymentDate` datetime DEFAULT NULL,
  `createdDate` datetime DEFAULT NULL,
  PRIMARY KEY (`paymentId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `dfl_roles` (
  `roleId` int(14) NOT NULL,
  `roleName` varchar(100) CHARACTER SET utf8mb4 NOT NULL,
  `roleLabel` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  PRIMARY KEY (`roleId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `dfl_scheduled_tasks` (
  `action` varchar(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `lastExecuted` varchar(100) CHARACTER SET utf8mb4 DEFAULT NULL,
  `frequency` int(14) DEFAULT NULL,
  `period` varchar(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `executeCount` int(14) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `dfl_users` (
  `userId` int(14) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `country` varchar(4) DEFAULT '',
  `createdDate` datetime DEFAULT NULL,
  `modifiedDate` datetime DEFAULT NULL,
  `userStatus` varchar(20) DEFAULT NULL,
  `allowGifting` tinyint(1) DEFAULT '1',
  `allowChatting` tinyint(1) DEFAULT '1',
  `allowNameChange` tinyint(1) DEFAULT '0',
  `istwitchsubscriber` tinyint(1) DEFAULT '0',
  `discorduuid` varchar(36) CHARACTER SET ascii DEFAULT NULL,
  `discordname` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `chatsettings` blob,
  `minecraftuuid` varchar(36) DEFAULT NULL,
  `minecraftname` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`userId`),
  UNIQUE KEY `discorduuid` (`discorduuid`),
  UNIQUE KEY `discordname` (`discordname`),
  KEY `minecraftuuid` (`minecraftuuid`),
  KEY `minecraftname` (`minecraftname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `dfl_users_auth` (
  `id` int(14) NOT NULL AUTO_INCREMENT,
  `userId` int(14) DEFAULT NULL,
  `authProvider` varchar(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `authId` varchar(100) CHARACTER SET utf8mb4 DEFAULT NULL,
  `authDetail` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `authEmail` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `expiresIn` int(11) DEFAULT NULL,
  `accessToken` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `refreshToken` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `createdDate` datetime DEFAULT NULL,
  `modifiedDate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UQ_PROVIDER` (`authProvider`,`authId`),
  KEY `userId` (`userId`),
  CONSTRAINT `dfl_users_auth_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `dfl_users` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `dfl_users_auth_token` (
  `authTokenId` int(14) NOT NULL AUTO_INCREMENT,
  `userId` int(14) DEFAULT NULL,
  `authToken` varchar(255) DEFAULT NULL,
  `createdDate` datetime DEFAULT NULL,
  PRIMARY KEY (`authTokenId`),
  KEY `userId` (`userId`),
  KEY `authToken` (`authToken`(191)),
  CONSTRAINT `dfl_users_auth_token_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `dfl_users` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `dfl_users_features` (
  `id` int(14) NOT NULL AUTO_INCREMENT,
  `userId` int(14) NOT NULL,
  `featureId` int(14) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`),
  KEY `featureId` (`featureId`),
  CONSTRAINT `dfl_users_features_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `dfl_users` (`userId`),
  CONSTRAINT `dfl_users_features_ibfk_2` FOREIGN KEY (`featureId`) REFERENCES `dfl_features` (`featureId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `dfl_users_roles` (
  `userId` int(14) NOT NULL,
  `roleId` int(14) NOT NULL,
  KEY `userId` (`userId`),
  KEY `roleId` (`roleId`),
  CONSTRAINT `dfl_users_roles_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `dfl_users` (`userId`),
  CONSTRAINT `dfl_users_roles_ibfk_2` FOREIGN KEY (`roleId`) REFERENCES `dfl_roles` (`roleId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `dfl_users_subscriptions` (
  `subscriptionId` int(14) NOT NULL AUTO_INCREMENT,
  `subscriptionSource` varchar(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `subscriptionType` varchar(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `subscriptionTier` tinyint(4) DEFAULT NULL,
  `userId` int(14) DEFAULT NULL,
  `endDate` datetime DEFAULT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `recurring` tinyint(4) DEFAULT NULL,
  `paymentProfileId` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `billingStartDate` datetime DEFAULT NULL,
  `billingNextDate` datetime DEFAULT NULL,
  `paymentStatus` varchar(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `gifter` int(14) DEFAULT NULL,
  `cancelledBy` int(14) DEFAULT NULL,
  `cancelDate` datetime DEFAULT NULL,
  `createdDate` datetime DEFAULT NULL,
  PRIMARY KEY (`subscriptionId`),
  KEY `userId` (`userId`),
  KEY `userStatus` (`userId`,`status`),
  CONSTRAINT `dfl_users_subscriptions_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `dfl_users` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `donations` (
  `id` int(14) NOT NULL AUTO_INCREMENT,
  `userid` int(14) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `currency` varchar(4) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `message` blob,
  `status` varchar(100) DEFAULT NULL,
  `invoiceId` varchar(255) DEFAULT NULL,
  `timestamp` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `emotes` (
  `id` int(14) NOT NULL AUTO_INCREMENT,
  `prefix` varchar(180) CHARACTER SET utf8mb4 DEFAULT NULL,
  `imageId` int(14) NOT NULL,
  `twitch` tinyint(1) NOT NULL,
  `draft` tinyint(1) NOT NULL,
  `styles` blob,
  `theme` int(14) NOT NULL,
  `createdDate` datetime NOT NULL,
  `modifiedDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `prefix` (`prefix`,`theme`),
  KEY `theme` (`theme`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE `images` (
  `id` int(14) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `hash` varchar(130) NOT NULL,
  `type` varchar(130) NOT NULL,
  `size` bigint(20) NOT NULL,
  `width` int(6) NOT NULL,
  `height` int(6) NOT NULL,
  `tag` varchar(100) NOT NULL,
  `createdDate` datetime NOT NULL,
  `modifiedDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `hash` (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `oauth_access_tokens` (
  `tokenId` int(14) NOT NULL AUTO_INCREMENT,
  `clientId` int(14) DEFAULT NULL,
  `userId` int(14) NOT NULL,
  `scope` varchar(100) NOT NULL,
  `token` varchar(64) NOT NULL,
  `refresh` varchar(64) DEFAULT NULL,
  `expireIn` int(11) DEFAULT NULL,
  `createdDate` datetime DEFAULT NULL,
  PRIMARY KEY (`tokenId`),
  KEY `IDX_TOKEN` (`token`),
  KEY `IDX_REFRESH` (`clientId`,`refresh`),
  KEY `IDX_USER` (`userId`),
  CONSTRAINT `oauth_access_tokens_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `dfl_users` (`userId`),
  CONSTRAINT `oauth_access_tokens_ibfk_2` FOREIGN KEY (`clientId`) REFERENCES `oauth_client_details` (`clientId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `oauth_client_details` (
  `clientId` int(14) NOT NULL AUTO_INCREMENT,
  `ownerId` int(14) NOT NULL,
  `clientCode` varchar(32) DEFAULT NULL,
  `clientSecret` varchar(64) DEFAULT NULL,
  `clientName` varchar(100) DEFAULT NULL,
  `redirectUrl` varchar(255) DEFAULT NULL,
  `createdDate` datetime DEFAULT NULL,
  `modifiedDate` datetime DEFAULT NULL,
  PRIMARY KEY (`clientId`),
  UNIQUE KEY `UQ_CODE` (`clientCode`),
  KEY `IDX_OWNER` (`ownerId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `privatemessages` (
  `id` int(14) NOT NULL AUTO_INCREMENT,
  `userid` int(14) NOT NULL,
  `targetuserid` int(14) NOT NULL,
  `message` blob NOT NULL,
  `timestamp` datetime NOT NULL,
  `isread` tinyint(1) DEFAULT '0',
  `deletedbysender` tinyint(1) DEFAULT '0',
  `deletedbyreceiver` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `targetuserid` (`targetuserid`),
  KEY `userid` (`userid`),
  KEY `target_user` (`targetuserid`,`userid`),
  KEY `time` (`timestamp`),
  KEY `isread` (`isread`),
  KEY `deletedbysender` (`deletedbysender`),
  KEY `deletedbyreceiver` (`deletedbyreceiver`),
  CONSTRAINT `privatemessages_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `dfl_users` (`userId`),
  CONSTRAINT `privatemessages_ibfk_2` FOREIGN KEY (`targetuserid`) REFERENCES `dfl_users` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `users_audit` (
  `id` int(14) NOT NULL AUTO_INCREMENT,
  `userid` int(14) NOT NULL,
  `username` varchar(255) NOT NULL,
  `timestamp` datetime NOT NULL,
  `requesturi` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `users_deleted` (
  `userid` int(14) DEFAULT NULL,
  `timestamp` datetime DEFAULT NULL,
  `deletedby` int(14) DEFAULT NULL,
  `usernamehash` varchar(92) DEFAULT NULL,
  `emailhash` varchar(92) DEFAULT NULL,
  KEY `userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `themes` (
  `id` int(14) NOT NULL AUTO_INCREMENT,
  `prefix` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(1) DEFAULT '0',
  `color` varchar(14) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `createdDate` datetime DEFAULT NULL,
  `modifiedDate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `countries` (
  `id` int(14) NOT NULL AUTO_INCREMENT,
  `label` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `label` (`label`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;