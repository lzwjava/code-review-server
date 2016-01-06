ALTER TABLE `orders` ADD COLUMN `displaying` TINYINT NOT NULL DEFAULT 0
AFTER `status` /

ALTER TABLE `orders` ADD COLUMN `coverUrl` VARCHAR(255)
AFTER `displaying`

