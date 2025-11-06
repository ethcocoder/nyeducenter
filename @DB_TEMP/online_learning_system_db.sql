-- First, drop foreign key constraints
ALTER TABLE `course` DROP FOREIGN KEY `course_ibfk_1`;
ALTER TABLE `student_course` DROP FOREIGN KEY IF EXISTS `student_course_ibfk_1`;
ALTER TABLE `student_course` DROP FOREIGN KEY IF EXISTS `student_course_ibfk_2`;
ALTER TABLE `chapter` DROP FOREIGN KEY IF EXISTS `chapter_ibfk_1`;
ALTER TABLE `topic` DROP FOREIGN KEY IF EXISTS `topic_ibfk_1`;
ALTER TABLE `topic` DROP FOREIGN KEY IF EXISTS `topic_ibfk_2`;

-- Then truncate tables in correct order
TRUNCATE TABLE `topic`;
TRUNCATE TABLE `chapter`;
TRUNCATE TABLE `student_course`;
TRUNCATE TABLE `course`;
TRUNCATE TABLE `student`;
TRUNCATE TABLE `instructor`;
TRUNCATE TABLE `admin`;

-- Reset auto-increment values
ALTER TABLE `admin` AUTO_INCREMENT = 1;
ALTER TABLE `instructor` AUTO_INCREMENT = 1;
ALTER TABLE `student` AUTO_INCREMENT = 1;
ALTER TABLE `course` AUTO_INCREMENT = 1;
ALTER TABLE `chapter` AUTO_INCREMENT = 1;
ALTER TABLE `topic` AUTO_INCREMENT = 1;

-- Insert test admin
INSERT INTO `admin` (`admin_id`, `full_name`, `email`, `username`, `password`) VALUES
(1, 'Admin User', 'admin@test.com', 'admin', '$2y$10$YourNewHashHere');

-- Insert test students
INSERT INTO `student` (`student_id`, `username`, `password`, `first_name`, `last_name`, `email`, `date_of_birth`, `date_of_joined`) VALUES
(1, 'student1', '$2y$10$8K1p/a0dR1Ux5Yg9zq6J3O9v6Z9K9v6Z9K9v6Z9K9v6Z9K9v6Z9K9v6', 'Test', 'Student', 'student@test.com', '2000-01-01', '2024-01-01'),
(2, 'student2', '$2y$10$8K1p/a0dR1Ux5Yg9zq6J3O9v6Z9K9v6Z9K9v6Z9K9v6Z9K9v6Z9K9v6', 'John', 'Student', 'john@test.com', '2000-01-01', '2024-01-01');

-- Insert test instructor
INSERT INTO `instructor` (`instructor_id`, `username`, `password`, `first_name`, `last_name`, `email`, `date_of_birth`, `date_of_joined`) VALUES
(1, 'instructor1', '$2y$10$8K1p/a0dR1Ux5Yg9zq6J3O9v6Z9K9v6Z9K9v6Z9K9v6Z9K9v6Z9K9v6', 'Test', 'Instructor', 'instructor@test.com', '1985-01-01', '2024-01-01');

-- Re-add foreign key constraints
ALTER TABLE `course` ADD CONSTRAINT `course_ibfk_1` FOREIGN KEY (`instructor_id`) REFERENCES `instructor` (`instructor_id`);
ALTER TABLE `student_course` ADD CONSTRAINT `student_course_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`);
ALTER TABLE `student_course` ADD CONSTRAINT `student_course_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `course` (`course_id`);
ALTER TABLE `chapter` ADD CONSTRAINT `chapter_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course` (`course_id`);
ALTER TABLE `topic` ADD CONSTRAINT `topic_ibfk_1` FOREIGN KEY (`chapter_id`) REFERENCES `chapter` (`chapter_id`);
ALTER TABLE `topic` ADD CONSTRAINT `topic_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `course` (`course_id`);