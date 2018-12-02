INSERT INTO `oauth_access_tokens`
SELECT NULL `tokenId`, NULL `clientId`, a.userId `userId`, 'identify' `scope`, a.authToken `token`, NULL `refresh`, NULL `expireIn`, a.createdDate
FROM `dfl_users_auth_token` a;

DROP TABLE `dfl_users_auth_token`;

ALTER TABLE `dfl_users`
  ADD INDEX `minecraftuuid` (`minecraftuuid`),
  ADD INDEX `minecraftname` (`minecraftname`);
