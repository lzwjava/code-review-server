DROP TABLE IF EXISTS `events` /
CREATE TABLE `events` (
  `eventId` INT(11)          NOT NULL AUTO_INCREMENT,
  `name`    VARCHAR(31)      NOT NULL,
  `amount`  INT(11) UNSIGNED NOT NULL,
  `created` TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`eventId`),
  UNIQUE KEY (`name`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4 /

DROP TABLE IF EXISTS `user_events` /
CREATE TABLE `user_events` (
  `userEventId` INT(11)              AUTO_INCREMENT,
  `userId`      VARCHAR(31) NOT NULL,
  `eventId`     INT(11)     NOT NULL,
  `chargeId`    INT(11),
  `created`     TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`userEventId`),
  UNIQUE KEY (`userId`, `eventId`),
  FOREIGN KEY (`eventId`) REFERENCES `events` (`eventId`),
  FOREIGN KEY (`chargeId`) REFERENCES `charges` (`chargeId`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4