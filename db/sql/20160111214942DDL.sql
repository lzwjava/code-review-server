ALTER TABLE `orders` ADD COLUMN `firstRewardId` INT(11)
AFTER `status` /

ALTER TABLE `orders` ADD COLUMN `amount` INT(11) NOT NULL
AFTER `status` /

