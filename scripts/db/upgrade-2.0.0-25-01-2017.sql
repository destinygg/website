ALTER TABLE  `dfl_users` ADD  `discordname` VARCHAR( 255 ) CHARACTER SET ASCII COLLATE ascii_general_ci NULL DEFAULT NULL ,
  ADD UNIQUE (
  discordname
);
ALTER TABLE  `dfl_users` ADD  `discordname` VARCHAR( 16 ) DEFAULT NULL ,
  ADD UNIQUE (
  discordname
);