CREATE TABLE IF NOT EXISTS `learning_paths` (
  `path_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `level` enum('Beginner','Intermediate','Advanced') NOT NULL,
  `duration` int(11) NOT NULL COMMENT 'Duration in hours',
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`path_id`),
  KEY `instructor_id` (`instructor_id`),
  KEY `subject` (`subject`),
  KEY `status` (`status`),
  CONSTRAINT `learning_paths_ibfk_1` FOREIGN KEY (`instructor_id`) REFERENCES `instructor` (`instructor_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `learning_path_enrollments` (
  `enrollment_id` int(11) NOT NULL AUTO_INCREMENT,
  `path_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `enrolled_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('Active','Completed','Dropped') NOT NULL DEFAULT 'Active',
  `progress` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`enrollment_id`),
  UNIQUE KEY `path_student` (`path_id`,`student_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `learning_path_enrollments_ibfk_1` FOREIGN KEY (`path_id`) REFERENCES `learning_paths` (`path_id`) ON DELETE CASCADE,
  CONSTRAINT `learning_path_enrollments_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 