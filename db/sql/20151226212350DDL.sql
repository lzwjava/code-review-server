ALTER TABLE `orders` MODIFY COLUMN `updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
ON UPDATE CURRENT_TIMESTAMP /

ALTER TABLE `reviews` MODIFY COLUMN `updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
ON UPDATE CURRENT_TIMESTAMP /

ALTER TABLE `charges` MODIFY COLUMN `updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
ON UPDATE CURRENT_TIMESTAMP /

ALTER TABLE `rewards` MODIFY COLUMN `updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
ON UPDATE CURRENT_TIMESTAMP /

ALTER TABLE `reviewers` ADD COLUMN `updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
ON UPDATE CURRENT_TIMESTAMP
AFTER `created` /

ALTER TABLE `learners` ADD COLUMN `updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
ON UPDATE CURRENT_TIMESTAMP
AFTER `created` /

ALTER VIEW `users` AS SELECT
                        `id`,
                        `username`,
                        `mobilePhoneNumber`,
                        `avatarUrl`,
                        `sessionToken`,
                        `sessionTokenCreated`,
                        `password`,
                        `created`,
                        `updated`,
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
                              `updated`,
                              `type`,
                              `introduction`,
                              `company`,
                              `jobTitle`,
                              `gitHubUsername`
                            FROM `reviewers`  /

ALTER TABLE `reviews` ADD CONSTRAINT `reviews_fk_1` FOREIGN KEY (`orderId`) REFERENCES `orders` (`orderId`) /

ALTER TABLE `rewards` ADD CONSTRAINT `rewards_fk_1` FOREIGN KEY (`chargeId`) REFERENCES `charges` (`chargeId`),
ADD CONSTRAINT `rewards_fk_2` FOREIGN KEY (`orderId`) REFERENCES `orders` (`orderId`) /
