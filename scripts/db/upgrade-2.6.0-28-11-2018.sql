INSERT INTO `oauth_access_tokens`
SELECT NULL `tokenId`, 0 `clientId`, a.userId `userId`, 'identify' `scope`, a.authToken `token`, NULL `refresh`, NULL `expireIn`, a.createdDate
FROM `dfl_users_auth_token` a;

DROP TABLE `dfl_users_auth_token`;
