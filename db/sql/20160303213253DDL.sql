RENAME TABLE `user_events` TO `attendances` /

ALTER TABLE `attendances` CHANGE COLUMN `userEventId` `attendanceId` INT(11) AUTO_INCREMENT