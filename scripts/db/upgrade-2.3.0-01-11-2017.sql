DROP INDEX minecraftuuid ON dfl_users;
DROP INDEX minecraftname ON dfl_users;
ALTER TABLE dfl_users DROP minecraftuuid;
ALTER TABLE dfl_users DROP minecraftname;