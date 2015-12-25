SET FOREIGN_KEY_CHECKS = 1 /

SET FOREIGN_KEY_CHECKS = 0  /
DROP TABLE IF EXISTS `tags` /
SET FOREIGN_KEY_CHECKS = 1  /
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
  `orderId` INT(11)          NOT NULL,
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

# INSERT INTO `tags` (`tagName`) VALUES ("图像"), ("动画"), ("IM 通信"), ("音视频"), ("支付"),("测试发布"), ("AutoLayout"), ("iOS 底层"), ("地图"), ("主流 SDK 使用"), ("UI")

ALTER TABLE `learners` ADD COLUMN `introduction` VARCHAR(511) /

ALTER TABLE `reviewers` ADD COLUMN `company` VARCHAR(127),
ADD COLUMN `jobTitle` VARCHAR(127), ADD COLUMN `gitHubUsername` VARCHAR(127) /

ALTER TABLE `learners` ADD COLUMN `company` VARCHAR(127),
ADD COLUMN `jobTitle` VARCHAR(127), ADD COLUMN `gitHubUsername` VARCHAR(127) /

ALTER VIEW `users` AS SELECT
                        `id`,
                        `username`,
                        `mobilePhoneNumber`,
                        `avatarUrl`,
                        `sessionToken`,
                        `sessionTokenCreated`,
                        `password`,
                        `created`,
                        `type`,
                        `introduction`,
                        `company`,
                        `jobTitle`,
                        `gitHubUsername`
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
                              `type`,
                              `introduction`,
                              `company`,
                              `jobTitle`,
                              `gitHubUsername`
                            FROM `reviewers`  /

ALTER TABLE `reviewers` ADD COLUMN `maxOrders` INT UNSIGNED DEFAULT 8 /

ALTER TABLE `orders` ADD COLUMN `codeLines` INT UNSIGNED NOT NULL
AFTER `reviewerId` /

ALTER TABLE `reviews` ADD COLUMN `title` VARCHAR(255) NOT NULL AFTER `orderId` /