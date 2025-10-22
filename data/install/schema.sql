CREATE TABLE `roles` (
  `id` int(11) AUTO_INCREMENT NOT NULL,
  `name` varchar(100) NOT NULL,
  `label` varchar(100) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created` tinyint(1) NOT NULL DEFAULT 0,
  `parent` varchar(100) DEFAULT NULL,
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `allow` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `deny` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  UNIQUE INDEX `name` (`name`),
  PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
