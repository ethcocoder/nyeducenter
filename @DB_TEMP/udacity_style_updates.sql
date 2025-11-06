-- Add new tables for High School Learning Paths (adapted from Udacity style)

-- Learning Paths (replacing Nanodegree Programs)
CREATE TABLE IF NOT EXISTS `learning_path` (
  `path_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `grade_level` enum('Grade 9','Grade 10','Grade 11','Grade 12') NOT NULL,
  `subject` varchar(100) NOT NULL,
  `semester` enum('First','Second') NOT NULL,
  `credits` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `department_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`path_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- School Departments (replacing Industry Partners)
CREATE TABLE IF NOT EXISTS `school_department` (
  `department_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `head_teacher_id` int(11),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`department_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Path-Course Relationship
CREATE TABLE IF NOT EXISTS `path_course` (
  `path_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `sequence_order` int(11) NOT NULL,
  PRIMARY KEY (`path_id`,`course_id`),
  CONSTRAINT `path_course_ibfk_1` FOREIGN KEY (`path_id`) REFERENCES `learning_path` (`path_id`) ON DELETE CASCADE,
  CONSTRAINT `path_course_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `course` (`course_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Assignments (replacing Projects)
CREATE TABLE IF NOT EXISTS `assignment` (
  `assignment_id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `rubric` text NOT NULL,
  `due_date` date DEFAULT NULL,
  `max_score` int(11) NOT NULL,
  `assignment_type` enum('Homework','Project','Quiz','Test','Final Exam') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`assignment_id`),
  CONSTRAINT `assignment_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course` (`course_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Assignment Submissions
CREATE TABLE IF NOT EXISTS `assignment_submission` (
  `submission_id` int(11) NOT NULL AUTO_INCREMENT,
  `assignment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `submission_file` varchar(255) NOT NULL,
  `submission_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Submitted','Under Review','Graded','Late') NOT NULL DEFAULT 'Submitted',
  `score` decimal(5,2) DEFAULT NULL,
  `feedback` text,
  `teacher_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`submission_id`),
  CONSTRAINT `assignment_submission_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `assignment` (`assignment_id`),
  CONSTRAINT `assignment_submission_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`),
  CONSTRAINT `assignment_submission_ibfk_3` FOREIGN KEY (`teacher_id`) REFERENCES `instructor` (`instructor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Teachers (replacing Mentors)
CREATE TABLE IF NOT EXISTS `teacher` (
  `teacher_id` int(11) NOT NULL AUTO_INCREMENT,
  `instructor_id` int(11) NOT NULL,
  `subject_specialty` varchar(100) NOT NULL,
  `office_hours` text,
  `max_students` int(11) DEFAULT 30,
  PRIMARY KEY (`teacher_id`),
  CONSTRAINT `teacher_ibfk_1` FOREIGN KEY (`instructor_id`) REFERENCES `instructor` (`instructor_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Teacher-Student Relationships
CREATE TABLE IF NOT EXISTS `teacher_student` (
  `teacher_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `path_id` int(11) NOT NULL,
  `start_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `end_date` timestamp NULL DEFAULT NULL,
  `status` enum('Active','Completed','Dropped') NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`teacher_id`,`student_id`,`path_id`),
  CONSTRAINT `teacher_student_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`teacher_id`),
  CONSTRAINT `teacher_student_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`),
  CONSTRAINT `teacher_student_ibfk_3` FOREIGN KEY (`path_id`) REFERENCES `learning_path` (`path_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Assessments
CREATE TABLE IF NOT EXISTS `assessment` (
  `assessment_id` int(11) NOT NULL AUTO_INCREMENT,
  `path_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `passing_score` int(11) NOT NULL,
  `assessment_type` enum('Quiz','Test','Midterm','Final') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`assessment_id`),
  CONSTRAINT `assessment_ibfk_1` FOREIGN KEY (`path_id`) REFERENCES `learning_path` (`path_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Student Support Services
CREATE TABLE IF NOT EXISTS `support_service` (
  `service_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `type` enum('Tutoring','Study Skills','College Prep','Counseling') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Student Support Service Bookings
CREATE TABLE IF NOT EXISTS `student_support_service` (
  `student_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `status` enum('Scheduled','Completed','Cancelled') NOT NULL DEFAULT 'Scheduled',
  `scheduled_date` timestamp NULL DEFAULT NULL,
  `notes` text,
  PRIMARY KEY (`student_id`,`service_id`),
  CONSTRAINT `student_support_service_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`),
  CONSTRAINT `student_support_service_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `support_service` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Modify existing course table
ALTER TABLE `course`
ADD COLUMN IF NOT EXISTS `estimated_hours` int(11) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `prerequisites` text,
ADD COLUMN IF NOT EXISTS `learning_outcomes` text,
ADD COLUMN IF NOT EXISTS `grade_level` enum('Grade 9','Grade 10','Grade 11','Grade 12') NOT NULL,
ADD COLUMN IF NOT EXISTS `subject` varchar(100) NOT NULL;

-- Add foreign key for department (if it doesn't exist)
SET @constraint_name = 'learning_path_ibfk_1';
SET @constraint_exists = (
    SELECT COUNT(*)
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'learning_path'
    AND CONSTRAINT_NAME = @constraint_name
);

SET @sql = IF(@constraint_exists = 0,
    'ALTER TABLE `learning_path` ADD CONSTRAINT `learning_path_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `school_department` (`department_id`)',
    'SELECT "Foreign key constraint already exists"'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt; 