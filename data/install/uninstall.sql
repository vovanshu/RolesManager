SET foreign_key_checks = 0;
DROP TABLE IF EXISTS `roles`;
DELETE FROM `setting` WHERE `id` LIKE 'roles_manager_%';
SET foreign_key_checks = 1;
