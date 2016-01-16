DROP TABLE IF EXISTS `videos` /
CREATE TABLE `videos` (
  `videoId` INT(11)      NOT NULL AUTO_INCREMENT,
  `title`   VARCHAR(127) NOT NULL,
  `source`  VARCHAR(255) NOT NULL,
  `created` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`videoId`),
  UNIQUE KEY `TITLE_IDX` (`title`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8 /

DROP TABLE IF EXISTS `video_visits` /
CREATE TABLE `video_visits` (
  `visitId`   INT(11)     NOT NULL AUTO_INCREMENT,
  `userId`    VARCHAR(31)          DEFAULT NULL,
  `visitorId` VARCHAR(63) NOT NULL,
  `videoId`   INT(11)     NOT NULL,
  `referrer`  VARCHAR(255)         DEFAULT NULL,
  `created`   TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`visitId`),
  UNIQUE KEY `visitorId` (`visitorId`, `videoId`, `created`),
  FOREIGN KEY (`videoId`) REFERENCES `videos` (`videoId`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
