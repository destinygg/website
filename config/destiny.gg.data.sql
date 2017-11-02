/* INSERT DEFAULT DATA */

INSERT INTO `dfl_users` (userId, username, email, country, createdDate, modifiedDate, userStatus, nameChangedCount, nameChangedDate) VALUES
  (NULL, 'Admin', 'admin@destiny.gg', '', NOW(), NOW(), 'Active', 0, NULL);

INSERT INTO `dfl_roles` (roleId, roleName) VALUES
  (1, 'ADMIN'),
  (2, 'FINANCE');

INSERT INTO `dfl_users_roles` (userId, roleId) VALUES
  (1, 1);

INSERT  INTO `dfl_features`(`featureId`,`featureName`,`featureLabel`) VALUES
  (1, 'protected', 'Protected'),
  (2, 'subscriber', 'Subscriber'),
  (3, 'vip', 'Vip'),
  (4, 'moderator', 'Moderator'),
  (5, 'admin', 'Admin'),
  (6, 'bot', 'Bot'),
  (7, 'flair13', 'Subscriber Tier 1'),
  (8, 'flair1', 'Subscriber Tier 2'),
  (9, 'flair2', 'Notable'),
  (10, 'flair3', 'Subscriber Tier 3'),
  (11, 'flair4', 'Trusted'),
  (12, 'flair5', 'Contributor'),
  (13, 'flair6', 'Composition Challenge Winner'),
  (14, 'flair7', 'Eve'),
  (15, 'flair8', 'Subscriber Tier 4'),
  (16, 'flair10', 'StarCraft 2'),
  (17, 'flair11', 'Bot 2'),
  (18, 'flair12', 'Broadcaster');
