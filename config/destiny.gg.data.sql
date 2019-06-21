/* INSERT DEFAULT DATA */

INSERT INTO `dfl_users` (userId, username, email, country, createdDate, modifiedDate, userStatus) VALUES
  (NULL, 'Admin', 'admin@destiny.gg', '', NOW(), NOW(), 'Active');

INSERT INTO `dfl_roles` (roleId, roleName, roleLabel) VALUES
  (1, 'ADMIN', 'Admin'),
  (2, 'FINANCE', 'Finance'),
  (3, 'STREAMLABS', 'StreamLabs'),
  (4, 'USER', 'User'),
  (5, 'SUBSCRIBER', 'Subscriber'),
  (6, 'MODERATOR', 'Moderator'),
  (7, 'EMOTES', 'Emotes'),
  (8, 'FLAIRS', 'Flairs');

INSERT INTO `dfl_users_roles` (userId, roleId) VALUES (1, 1),(1, 2),(1, 3),(1, 6),(1, 7),(1, 8);

INSERT INTO `dfl_features` (`featureId`, `featureName`, `featureLabel`) VALUES
   (1,	'protected',	'Protected'),
   (2,	'subscriber',	'Subscriber'),
   (3,	'vip',	'VIP'),
   (4,	'moderator',	'Moderator'),
   (5,	'admin',	'Admin'),
   (6,	'bot',	'Bot'),
   (7,	'flair1',	'Subscriber Tier 2'),
   (8,	'flair2',	'Notable'),
   (9,	'flair3',	'Subscriber Tier 3'),
   (10,	'flair4',	'Trusted'),
   (11,	'flair5',	'Contributor'),
   (12,	'flair6',	'Composition Winner'),
   (13,	'flair7',	'Eve'),
   (14,	'flair8',	'Subscriber Tier 4'),
   (15,	'flair10',	'StarCraft 2'),
   (16,	'flair11',	'Bot 2'),
   (17,	'flair12',	'Broadcaster'),
   (18,	'flair14',	'Minecraft VIP'),
   (19,	'flair15',	'DGG Bday'),
   (20,	'flair19',	'DGG Shirt Designer'),
   (21,	'flair13',	'Subscriber Tier 1'),
   (22,	'flair9',	'Twitch Subscriber');