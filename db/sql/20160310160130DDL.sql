ALTER TABLE `events` ADD COLUMN `location` VARCHAR(63) NOT NULL
AFTER `maxPeople` /

ALTER TABLE `events` ADD COLUMN `startDate` TIMESTAMP NOT NULL
AFTER `location`
