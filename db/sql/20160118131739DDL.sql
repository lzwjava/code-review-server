CREATE TABLE `applications` (
  `applicationId` INT(11)              AUTO_INCREMENT,
  `learnerId`     VARCHAR(31) NOT NULL,
  `created`       TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`applicationId`),
  UNIQUE KEY `LEARNER_IDX` (`learnerId`)
) /

