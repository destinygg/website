/*!40101 SET NAMES utf8 */;
/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*Table structure for table `dfl_challengers` */

CREATE TABLE `dfl_challengers` (
  `ownerTeamId` int(14) DEFAULT NULL,
  `challengeTeamId` int(14) DEFAULT NULL,
  `status` enum('SENT','ACCEPTED','DECLINED') CHARACTER SET utf8 DEFAULT NULL,
  `createdDate` datetime DEFAULT NULL,
  KEY `ownerTeamId` (`ownerTeamId`),
  KEY `challengeTeamId` (`challengeTeamId`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `dfl_champs` */

CREATE TABLE `dfl_champs` (
  `championId` int(14) NOT NULL,
  `championName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `championValue` int(14) NOT NULL,
  `championMultiplier` decimal(4,3) DEFAULT '1.000',
  `championTypes` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `championFree` tinyint(1) DEFAULT '0',
  `gamesPlayed` int(14) DEFAULT '0',
  `gamesWin` int(14) DEFAULT '0',
  `gamesLost` int(14) DEFAULT '0',
  PRIMARY KEY (`championId`),
  KEY `championName` (`championName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `dfl_games` */

CREATE TABLE `dfl_games` (
  `gameId` int(14) NOT NULL,
  `gameCreatedDate` datetime NOT NULL,
  `gameEndDate` datetime NOT NULL,
  `gameType` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `gameRanked` tinyint(1) NOT NULL,
  `gameSeason` int(14) NOT NULL,
  `gameWinSideId` enum('100','200') COLLATE utf8_unicode_ci NOT NULL,
  `gameLoseSideId` enum('100','200') COLLATE utf8_unicode_ci NOT NULL,
  `gameRegion` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `aggregated` tinyint(1) NOT NULL,
  `aggregatedDate` datetime NOT NULL,
  `createdDate` datetime NOT NULL,
  PRIMARY KEY (`gameId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `dfl_games_champs` */

CREATE TABLE `dfl_games_champs` (
  `gameId` int(14) NOT NULL,
  `championId` int(14) NOT NULL,
  `teamSideId` enum('100','200') COLLATE utf8_unicode_ci NOT NULL,
  `summonerId` int(14) NOT NULL,
  `summonerName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`gameId`,`championId`,`summonerId`),
  KEY `gameId` (`gameId`),
  KEY `championId` (`championId`),
  KEY `summonerId` (`summonerId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `dfl_games_summoner_data` */

CREATE TABLE `dfl_games_summoner_data` (
  `gameId` int(14) NOT NULL,
  `gameData` longtext COLLATE utf8_unicode_ci NOT NULL,
  `gameWin` tinyint(1) NOT NULL,
  `acctId` int(14) NOT NULL,
  `summonerId` int(14) NOT NULL,
  PRIMARY KEY (`gameId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `dfl_ingame_progress` */

CREATE TABLE `dfl_ingame_progress` (
  `gameId` int(14) NOT NULL,
  `gameStartTime` datetime NOT NULL,
  `gameData` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`gameId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `dfl_orders_ipn` */

CREATE TABLE `dfl_orders_ipn` (
  `ipnTrackId` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ipnTransactionType` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ipnTransactionId` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ipnData` text COLLATE utf8_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `dfl_orders_items` */

CREATE TABLE `dfl_orders_items` (
  `orderId` int(11) NOT NULL,
  `itemSku` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `itemPrice` float NOT NULL,
  KEY `orderId` (`orderId`)
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
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `dfl_scheduled_tasks` */

CREATE TABLE `dfl_scheduled_tasks` (
  `action` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lastExecuted` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `frequency` int(14) DEFAULT NULL,
  `period` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `executeOnStart` tinyint(1) DEFAULT NULL,
  `executeCount` int(14) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `dfl_scores_champs` */

CREATE TABLE `dfl_scores_champs` (
  `gameId` int(14) NOT NULL,
  `championId` int(14) NOT NULL,
  `championMultiplier` decimal(4,3) NOT NULL,
  `scoreType` enum('WIN','LOSE') COLLATE utf8_unicode_ci NOT NULL,
  `scoreValue` int(14) NOT NULL,
  `createdDate` datetime NOT NULL,
  KEY `gameId` (`gameId`),
  KEY `championId` (`championId`),
  KEY `gameChamp` (`gameId`,`championId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `dfl_scores_teams` */

CREATE TABLE `dfl_scores_teams` (
  `gameId` int(14) DEFAULT NULL,
  `teamId` int(14) NOT NULL,
  `scoreValue` int(14) NOT NULL,
  `scoreType` enum('GAME','BONUS','PARTICIPATE','POLL') COLLATE utf8_unicode_ci NOT NULL,
  `createdDate` datetime NOT NULL,
  KEY `gameId` (`gameId`),
  KEY `teamId` (`teamId`),
  KEY `createdDate` (`createdDate`),
  KEY `teamScoreType` (`teamId`,`scoreType`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `dfl_scores_teams_champs` */

CREATE TABLE `dfl_scores_teams_champs` (
  `gameId` int(14) NOT NULL,
  `teamId` int(14) NOT NULL,
  `championId` int(14) NOT NULL,
  `championMultiplier` decimal(4,3) NOT NULL,
  `penalty` decimal(4,2) NOT NULL,
  `scoreValue` int(14) NOT NULL,
  `createdDate` datetime NOT NULL,
  KEY `gameId` (`gameId`),
  KEY `teamId` (`teamId`),
  KEY `championId` (`championId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `dfl_team_champs` */

CREATE TABLE `dfl_team_champs` (
  `teamId` int(14) NOT NULL,
  `championId` int(14) NOT NULL,
  `displayOrder` int(14) NOT NULL,
  `createdDate` datetime NOT NULL,
  PRIMARY KEY (`teamId`,`championId`),
  KEY `teamId` (`teamId`),
  KEY `championId` (`championId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `dfl_team_milestones` */

CREATE TABLE `dfl_team_milestones` (
  `teamId` int(14) NOT NULL,
  `milestoneType` enum('GAMEPOINTS','TIMESPAN','GAMES') COLLATE utf8_unicode_ci NOT NULL,
  `milestoneValue` int(14) NOT NULL,
  `milestoneGoal` int(14) NOT NULL,
  `createdDate` datetime NOT NULL,
  `modifiedDate` datetime NOT NULL,
  PRIMARY KEY (`teamId`,`milestoneType`),
  KEY `teamId` (`teamId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `dfl_team_ranks` */

CREATE TABLE `dfl_team_ranks` (
  `teamId` int(11) NOT NULL,
  `teamRank` int(11) NOT NULL,
  PRIMARY KEY (`teamId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `dfl_team_transfers` */

CREATE TABLE `dfl_team_transfers` (
  `teamId` int(14) NOT NULL,
  `championId` int(14) NOT NULL,
  `championValue` int(14) NOT NULL,
  `transferType` enum('IN','OUT') COLLATE utf8_unicode_ci NOT NULL,
  `createdDate` datetime NOT NULL,
  KEY `teamId` (`teamId`),
  KEY `championId` (`championId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `dfl_teams` */

CREATE TABLE `dfl_teams` (
  `teamId` int(14) NOT NULL AUTO_INCREMENT,
  `userId` int(14) NOT NULL,
  `credits` decimal(14,2) DEFAULT '0.00',
  `scoreValue` int(14) DEFAULT '0',
  `transfersRemaining` int(14) DEFAULT '0',
  `teamActive` tinyint(1) NOT NULL DEFAULT '1',
  `createdDate` datetime DEFAULT NULL,
  `modifiedDate` datetime DEFAULT NULL,
  PRIMARY KEY (`teamId`),
  KEY `userId` (`userId`),
  KEY `userTeamId` (`teamId`,`userId`)
) ENGINE=InnoDB AUTO_INCREMENT=4219 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `dfl_users` */

CREATE TABLE `dfl_users` (
  `userId` int(14) NOT NULL AUTO_INCREMENT,
  `externalId` int(14) NOT NULL,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `displayName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `country` varchar(4) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `admin` tinyint(1) DEFAULT '0',
  `createdDate` datetime NOT NULL,
  PRIMARY KEY (`userId`),
  KEY `externalId` (`externalId`)
) ENGINE=InnoDB AUTO_INCREMENT=4219 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `dfl_users_champs` */

CREATE TABLE `dfl_users_champs` (
  `userId` int(14) NOT NULL,
  `championId` int(14) NOT NULL,
  `createdDate` datetime NOT NULL,
  PRIMARY KEY (`userId`,`championId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `dfl_users_sessions` */

CREATE TABLE `dfl_users_sessions` (
  `userId` int(14) DEFAULT NULL,
  `sessionId` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `authorized` tinyint(1) DEFAULT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `createdDate` datetime DEFAULT NULL,
  `modifiedDate` datetime DEFAULT NULL,
  `expireDate` datetime DEFAULT NULL,
  PRIMARY KEY (`sessionId`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `dfl_users_settings` */

CREATE TABLE `dfl_users_settings` (
  `userId` int(14) NOT NULL,
  `settingName` enum('teambar_homepage') COLLATE utf8_unicode_ci NOT NULL,
  `settingValue` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `userSetting` (`userId`,`settingName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `dfl_users_subscriptions` */

CREATE TABLE `dfl_users_subscriptions` (
  `subscriptionId` int(14) NOT NULL AUTO_INCREMENT,
  `userId` int(14) DEFAULT NULL,
  `createdDate` datetime DEFAULT NULL,
  `endDate` datetime DEFAULT NULL,
  `status` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `recurring` tinyint(4) DEFAULT NULL,
  `paymentProfileId` int(14) DEFAULT NULL,
  PRIMARY KEY (`subscriptionId`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `dfl_users_twitch_subscribers` */

CREATE TABLE `dfl_users_twitch_subscribers` (
  `externalId` int(14) DEFAULT NULL,
  `username` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `displayName` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `staff` tinyint(1) DEFAULT NULL,
  `subscribeDate` datetime DEFAULT NULL,
  `createdDate` datetime DEFAULT NULL,
  `validated` tinyint(1) DEFAULT '0',
  UNIQUE KEY `externalId` (`externalId`),
  KEY `validated` (`validated`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;