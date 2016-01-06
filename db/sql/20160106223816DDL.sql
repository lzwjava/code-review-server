ALTER TABLE `orders` DROP COLUMN `displaying` /

ALTER TABLE `reviews` ADD COLUMN `displaying` TINYINT NOT NULL DEFAULT 0
AFTER `content` /

ALTER TABLE `orders` DROP COLUMN `coverUrl` /

ALTER TABLE `reviews` ADD COLUMN `coverUrl` VARCHAR(255)
AFTER `displaying`

DROP TABLE IF EXISTS `orders_tags` /

CREATE TABLE `reviews_tags` (
  `id`       INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `reviewId` INT(11)          NOT NULL,
  `tagId`    INT(11) UNSIGNED NOT NULL,

  PRIMARY KEY (`id`),
  UNIQUE KEY `TAG_IDX` (`tagId`, `reviewId`),

  FOREIGN KEY (`tagId`)
  REFERENCES `tags` (`tagId`)
    ON DELETE CASCADE,
  FOREIGN KEY (`reviewId`)
  REFERENCES `reviews` (`reviewId`)
    ON DELETE CASCADE
)
  ENGINE InnoDB
  DEFAULT CHARSET utf8 /