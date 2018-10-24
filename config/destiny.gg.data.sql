/* INSERT DEFAULT DATA */

INSERT INTO `dfl_users` (userId, username, email, country, createdDate, modifiedDate, userStatus, nameChangedCount, nameChangedDate) VALUES
  (NULL, 'Admin', 'admin@destiny.gg', '', NOW(), NOW(), 'Active', 0, NULL);

INSERT INTO `dfl_roles` (roleId, roleName, roleLabel) VALUES
  (1, 'ADMIN', 'Admin'),
  (2, 'USER', 'USER'),
  (3, 'SUBSCRIBER', 'SUBSCRIBER'),
  (4, 'MODERATOR', 'MODERATOR'),
  (5, 'FINANCE', 'FINANCE'),
  (6, 'STREAMLABS', 'STREAMLABS'),
  (7, 'EMOTES', 'EMOTES'),
  (8, 'FLAIRS', 'FLAIRS');

INSERT INTO `dfl_users_roles` (userId, roleId) VALUES (1, 1),(1, 2),(1, 3),(1, 4),(1, 5),(1, 6),(1, 7),(1, 8);

INSERT  INTO `dfl_features`(`featureId`,`featureName`,`featureLabel`,`imageId`,`locked`,`createdDate`,`modifiedDate`) VALUES
  (1, 'protected', 'Protected', NULL, 1, NOW(), NOW()),
  (2, 'subscriber', 'Subscriber', NULL, 1, NOW(), NOW()),
  (3, 'vip', 'Vip', NULL, 1, NOW(), NOW()),
  (4, 'moderator', 'Moderator', NULL, 1, NOW(), NOW()),
  (5, 'admin', 'Admin', NULL, 1, NOW(), NOW()),
  (6, 'bot', 'Bot', NULL, 1, NOW(), NOW()),
  (7, 'flair13', 'Subscriber Tier 1', NULL, 1, NOW(), NOW()),
  (8, 'flair1', 'Subscriber Tier 2', NULL, 1, NOW(), NOW()),
  (9, 'flair3', 'Subscriber Tier 3', NULL, 1, NOW(), NOW()),
  (15, 'flair8', 'Subscriber Tier 4', NULL, 1, NOW(), NOW()),
  (17, 'flair11', 'Bot 2', NULL, 1, NOW(), NOW()),
  (18, 'flair12', 'Broadcaster', NULL, 1, NOW(), NOW()),
  (19, 'flair15', 'DGG Bday', NULL, 1, NOW(), NOW()),
