DROP TABLE IF EXISTS `alipay_callbacks` /

CREATE TABLE `alipay_callbacks` (
  `callbackId` INT(11)      NOT NULL AUTO_INCREMENT,
  `params`     VARCHAR(512) NOT NULL,
  PRIMARY KEY (`callbackId`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4