SET FOREIGN_KEY_CHECKS = 1 /

DROP TABLE IF EXISTS `tags` /
CREATE TABLE `tags` (
  `tagId`   INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tagName` VARCHAR(127)     NOT NULL,
  PRIMARY KEY (`tagId`),
  UNIQUE KEY `TAG_NAME_IDX`(`tagName`)
)
  ENGINE InnoDB
  DEFAULT CHARSET utf8 /

DROP TABLE IF EXISTS `users_tags` /
CREATE TABLE `users_tags` (
  `id`     INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `userId` VARCHAR(31)      NOT NULL,
  `tagId`  INT(11) UNSIGNED NOT NULL,

  PRIMARY KEY (`id`),
  UNIQUE KEY `TAG_IDX` (`tagId`, `userId`),

  FOREIGN KEY (`tagId`)
  REFERENCES `tags` (`tagId`)
    ON DELETE CASCADE
)
  ENGINE InnoDB
  DEFAULT CHARSET utf8 /

DROP TABLE IF EXISTS `orders_tags` /
CREATE TABLE `orders_tags` (
  `id`      INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `orderId` INT(11)      NOT NULL,
  `tagId`   INT(11) UNSIGNED NOT NULL,

  PRIMARY KEY (`id`),
  UNIQUE KEY `TAG_IDX` (`tagId`, `orderId`),

  FOREIGN KEY (`tagId`)
  REFERENCES `tags` (`tagId`)
    ON DELETE CASCADE,
  FOREIGN KEY (`orderId`)
  REFERENCES `orders` (`orderId`)
    ON DELETE CASCADE
)
  ENGINE InnoDB
  DEFAULT CHARSET utf8 /

DELETE FROM `tags` /
INSERT INTO `tags` (`tagName`) VALUES ("图像"), ("动画"), ("IM 通信"),("音视频"), ("支付"),
("测试发布"), ("AutoLayout"), ("iOS 底层"), ("地图"),("主流 SDK 使用"), ("UI") /