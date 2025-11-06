CREATE TABLE IF NOT EXISTS `learning_modules` (
  `module_id` int(11) NOT NULL AUTO_INCREMENT,
  `path_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `duration` int(11) NOT NULL COMMENT 'Duration in hours',
  `order_number` int(11) NOT NULL DEFAULT 0,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`module_id`),
  KEY `path_id` (`path_id`),
  CONSTRAINT `learning_modules_ibfk_1` FOREIGN KEY (`path_id`) REFERENCES `learning_paths` (`path_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `module_resources` (
  `resource_id` int(11) NOT NULL AUTO_INCREMENT,
  `module_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `type` enum('Video','Document','Quiz','Assignment') NOT NULL,
  `url` varchar(255) NOT NULL,
  `duration` int(11) DEFAULT NULL COMMENT 'Duration in minutes',
  `order_number` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`resource_id`),
  KEY `module_id` (`module_id`),
  CONSTRAINT `module_resources_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `learning_modules` (`module_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `student_module_progress` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `status` enum('Not Started','In Progress','Completed') NOT NULL DEFAULT 'Not Started',
  `progress_percentage` int(11) NOT NULL DEFAULT 0,
  `last_accessed` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_module` (`student_id`,`module_id`),
  KEY `module_id` (`module_id`),
  CONSTRAINT `student_module_progress_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  CONSTRAINT `student_module_progress_ibfk_2` FOREIGN KEY (`module_id`) REFERENCES `learning_modules` (`module_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 