DROP TABLE IF EXISTS `workshops` /
CREATE TABLE `workshops` (
  `workshopId` INT(11)          NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(63)      NOT NULL,
  `amount`     INT(11) UNSIGNED NOT NULL,
  `maxPeople`  INT(11) UNSIGNED NOT NULL,
  `created`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`workshopId`),
  UNIQUE KEY `NAME_IDX`(`name`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4 /

DROP TABLE IF EXISTS `enrollments` /
CREATE TABLE `enrollments` (
  `enrollmentId` INT(11)     NOT NULL AUTO_INCREMENT,
  `userId`       VARCHAR(31) NOT NULL,
  `workshopId`   INT(11)     NOT NULL,
  `chargeId`     INT(11)     NOT NULL,
  `created`      TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`enrollmentId`),
  UNIQUE KEY `userId` (`userId`, `workshopId`),
  FOREIGN KEY (`workshopId`) REFERENCES `workshops` (`workshopId`),
  FOREIGN KEY (`chargeId`) REFERENCES `charges` (`chargeId`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4