ALTER TABLE  `dfl_users` ADD  `minecraftuuid` VARCHAR( 36 ) CHARACTER SET ASCII COLLATE ascii_general_ci NULL DEFAULT NULL ,
ADD UNIQUE (
minecraftuuid
);
ALTER TABLE  `dfl_users` ADD  `minecraftname` VARCHAR( 16 ) DEFAULT NULL ,
ADD UNIQUE (
minecraftname
);

UPDATE dfl_users SET minecraftname = 'destiny' WHERE userName = 'Destiny' LIMIT 1;
