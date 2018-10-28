DROP TABLE `chatlog`;

ALTER TABLE `dfl_roles`
  ADD COLUMN `roleLabel` VARCHAR(255) CHARSET utf8mb4 COLLATE utf8mb4_general_ci NULL AFTER `roleName`;

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

ALTER TABLE `dfl_features`
  ADD COLUMN `imageId` INT(14) NULL AFTER `featureLabel`,
  ADD COLUMN `locked` TINYINT(1) NOT NULL AFTER `imageId`,
  ADD COLUMN `createdDate` DATETIME NOT NULL AFTER `locked`,
  ADD COLUMN `modifiedDate` DATETIME NOT NULL AFTER `createdDate`,
  ADD UNIQUE INDEX (`featureName`);

UPDATE `dfl_features` SET createdDate = NOW(), modifiedDate = NOW();

ALTER TABLE `emotes`
  ADD COLUMN `styles` BLOB NULL AFTER `draft`;

ALTER TABLE `dfl_features`
  ADD COLUMN `hidden` TINYINT(1) NOT NULL AFTER `locked`,
  ADD COLUMN `color` VARCHAR(16) NOT NULL AFTER `hidden`,
  ADD COLUMN `priority` TINYINT(2) NOT NULL AFTER `color`;