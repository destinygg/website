ALTER TABLE `bans` ADD INDEX IF NOT EXISTS `targetuserid` (`targetuserid`);
ALTER TABLE `bans` ADD INDEX IF NOT EXISTS `endtimestamp` (`endtimestamp`);