ALTER TABLE `dfl_users_auth` ADD COLUMN `id` INT(14) NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`);
ALTER TABLE `dfl_users_auth` ADD COLUMN `expiresIn` INT(11) NULL AFTER `authEmail`;
ALTER TABLE `dfl_users_auth` ADD COLUMN `accessToken` VARCHAR(255) NULL AFTER `expiresIn`;
ALTER TABLE `dfl_users` ADD COLUMN `allowChatting` TINYINT(1) DEFAULT 1 NULL AFTER `allowGifting`, ADD COLUMN `allowNameChange` TINYINT(1) DEFAULT 0 NULL AFTER `allowChatting`;
ALTER TABLE `dfl_users` DROP COLUMN `nameChangedCount`, DROP COLUMN `nameChangedDate`;
ALTER TABLE `dfl_users_auth` DROP COLUMN `authCode`;