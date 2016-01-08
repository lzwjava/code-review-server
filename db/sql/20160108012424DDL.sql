DROP TABLE IF EXISTS `review_visits` /

CREATE TABLE `review_visits` (
  `visitId`   INT(11)     NOT NULL            AUTO_INCREMENT,
  `userId`    VARCHAR(31),
  `visitorId` VARCHAR(63) NOT NULL,
  `reviewId`  INT(11)     NOT NULL,
  `referrer`  VARCHAR(255),
  `created`   TIMESTAMP   NOT NULL            DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`visitId`),
  UNIQUE KEY (`visitorId`, `reviewId`, `created`),
  FOREIGN KEY (`reviewId`) REFERENCES `reviews` (`reviewId`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8 /
