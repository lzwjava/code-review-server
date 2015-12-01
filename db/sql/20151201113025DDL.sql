SET NAMES utf8/

DROP TABLE IF EXISTS `reviewers`/
CREATE TABLE `reviewers` (
  `id`                  VARCHAR(31)  NOT NULL,
  `username`            VARCHAR(255) NOT NULL,
  `mobilePhoneNumber`   VARCHAR(255) NOT NULL,
  `avatarUrl`           VARCHAR(255) NOT NULL,
  `sessionToken`        VARCHAR(255) NOT NULL,
  `sessionTokenCreated` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `password`            VARCHAR(255) NOT NULL,
  `created`             TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `type`                TINYINT(2)   NOT NULL DEFAULT 1,

  `introduction`        VARCHAR(511),

  PRIMARY KEY (`id`),
  UNIQUE KEY `NAME_IDX` (`username`),
  UNIQUE KEY `PHONE_IDX` (`mobilePhoneNumber`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8/

DROP TABLE IF EXISTS `learners`/
CREATE TABLE `learners` (
  `id`                  VARCHAR(31)  NOT NULL,
  `username`            VARCHAR(255) NOT NULL,
  `mobilePhoneNumber`   VARCHAR(255) NOT NULL,
  `avatarUrl`           VARCHAR(255) NOT NULL,
  `sessionToken`        VARCHAR(255) NOT NULL,
  `sessionTokenCreated` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `password`            VARCHAR(255) NOT NULL,
  `created`             TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `type`                TINYINT(2)   NOT NULL DEFAULT 0,

  PRIMARY KEY (`id`),
  UNIQUE KEY `NAME_IDX` (`username`),
  UNIQUE KEY `PHONE_IDX` (`mobilePhoneNumber`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8/

DROP VIEW IF EXISTS `users`/
CREATE VIEW `users` AS SELECT
                         `id`,
                         `username`,
                         `mobilePhoneNumber`,
                         `avatarUrl`,
                         `sessionToken`,
                         `sessionTokenCreated`,
                         `password`,
                         `created`,
                         `type`
                       FROM `learners`
                       UNION SELECT
                               `id`,
                               `username`,
                               `mobilePhoneNumber`,
                               `avatarUrl`,
                               `sessionToken`,
                               `sessionTokenCreated`,
                               `password`,
                               `created`,
                               `type`
                             FROM `reviewers`/
