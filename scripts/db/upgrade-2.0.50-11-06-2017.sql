ALTER TABLE `dfl_users_auth` ADD COLUMN `refreshToken` VARCHAR(255) NULL AFTER `authDetail`;

CREATE TABLE `donations` (
  `id` int(14) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `currency` varchar(4) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `message` blob NULL,
  `status` varchar(100) NULL,
  `timestamp` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;