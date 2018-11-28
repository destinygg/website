/*!40101 SET NAMES utf8 */;
/*!40101 SET SQL_MODE=''*/;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

CREATE TABLE `bans` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL,
  `targetuserid` int(10) unsigned NOT NULL,
  `ipaddress` text,
  `reason` text NOT NULL,
  `starttimestamp` datetime NOT NULL,
  `endtimestamp` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `dfl_features` (
  `featureId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `featureName` varchar(100) NOT NULL,
  `featureLabel` varchar(100) NOT NULL,
  `imageId` int(14) DEFAULT NULL,
  `locked` tinyint(1) NOT NULL,
  `createdDate` datetime NOT NULL,
  `modifiedDate` datetime NOT NULL,
  PRIMARY KEY (`featureId`),
  UNIQUE KEY `featureName` (`featureName`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `dfl_orders_ipn` (
  `id` int(14) NOT NULL AUTO_INCREMENT,
  `ipnTrackId` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ipnTransactionType` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ipnTransactionId` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ipnData` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `dfl_orders_payments` (
  `paymentId` int(14) NOT NULL AUTO_INCREMENT,
  `donationId` int(14) DEFAULT NULL,
  `subscriptionId` int(14) DEFAULT NULL,
  `amount` float DEFAULT NULL,
  `currency` varchar(4) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `transactionId` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `transactionType` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `paymentType` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payerId` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `paymentStatus` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
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
  `action` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `lastExecuted` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `frequency` int(14) DEFAULT NULL,
  `period` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `executeCount` int(14) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `dfl_users` (
  `userId` int(14) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `country` varchar(4) COLLATE utf8mb4_general_ci DEFAULT '',
  `createdDate` datetime NOT NULL,
  `modifiedDate` datetime DEFAULT NULL,
  `userStatus` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nameChangedCount` tinyint(4) DEFAULT '0',
  `nameChangedDate` datetime DEFAULT NULL,
  `allowGifting` tinyint(1) DEFAULT '1',
  `istwitchsubscriber` int(11) NOT NULL DEFAULT '0',
  `discorduuid` varchar(36) CHARACTER SET ascii DEFAULT NULL,
  `discordname` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `chatsettings` blob,
  PRIMARY KEY (`userId`),
  UNIQUE KEY `discorduuid` (`discorduuid`),
  UNIQUE KEY `discordname` (`discordname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `dfl_users_auth` (
  `userId` int(14) DEFAULT NULL,
  `authProvider` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `authCode` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `authId` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `authDetail` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `refreshToken` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `createdDate` datetime DEFAULT NULL,
  `modifiedDate` datetime DEFAULT NULL,
  KEY `authProvider` (`authProvider`,`authId`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `dfl_users_features` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL,
  `featureId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `dfl_users_roles` (
  `userId` int(14) NOT NULL,
  `roleId` tinyint(1) NOT NULL,
  KEY `userId` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `dfl_users_subscriptions` (
  `subscriptionId` INT(14) NOT NULL AUTO_INCREMENT,
  `subscriptionSource` VARCHAR(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `subscriptionType` VARCHAR(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `subscriptionTier` TINYINT(4) DEFAULT NULL,
  `userId` INT(14) DEFAULT NULL,
  `createdDate` DATETIME DEFAULT NULL,
  `endDate` DATETIME DEFAULT NULL,
  `status` VARCHAR(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `recurring` TINYINT(4) DEFAULT NULL,
  `paymentProfileId` VARCHAR(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `billingStartDate` DATETIME DEFAULT NULL,
  `billingNextDate` DATETIME DEFAULT NULL,
  `paymentStatus` VARCHAR(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gifter` int(14) DEFAULT NULL,
  PRIMARY KEY (`subscriptionId`),
  KEY `userId` (`userId`),
  KEY `userStatus` (`userId`,`status`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

CREATE TABLE `donations` (
  `id` int(14) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `username` varchar(255) NULL,
  `currency` varchar(4) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `message` blob NULL,
  `status` varchar(100) NULL,
  `timestamp` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `privatemessages` (
  `id` int(14) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `targetuserid` int(11) NOT NULL,
  `message` blob NOT NULL,
  `timestamp` datetime NOT NULL,
  `isread` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `targetuserid` (`targetuserid`),
  KEY `userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `emotes` (
  `id` int(14) NOT NULL AUTO_INCREMENT,
  `prefix` varchar(180) DEFAULT NULL,
  `imageId` int(14) NOT NULL,
  `twitch` tinyint(1) NOT NULL,
  `draft` tinyint(1) NOT NULL,
  `createdDate` datetime NOT NULL,
  `modifiedDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `prefix` (`prefix`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4;

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
) ENGINE=InnoDB AUTO_INCREMENT=195 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `oauth_access_tokens` (
   `tokenId` int(14) NOT NULL AUTO_INCREMENT,
   `clientId` int(14) NOT NULL,
   `userId` int(14) NOT NULL,
   `scope` varchar(100) NOT NULL,
   `token` varchar(64) NOT NULL,
   `refresh` varchar(64) DEFAULT NULL,
   `expireIn` int(11) DEFAULT NULL,
   `createdDate` datetime DEFAULT NULL,
   PRIMARY KEY (`tokenId`),
   KEY `IDX_TOKEN` (`token`),
   KEY `IDX_REFRESH` (`clientId`,`refresh`),
   KEY `IDX_USER` (`userId`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4;

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;
