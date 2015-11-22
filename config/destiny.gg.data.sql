/* INSERT DEFAULT DATA */

INSERT INTO `dfl_users` (userId, username, email, country, createdDate, modifiedDate, userStatus, nameChangedCount, nameChangedDate) VALUES
  (NULL, 'Admin', 'admin@destiny.gg', '', NOW(), NOW(), 'Active', 0, NULL);

INSERT INTO `dfl_roles` (roleId, roleName) VALUES
  (1, 'ADMIN');

INSERT INTO `dfl_users_roles` (userId, roleId) VALUES
  (1, 1);

INSERT  INTO `dfl_features`(`featureId`,`featureName`,`featureLabel`) VALUES
  (1, 'protected', 'Protected'),
  (2, 'subscriber', 'Subscriber'),
  (3, 'vip', 'Vip'),
  (4, 'moderator', 'Moderator'),
  (5, 'admin', 'Admin'),
  (6, 'bot', 'Bot'),
  (7, 'flair1', 'Subscriber Tier 2'),
  (8, 'flair2', 'Notable'),
  (9, 'flair3', 'Subscriber Tier 3'),
  (10, 'flair4', 'Trusted'),
  (11, 'flair5', 'Contributor'),
  (12, 'flair6', 'Composition Challenge Winner'),
  (13, 'flair7', 'Eve Notable'),
  (14, 'flair8', 'Subscriber Tier 4');