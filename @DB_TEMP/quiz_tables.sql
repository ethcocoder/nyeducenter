-- --------------------------------------------------------

--
-- Table structure for table `quiz`
--

CREATE TABLE `quiz` (
  `quiz_id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `chapter_id` int(11) DEFAULT NULL,
  `topic_id` int(11) DEFAULT NULL,
  `title_en` varchar(1023) NOT NULL,
  `title_am` varchar(1023) NOT NULL,
  `title_ti` varchar(1023) NOT NULL,
  `title_om` varchar(1023) NOT NULL,
  `description_en` text DEFAULT NULL,
  `description_am` text DEFAULT NULL,
  `description_ti` text DEFAULT NULL,
  `description_om` text DEFAULT NULL,
  `passing_score` int(11) DEFAULT 70,
  `time_limit` int(11) DEFAULT NULL,
  `status` enum('Public','Private') NOT NULL DEFAULT 'Public',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) NOT NULL,
  `created_by_role` enum('admin','instructor') NOT NULL,
  PRIMARY KEY (`quiz_id`),
  KEY `course_id` (`course_id`),
  KEY `chapter_id` (`chapter_id`),
  KEY `topic_id` (`topic_id`),
  CONSTRAINT `quiz_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course` (`course_id`),
  CONSTRAINT `quiz_ibfk_2` FOREIGN KEY (`chapter_id`) REFERENCES `chapter` (`chapter_id`),
  CONSTRAINT `quiz_ibfk_3` FOREIGN KEY (`topic_id`) REFERENCES `topic` (`topic_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question`
--

CREATE TABLE `question` (
  `question_id` int(11) NOT NULL AUTO_INCREMENT,
  `quiz_id` int(11) NOT NULL,
  `question_text_en` text NOT NULL,
  `question_text_am` text NOT NULL,
  `question_text_ti` text NOT NULL,
  `question_text_om` text NOT NULL,
  `question_type` enum('multiple_choice','true_false','short_answer') NOT NULL DEFAULT 'multiple_choice',
  `points` int(11) NOT NULL DEFAULT 1,
  `order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`question_id`),
  KEY `quiz_id` (`quiz_id`),
  CONSTRAINT `question_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quiz` (`quiz_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question_option`
--

CREATE TABLE `question_option` (
  `option_id` int(11) NOT NULL AUTO_INCREMENT,
  `question_id` int(11) NOT NULL,
  `option_text_en` text NOT NULL,
  `option_text_am` text NOT NULL,
  `option_text_ti` text NOT NULL,
  `option_text_om` text NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0,
  `order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`option_id`),
  KEY `question_id` (`question_id`),
  CONSTRAINT `question_option_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `question` (`question_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_attempt`
--

CREATE TABLE `quiz_attempt` (
  `attempt_id` int(11) NOT NULL AUTO_INCREMENT,
  `quiz_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `start_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `end_time` timestamp NULL DEFAULT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `status` enum('in_progress','completed','abandoned') NOT NULL DEFAULT 'in_progress',
  PRIMARY KEY (`attempt_id`),
  KEY `quiz_id` (`quiz_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `quiz_attempt_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quiz` (`quiz_id`),
  CONSTRAINT `quiz_attempt_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_answer`
--

CREATE TABLE `quiz_answer` (
  `answer_id` int(11) NOT NULL AUTO_INCREMENT,
  `attempt_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `selected_option_id` int(11) DEFAULT NULL,
  `text_answer` text DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT NULL,
  `points_earned` decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (`answer_id`),
  KEY `attempt_id` (`attempt_id`),
  KEY `question_id` (`question_id`),
  KEY `selected_option_id` (`selected_option_id`),
  CONSTRAINT `quiz_answer_ibfk_1` FOREIGN KEY (`attempt_id`) REFERENCES `quiz_attempt` (`attempt_id`) ON DELETE CASCADE,
  CONSTRAINT `quiz_answer_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `question` (`question_id`),
  CONSTRAINT `quiz_answer_ibfk_3` FOREIGN KEY (`selected_option_id`) REFERENCES `question_option` (`option_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci; 