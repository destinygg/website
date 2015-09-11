ALTER TABLE  `dfl_users` ADD  `minecraftuuid` VARCHAR( 36 ) CHARACTER SET ASCII COLLATE ascii_general_ci NULL DEFAULT NULL ,
ADD UNIQUE (
minecraftuuid
);
