CREATE TABLE IF NOT EXISTS `system_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `action` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_type` enum('admin','instructor','student') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`user_id`),
  KEY `user_type` (`user_type`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 