DROP TABLE IF EXISTS `rewards` /
CREATE TABLE `rewards` (
  `rewardId`  INT(11)     NOT NULL          AUTO_INCREMENT,

  `reviewId`  INT(11)     NOT NULL,

  `orderNo`   VARCHAR(31) NOT NULL,
  `amount`    INT(11)     NOT NULL,
  `paid`      TINYINT(2)  NOT NULL          DEFAULT 0,

  `creator`   VARCHAR(31) NOT NULL,
  `creatorIP` VARCHAR(63) NOT NULL,

  `created`   TIMESTAMP   NOT NULL          DEFAULT CURRENT_TIMESTAMP,
  `updated`   TIMESTAMP   NOT NULL          DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`rewardId`),
  UNIQUE KEY `ORDER_NO_IDX` (`orderNo`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET utf8 /