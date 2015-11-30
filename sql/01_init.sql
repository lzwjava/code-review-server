SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `mobilePhoneNumber` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `avatarUrl` varchar(255) NOT NULL,
  `type` tinyint(2) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY 'NAME_IDX' (`username`)
  UNIQUE KEY `PHONE_IDX` (`mobilePhoneNumber`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SET FOREIGN_KEY_CHECKS = 1;

DROP TABLE IF EXISTS `reviewers`;
CREATE TABLE `reviewers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `introduction` varchar(511) CHARACTER SET utf8 COLLATE utf8_bin,
  PRIMARY KEY(`id`),
  KEY `USER_ID_IDX` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `learners`;
CREATE TABLE `learners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY(`id`),
  KEY `USER_ID_IDX` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
