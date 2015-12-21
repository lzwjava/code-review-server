ALTER TABLE `rewards` ADD COLUMN `orderId` INT(11) NOT NULL
AFTER `rewardId` /

DROP TABLE IF EXISTS `charges` /
CREATE TABLE `charges` (
  `chargeId`  INT(11)     NOT NULL          AUTO_INCREMENT,
  `orderNo`   VARCHAR(31) NOT NULL,
  `amount`    INT(11)     NOT NULL,
  `paid`      TINYINT(2)  NOT NULL          DEFAULT 0,

  `creator`   VARCHAR(31) NOT NULL,
  `creatorIP` VARCHAR(63) NOT NULL,

  `created`   TIMESTAMP   NOT NULL          DEFAULT CURRENT_TIMESTAMP,
  `updated`   TIMESTAMP   NOT NULL          DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`chargeId`),
  UNIQUE KEY `ORDER_NO_IDX` (`orderNo`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET utf8 /

DROP TABLE IF EXISTS `rewards` /
CREATE TABLE `rewards` (
  `rewardId` INT(11)   NOT NULL          AUTO_INCREMENT,

  `orderId`  INT(11)   NOT NULL,

  `chargeId` INT(11)   NOT NULL,

  `creator`  VARCHAR(31) NOT NULL,

  `created`  TIMESTAMP NOT NULL          DEFAULT CURRENT_TIMESTAMP,
  `updated`  TIMESTAMP NOT NULL          DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`rewardId`),
  UNIQUE KEY `CHARGE_ID_IDX` (`chargeId`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET utf8 /