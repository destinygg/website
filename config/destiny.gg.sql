/*!40101 SET NAMES utf8 */;
/*!40101 SET SQL_MODE=''*/;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

/*Table structure for table `bans` */

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

/*Table structure for table `chatlog` */

CREATE TABLE `chatlog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL,
  `targetuserid` int(10) unsigned DEFAULT NULL,
  `event` varchar(15) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `data` text,
  `timestamp` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `dfl_features` */

CREATE TABLE `dfl_features` (
  `featureId` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `featureName` VARCHAR(100) NOT NULL,
  `featureLabel` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`featureId`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

/*Table structure for table `dfl_orders` */

CREATE TABLE `dfl_orders` (
  `orderId` int(14) NOT NULL AUTO_INCREMENT,
  `userId` int(14) DEFAULT NULL,
  `state` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `amount` float DEFAULT NULL,
  `currency` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `createdDate` datetime DEFAULT NULL,
  PRIMARY KEY (`orderId`),
  KEY `userId` (`userId`),
  KEY `userOrderState` (`userId`,`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `dfl_orders_ipn` */

CREATE TABLE `dfl_orders_ipn` (
  `id` int(14) NOT NULL AUTO_INCREMENT,
  `ipnTrackId` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ipnTransactionType` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ipnTransactionId` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ipnData` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `dfl_orders_payment_profiles` */

CREATE TABLE `dfl_orders_payment_profiles` (
  `profileId` int(14) NOT NULL AUTO_INCREMENT,
  `userId` int(14) DEFAULT NULL,
  `orderId` int(14) DEFAULT NULL,
  `paymentProfileId` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `state` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `amount` float DEFAULT NULL,
  `currency` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `billingFrequency` int(2) DEFAULT NULL,
  `billingPeriod` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `billingStartDate` datetime DEFAULT NULL,
  `billingNextDate` datetime DEFAULT NULL,
  PRIMARY KEY (`profileId`),
  KEY `userId` (`userId`),
  KEY `userOrderId` (`userId`,`orderId`),
  KEY `paymentProfileId` (`paymentProfileId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `dfl_orders_payments` */

CREATE TABLE `dfl_orders_payments` (
  `paymentId` int(14) NOT NULL AUTO_INCREMENT,
  `orderId` int(14) DEFAULT NULL,
  `amount` float DEFAULT NULL,
  `currency` varchar(4) COLLATE utf8_unicode_ci DEFAULT NULL,
  `transactionId` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `transactionType` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `paymentType` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `payerId` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `paymentStatus` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `paymentDate` datetime DEFAULT NULL,
  `createdDate` datetime DEFAULT NULL,
  PRIMARY KEY (`paymentId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `dfl_roles` */

CREATE TABLE `dfl_roles` (
  `roleId` int(14) NOT NULL,
  `roleName` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`roleId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `dfl_scheduled_tasks` */

CREATE TABLE `dfl_scheduled_tasks` (
  `action` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lastExecuted` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `frequency` int(14) DEFAULT NULL,
  `period` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `executeOnNextRun` tinyint(1) DEFAULT NULL,
  `executeCount` int(14) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `dfl_users` */

CREATE TABLE `dfl_users` (
  `userId` int(14) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `country` varchar(4) COLLATE utf8_unicode_ci DEFAULT '',
  `createdDate` datetime NOT NULL,
  `modifiedDate` datetime DEFAULT NULL,
  `userStatus` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `nameChangedCount` tinyint(4) DEFAULT '0',
  `nameChangedDate` datetime DEFAULT NULL,
  `allowGifting` tinyint(4) DEFAULT '1',
  PRIMARY KEY (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `dfl_users_auth` */

CREATE TABLE `dfl_users_auth` (
  `userId` int(14) DEFAULT NULL,
  `authProvider` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `authCode` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `authId` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `authDetail` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `createdDate` datetime DEFAULT NULL,
  `modifiedDate` datetime DEFAULT NULL,
  KEY `authProvider` (`authProvider`,`authId`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `dfl_users_auth_token` */

CREATE TABLE `dfl_users_auth_token` (
  `authTokenId` INT(14) NOT NULL AUTO_INCREMENT,
  `userId` INT(11) DEFAULT NULL,
  `authToken` VARCHAR(32) CHARACTER SET utf8 DEFAULT NULL,
  `createdDate` DATETIME DEFAULT NULL,
  PRIMARY KEY (`authTokenId`),
  KEY `userId` (`userId`),
  KEY `authToken` (`authToken`)
) ENGINE=INNODB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `dfl_users_features` */

CREATE TABLE `dfl_users_features` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL,
  `featureId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `dfl_users_rememberme` */

CREATE TABLE `dfl_users_rememberme` (
  `userId` int(14) NOT NULL,
  `token` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `tokenType` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `createdDate` datetime DEFAULT NULL,
  `expireDate` datetime DEFAULT NULL,
  KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `dfl_users_roles` */

CREATE TABLE `dfl_users_roles` (
  `userId` int(14) NOT NULL,
  `roleId` tinyint(1) NOT NULL,
  KEY `userId` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `dfl_users_subscriptions` */

CREATE TABLE `dfl_users_subscriptions` (
  `subscriptionId` INT(14) NOT NULL AUTO_INCREMENT,
  `subscriptionSource` VARCHAR(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subscriptionType` VARCHAR(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subscriptionTier` TINYINT(4) DEFAULT NULL,
  `userId` INT(14) DEFAULT NULL,
  `createdDate` DATETIME DEFAULT NULL,
  `endDate` DATETIME DEFAULT NULL,
  `status` VARCHAR(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `recurring` TINYINT(4) DEFAULT NULL,
  `orderId` INT(14) DEFAULT NULL,
  `paymentProfileId` INT(14) DEFAULT NULL,
  `gifter` INT(14) DEFAULT NULL,
  PRIMARY KEY (`subscriptionId`),
  KEY `userId` (`userId`),
  KEY `userStatus` (`userId`,`status`)
) ENGINE=INNODB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `users_address` */

CREATE TABLE `users_address` (
  `id` int(14) NOT NULL AUTO_INCREMENT,
  `userId` int(14) NOT NULL,
  `fullName` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `line1` text COLLATE utf8_unicode_ci,
  `line2` text COLLATE utf8_unicode_ci,
  `city` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `region` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `country` varchar(4) COLLATE utf8_unicode_ci DEFAULT NULL,
  `createdDate` datetime DEFAULT NULL,
  `modifiedDate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


/* INSERT DEFAULT DATA */

INSERT INTO `dfl_users` (userId, username, email, country, createdDate, modifiedDate, userStatus, nameChangedCount, nameChangedDate) VALUES 
(NULL, 'Admin', 'admin@destiny.gg', '', NOW(), NOW(), 'Active', 0, NULL);

INSERT INTO `dfl_roles` (roleId, roleName) VALUES 
(1, 'ADMIN');

INSERT INTO `dfl_users_roles` (userId, roleId) VALUES 
(1, 1);

INSERT  INTO `dfl_features`(`featureId`,`featureName`,`featureLabel`) VALUES 
(1, 'protected', 'Protected'),
(2, 'subscriber', 'Subscriber'),
(3, 'vip', 'Vip'),
(4, 'moderator', 'Moderator'),
(5, 'admin', 'Admin'),
(6, 'bot', 'Bot'),
(7, 'flair1', 'Subscriber Tier 2'),
(8, 'flair2', 'Notable'),
(9, 'flair3', 'Subscriber Tier 3'),
(10, 'flair4', 'Trusted'),
(11, 'flair5', 'Contributor'),
(12, 'flair6', 'Composition Challenge Winner'),
(13, 'flair7', 'Eve Notable');
(14, 'flair8', 'Subscriber Tier 4');