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

INSERT  INTO `themes`(`id`,`prefix`,`label`,`active`,`color`,`createdDate`,`modifiedDate`) VALUES
(1,'destiny','Dgg',1,'#1e1e1e','2019-09-07 09:54:41','2019-09-08 18:06:24'),
(2,'xmas','Xmas',0,'#EE1F1F','2019-09-07 22:19:50','2019-09-08 18:06:15'),
(3,'halloween','Halloween',0,'#E79015','2019-09-07 12:38:02','2019-09-08 18:06:24');

ALTER TABLE `emotes` ADD COLUMN `theme` INT(14) DEFAULT 1 NULL AFTER `draft`;
ALTER TABLE `emotes` DROP INDEX `prefix`, ADD UNIQUE INDEX `prefix` (`prefix`, `theme`);