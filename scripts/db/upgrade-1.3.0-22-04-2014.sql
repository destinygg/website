# Upgrade 1.3.0 (22-04-2014)

ALTER TABLE `dfl_users_subscriptions` ADD COLUMN `gifter` INT(14) NULL; 
ALTER TABLE `dfl_users` ADD COLUMN `allowGifting` TINYINT(1) DEFAULT 1 NULL;
INSERT INTO `dfl_features` VALUES (NULL,'flair8','Subscriber Tier 4');