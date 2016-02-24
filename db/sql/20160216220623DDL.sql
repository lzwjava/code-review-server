DROP TABLE IF EXISTS `alipay_callbacks` /
CREATE TABLE `alipay_callbacks` (
  `callbackId` INT(11)      NOT NULL AUTO_INCREMENT,
  `params`     VARCHAR(512) NOT NULL,
  PRIMARY KEY (`callbackId`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4 /

DROP TABLE IF EXISTS `comments` /
CREATE TABLE `comments` (
  `commentId` INT(11)      NOT NULL AUTO_INCREMENT,
  `reviewId`  INT(11)      NOT NULL,
  `parentId`  INT(11)               DEFAULT NULL,
  `content`   VARCHAR(511) NOT NULL,
  `authorId`  VARCHAR(31)  NOT NULL,
  `created`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`commentId`),
  FOREIGN KEY (`reviewId`) REFERENCES `reviews` (`reviewId`),
  FOREIGN KEY (`parentId`) REFERENCES `comments` (`commentId`)
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 70
  DEFAULT CHARSET = utf8mb4

DROP TABLE IF EXISTS `notifications` /
CREATE TABLE `notifications` (
  `notificationId` INT(11)     NOT NULL AUTO_INCREMENT,
  `userId`         VARCHAR(31) NOT NULL,
  `unread`         TINYINT     NOT NULL DEFAULT 1,
  `type`           VARCHAR(31) NOT NULL,
  `orderId`        INT(11),
  `commentId`      INT(11),
  `created`        TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notificationId`),
  FOREIGN KEY (`orderId`) REFERENCES `orders` (`orderId`),
  FOREIGN KEY (`commentId`) REFERENCES `comments` (`commentId`)
)
