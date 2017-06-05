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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `chatlog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL,
  `targetuserid` int(10) unsigned DEFAULT NULL,
  `event` varchar(15) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `data` text,
  `timestamp` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `dfl_features` (
  `featureId` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `featureName` VARCHAR(100) NOT NULL,
  `featureLabel` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`featureId`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `dfl_roles` (
  `roleId` int(14) NOT NULL,
  `roleName` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
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
  `minecraftuuid` varchar(36) CHARACTER SET ascii DEFAULT NULL,
  `minecraftname` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discorduuid` varchar(36) CHARACTER SET ascii DEFAULT NULL,
  `discordname` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `chatsettings` blob,
  PRIMARY KEY (`userId`),
  UNIQUE KEY `minecraftuuid` (`minecraftuuid`),
  UNIQUE KEY `minecraftname` (`minecraftname`),
  UNIQUE KEY `discorduuid` (`discorduuid`),
  UNIQUE KEY `discordname` (`discordname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `dfl_users_auth` (
  `userId` int(14) DEFAULT NULL,
  `authProvider` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `authCode` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `authId` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `authDetail` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `createdDate` datetime DEFAULT NULL,
  `modifiedDate` datetime DEFAULT NULL,
  KEY `authProvider` (`authProvider`,`authId`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `dfl_users_auth_token` (
  `authTokenId` INT(14) NOT NULL AUTO_INCREMENT,
  `userId` INT(11) DEFAULT NULL,
  `authToken` VARCHAR(32) CHARACTER SET utf8 DEFAULT NULL,
  `createdDate` DATETIME DEFAULT NULL,
  PRIMARY KEY (`authTokenId`),
  KEY `userId` (`userId`),
  KEY `authToken` (`authToken`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

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

CREATE TABLE `users_address` (
  `id` int(14) NOT NULL AUTO_INCREMENT,
  `userId` int(14) NOT NULL,
  `fullName` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `line1` text COLLATE utf8mb4_general_ci,
  `line2` text COLLATE utf8mb4_general_ci,
  `city` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `region` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `zip` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `country` varchar(4) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `createdDate` datetime DEFAULT NULL,
  `modifiedDate` datetime DEFAULT NULL,
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
