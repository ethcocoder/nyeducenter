-- E-Learning System Database Schema
-- This schema defines all tables for the e-learning system including users, courses, assignments, grades, etc.

-- Drop database if it exists to start fresh
DROP DATABASE IF EXISTS elearning2;
CREATE DATABASE elearning2;
USE elearning2;

-- CORE TABLES

-- User roles
CREATE TABLE roles (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(50) NOT NULL UNIQUE,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Grade levels
CREATE TABLE grade_levels (
  id INT PRIMARY KEY AUTO_INCREMENT,
  number INT NOT NULL,
  name VARCHAR(50) NOT NULL,
  level ENUM('elementary', 'middle', 'high') NOT NULL,
  tier ENUM('lower', 'upper') NOT NULL,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY (number)
);

-- Users
CREATE TABLE users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  role_id INT NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  phone VARCHAR(20),
  address TEXT,
  city VARCHAR(100),
  state VARCHAR(100),
  country VARCHAR(100),
  avatar VARCHAR(255),
  bio TEXT,
  grade_level_id INT,
  parent_id INT NULL,
  is_active BOOLEAN DEFAULT true,
  last_login DATETIME,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (role_id) REFERENCES roles(id),
  FOREIGN KEY (grade_level_id) REFERENCES grade_levels(id) ON DELETE SET NULL,
  FOREIGN KEY (parent_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Subjects
CREATE TABLE subjects (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  code VARCHAR(20) NOT NULL UNIQUE,
  icon VARCHAR(50),
  color VARCHAR(20),
  description TEXT,
  is_elective BOOLEAN DEFAULT false,
  is_advanced BOOLEAN DEFAULT false,
  prerequisite_id INT,
  min_grade_level INT,
  max_grade_level INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (prerequisite_id) REFERENCES subjects(id) ON DELETE SET NULL
);

-- Academic terms
CREATE TABLE terms (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  is_current BOOLEAN DEFAULT false,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- COURSE MANAGEMENT

-- Courses
CREATE TABLE courses (
  id INT PRIMARY KEY AUTO_INCREMENT,
  subject_id INT NOT NULL,
  grade_level_id INT NOT NULL,
  teacher_id INT NOT NULL,
  term_id INT NOT NULL,
  name VARCHAR(255) NOT NULL,
  code VARCHAR(20) NOT NULL UNIQUE,
  description TEXT,
  syllabus TEXT,
  start_date DATE,
  end_date DATE,
  schedule TEXT,
  location VARCHAR(100),
  max_students INT DEFAULT 30,
  credits DECIMAL(3,1) DEFAULT 1.0,
  is_active BOOLEAN DEFAULT true,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (subject_id) REFERENCES subjects(id),
  FOREIGN KEY (grade_level_id) REFERENCES grade_levels(id),
  FOREIGN KEY (teacher_id) REFERENCES users(id),
  FOREIGN KEY (term_id) REFERENCES terms(id)
);

-- Course enrollments
CREATE TABLE enrollments (
  id INT PRIMARY KEY AUTO_INCREMENT,
  course_id INT NOT NULL,
  student_id INT NOT NULL,
  enrollment_date DATE NOT NULL,
  status ENUM('active', 'completed', 'dropped', 'pending') DEFAULT 'active',
  final_grade DECIMAL(5,2),
  completion_percentage DECIMAL(5,2) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY (course_id, student_id),
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Course materials (textbooks, resources, etc.)
CREATE TABLE course_materials (
  id INT PRIMARY KEY AUTO_INCREMENT,
  course_id INT NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  type ENUM('document', 'video', 'audio', 'link', 'textbook', 'other'),
  url VARCHAR(255),
  file_path VARCHAR(255),
  is_required BOOLEAN DEFAULT true,
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Course modules/units
CREATE TABLE course_modules (
  id INT PRIMARY KEY AUTO_INCREMENT,
  course_id INT NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  start_date DATE,
  end_date DATE,
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Lessons
CREATE TABLE lessons (
  id INT PRIMARY KEY AUTO_INCREMENT,
  module_id INT NOT NULL,
  title VARCHAR(255) NOT NULL,
  content TEXT,
  duration INT, -- minutes
  sort_order INT DEFAULT 0,
  is_published BOOLEAN DEFAULT false,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (module_id) REFERENCES course_modules(id) ON DELETE CASCADE
);

-- ASSESSMENT MANAGEMENT

-- Assessment types
CREATE TABLE assessment_types (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  weight DECIMAL(5,2) DEFAULT 100.00,
  grade_level_type ENUM('elementary', 'middle', 'high'),
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Assignment templates for reusability
CREATE TABLE assignment_templates (
  id INT PRIMARY KEY AUTO_INCREMENT,
  teacher_id INT NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  instructions TEXT,
  subject_id INT,
  grade_level_id INT,
  is_public BOOLEAN DEFAULT false,
  tags VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (teacher_id) REFERENCES users(id),
  FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE SET NULL,
  FOREIGN KEY (grade_level_id) REFERENCES grade_levels(id) ON DELETE SET NULL
);

-- Assignment rubrics
CREATE TABLE assignment_rubrics (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  creator_id INT NOT NULL,
  description TEXT,
  max_score DECIMAL(7,2) DEFAULT 100.00,
  is_reusable BOOLEAN DEFAULT true,
  is_public BOOLEAN DEFAULT false,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (creator_id) REFERENCES users(id)
);

-- Rubric criteria
CREATE TABLE rubric_criteria (
  id INT PRIMARY KEY AUTO_INCREMENT,
  rubric_id INT NOT NULL,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  weight DECIMAL(5,2) DEFAULT 1.00,
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (rubric_id) REFERENCES assignment_rubrics(id) ON DELETE CASCADE
);

-- Rubric levels
CREATE TABLE rubric_levels (
  id INT PRIMARY KEY AUTO_INCREMENT,
  criteria_id INT NOT NULL,
  level_name VARCHAR(100) NOT NULL,
  description TEXT,
  point_value DECIMAL(7,2) NOT NULL,
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (criteria_id) REFERENCES rubric_criteria(id) ON DELETE CASCADE
);

-- Assignments with enhanced features
CREATE TABLE assignments (
  id INT PRIMARY KEY AUTO_INCREMENT,
  course_id INT NOT NULL,
  module_id INT,
  template_id INT,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  instructions TEXT,
  assessment_type_id INT NOT NULL,
  rubric_id INT,
  due_date DATETIME,
  available_from DATETIME,
  available_until DATETIME,
  total_points DECIMAL(7,2) DEFAULT 100.00,
  passing_score DECIMAL(5,2),
  allow_late_submissions BOOLEAN DEFAULT false,
  late_submission_penalty DECIMAL(5,2),
  submission_type ENUM('text', 'file', 'link', 'media', 'multiple', 'none') NOT NULL DEFAULT 'file',
  accepted_file_types VARCHAR(255),
  max_file_size INT, -- in KB
  max_file_count INT DEFAULT 1,
  allow_resubmission BOOLEAN DEFAULT false,
  resubmission_deadline DATETIME,
  estimated_completion_time INT, -- minutes
  difficulty_level ENUM('easy', 'medium', 'hard', 'mixed') DEFAULT 'medium',
  learning_objectives TEXT,
  resources TEXT,
  plagiarism_check BOOLEAN DEFAULT false,
  group_assignment BOOLEAN DEFAULT false,
  max_group_size INT,
  peer_review BOOLEAN DEFAULT false,
  peer_review_count INT DEFAULT 0,
  peer_review_due_date DATETIME,
  creator_id INT NOT NULL,
  collaborators JSON,
  is_draft BOOLEAN DEFAULT false,
  file_attachment VARCHAR(255),
  sample_solution TEXT,
  sample_solution_visibility ENUM('after_submission', 'after_due_date', 'after_grading', 'never') DEFAULT 'after_grading',
  is_published BOOLEAN DEFAULT false,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
  FOREIGN KEY (module_id) REFERENCES course_modules(id) ON DELETE SET NULL,
  FOREIGN KEY (assessment_type_id) REFERENCES assessment_types(id),
  FOREIGN KEY (template_id) REFERENCES assignment_templates(id) ON DELETE SET NULL,
  FOREIGN KEY (rubric_id) REFERENCES assignment_rubrics(id) ON DELETE SET NULL,
  FOREIGN KEY (creator_id) REFERENCES users(id)
);

-- Student groups for group assignments
CREATE TABLE student_groups (
  id INT PRIMARY KEY AUTO_INCREMENT,
  assignment_id INT NOT NULL,
  name VARCHAR(100) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE
);

-- Group members
CREATE TABLE group_members (
  group_id INT NOT NULL,
  student_id INT NOT NULL,
  is_leader BOOLEAN DEFAULT false,
  joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (group_id, student_id),
  FOREIGN KEY (group_id) REFERENCES student_groups(id) ON DELETE CASCADE,
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Enhanced assignment submissions
CREATE TABLE assignment_submissions (
  id INT PRIMARY KEY AUTO_INCREMENT,
  assignment_id INT NOT NULL,
  student_id INT NOT NULL,
  group_id INT,
  submission_text TEXT,
  file_paths JSON, -- store multiple file paths as JSON array
  external_links JSON, -- store multiple external links
  submission_date DATETIME NOT NULL,
  revision_number INT DEFAULT 1,
  word_count INT,
  is_late BOOLEAN DEFAULT false,
  grade DECIMAL(7,2),
  percentage DECIMAL(5,2),
  letter_grade VARCHAR(5),
  feedback TEXT,
  rubric_scores JSON, -- detailed rubric scoring
  graded_by INT,
  graded_date DATETIME,
  plagiarism_score DECIMAL(5,2),
  plagiarism_report TEXT,
  submission_status ENUM('draft', 'submitted', 'resubmitted', 'graded', 'returned') DEFAULT 'submitted',
  teacher_comments TEXT,
  submission_comments TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY (assignment_id, student_id, revision_number),
  FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (group_id) REFERENCES student_groups(id) ON DELETE SET NULL,
  FOREIGN KEY (graded_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Peer reviews
CREATE TABLE peer_reviews (
  id INT PRIMARY KEY AUTO_INCREMENT,
  submission_id INT NOT NULL,
  reviewer_id INT NOT NULL,
  rating DECIMAL(3,1),
  comments TEXT,
  rubric_feedback JSON,
  is_anonymous BOOLEAN DEFAULT true,
  is_completed BOOLEAN DEFAULT false,
  assigned_date DATETIME NOT NULL,
  completed_date DATETIME,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY (submission_id, reviewer_id),
  FOREIGN KEY (submission_id) REFERENCES assignment_submissions(id) ON DELETE CASCADE,
  FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Assignment analytics for teachers
CREATE TABLE assignment_analytics (
  id INT PRIMARY KEY AUTO_INCREMENT,
  assignment_id INT NOT NULL,
  average_score DECIMAL(5,2),
  median_score DECIMAL(5,2),
  highest_score DECIMAL(5,2),
  lowest_score DECIMAL(5,2),
  submission_rate DECIMAL(5,2),
  on_time_submission_rate DECIMAL(5,2),
  average_completion_time INT, -- minutes if tracked
  difficulty_rating DECIMAL(3,2),
  distribution_data JSON, -- grade distribution
  common_issues TEXT,
  last_calculated DATETIME,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE
);

-- Quiz templates for reusability
CREATE TABLE quiz_templates (
  id INT PRIMARY KEY AUTO_INCREMENT,
  teacher_id INT NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  instructions TEXT,
  subject_id INT,
  grade_level_id INT,
  is_public BOOLEAN DEFAULT false,
  tags VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (teacher_id) REFERENCES users(id),
  FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE SET NULL,
  FOREIGN KEY (grade_level_id) REFERENCES grade_levels(id) ON DELETE SET NULL
);

-- Quizzes/Tests
CREATE TABLE quizzes (
  id INT PRIMARY KEY AUTO_INCREMENT,
  course_id INT NOT NULL,
  module_id INT,
  template_id INT,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  instructions TEXT,
  assessment_type_id INT NOT NULL,
  due_date DATETIME,
  available_from DATETIME,
  available_until DATETIME,
  time_limit INT, -- minutes
  total_points DECIMAL(7,2) DEFAULT 100.00,
  passing_score DECIMAL(5,2),
  attempts_allowed INT DEFAULT 1,
  randomize_questions BOOLEAN DEFAULT false,
  show_answers ENUM('after_submission', 'after_due_date', 'never') DEFAULT 'after_submission',
  shuffle_options BOOLEAN DEFAULT false,
  prevent_backtracking BOOLEAN DEFAULT false,
  requires_proctoring BOOLEAN DEFAULT false,
  show_results ENUM('score_only', 'full_feedback', 'correct_answers', 'none') DEFAULT 'full_feedback',
  access_code VARCHAR(20),
  time_limit_exceptions JSON,
  difficulty_level ENUM('easy', 'medium', 'hard', 'mixed') DEFAULT 'medium',
  auto_grade BOOLEAN DEFAULT true,
  creator_id INT NOT NULL,
  collaborators JSON,
  is_draft BOOLEAN DEFAULT false,
  is_published BOOLEAN DEFAULT false,
  feedback_type ENUM('automatic', 'manual', 'both') DEFAULT 'automatic',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
  FOREIGN KEY (module_id) REFERENCES course_modules(id) ON DELETE SET NULL,
  FOREIGN KEY (assessment_type_id) REFERENCES assessment_types(id),
  FOREIGN KEY (template_id) REFERENCES quiz_templates(id) ON DELETE SET NULL,
  FOREIGN KEY (creator_id) REFERENCES users(id)
);

-- Question banks for reusable questions
CREATE TABLE question_banks (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  teacher_id INT NOT NULL,
  subject_id INT,
  grade_level_id INT,
  description TEXT,
  tags VARCHAR(255),
  is_public BOOLEAN DEFAULT false,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (teacher_id) REFERENCES users(id),
  FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE SET NULL,
  FOREIGN KEY (grade_level_id) REFERENCES grade_levels(id) ON DELETE SET NULL
);

-- Quiz questions with enhanced features
CREATE TABLE quiz_questions (
  id INT PRIMARY KEY AUTO_INCREMENT,
  quiz_id INT,
  question_bank_id INT,
  question_text TEXT NOT NULL,
  question_image VARCHAR(255),
  explanation TEXT,
  question_type ENUM('multiple_choice', 'true_false', 'short_answer', 'essay', 'matching', 'fill_in_blank', 'numeric', 'drag_and_drop', 'hotspot', 'sequence') NOT NULL,
  difficulty ENUM('easy', 'medium', 'hard') DEFAULT 'medium',
  points DECIMAL(7,2) DEFAULT 1.00,
  keywords_for_auto_grading TEXT,
  time_limit INT, -- optional per-question time limit in seconds
  sort_order INT DEFAULT 0,
  is_required BOOLEAN DEFAULT true,
  tags VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
  FOREIGN KEY (question_bank_id) REFERENCES question_banks(id) ON DELETE SET NULL
);

-- Question options (for multiple choice, etc.)
CREATE TABLE question_options (
  id INT PRIMARY KEY AUTO_INCREMENT,
  question_id INT NOT NULL,
  option_text TEXT NOT NULL,
  is_correct BOOLEAN DEFAULT false,
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (question_id) REFERENCES quiz_questions(id) ON DELETE CASCADE
);

-- Quiz attempts with enhanced tracking
CREATE TABLE quiz_attempts (
  id INT PRIMARY KEY AUTO_INCREMENT,
  quiz_id INT NOT NULL,
  student_id INT NOT NULL,
  start_time DATETIME NOT NULL,
  end_time DATETIME,
  ip_address VARCHAR(45),
  user_agent TEXT,
  device_type VARCHAR(50),
  total_score DECIMAL(7,2),
  percentage DECIMAL(5,2),
  passing_status ENUM('passed', 'failed', 'incomplete') DEFAULT 'incomplete',
  time_spent INT, -- seconds
  is_completed BOOLEAN DEFAULT false,
  is_graded BOOLEAN DEFAULT false,
  attempt_number INT DEFAULT 1,
  teacher_comments TEXT,
  browser_events JSON, -- track potential suspicious activity
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Quiz question responses
CREATE TABLE quiz_responses (
  id INT PRIMARY KEY AUTO_INCREMENT,
  attempt_id INT NOT NULL,
  question_id INT NOT NULL,
  selected_option_id INT,
  text_response TEXT,
  is_correct BOOLEAN,
  points_earned DECIMAL(7,2),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (attempt_id) REFERENCES quiz_attempts(id) ON DELETE CASCADE,
  FOREIGN KEY (question_id) REFERENCES quiz_questions(id) ON DELETE CASCADE,
  FOREIGN KEY (selected_option_id) REFERENCES question_options(id) ON DELETE SET NULL
);

-- Quiz analytics for teachers
CREATE TABLE quiz_analytics (
  id INT PRIMARY KEY AUTO_INCREMENT,
  quiz_id INT NOT NULL,
  average_score DECIMAL(5,2),
  median_score DECIMAL(5,2),
  highest_score DECIMAL(5,2),
  lowest_score DECIMAL(5,2),
  pass_rate DECIMAL(5,2),
  average_completion_time INT, -- seconds
  difficulty_rating DECIMAL(3,2),
  discrimination_index DECIMAL(3,2),
  question_stats JSON, -- detailed stats for each question
  last_calculated DATETIME,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
);

-- GRADE MANAGEMENT

-- Grade items
CREATE TABLE grade_items (
  id INT PRIMARY KEY AUTO_INCREMENT,
  course_id INT NOT NULL,
  assessment_type_id INT NOT NULL,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  due_date DATETIME,
  total_points DECIMAL(7,2) DEFAULT 100.00,
  weight DECIMAL(5,2),
  is_published BOOLEAN DEFAULT false,
  assignment_id INT,
  quiz_id INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
  FOREIGN KEY (assessment_type_id) REFERENCES assessment_types(id),
  FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE SET NULL,
  FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE SET NULL
);

-- Student grades
CREATE TABLE student_grades (
  id INT PRIMARY KEY AUTO_INCREMENT,
  grade_item_id INT NOT NULL,
  student_id INT NOT NULL,
  points_earned DECIMAL(7,2),
  percentage DECIMAL(5,2),
  letter_grade VARCHAR(5),
  feedback TEXT,
  graded_by INT,
  graded_date DATETIME,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY (grade_item_id, student_id),
  FOREIGN KEY (grade_item_id) REFERENCES grade_items(id) ON DELETE CASCADE,
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (graded_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Grade category weights
CREATE TABLE grade_category_weights (
  id INT PRIMARY KEY AUTO_INCREMENT,
  course_id INT NOT NULL,
  assessment_type_id INT NOT NULL,
  weight DECIMAL(5,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY (course_id, assessment_type_id),
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
  FOREIGN KEY (assessment_type_id) REFERENCES assessment_types(id)
);

-- COMMUNICATION

-- Message categories
CREATE TABLE message_categories (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  icon VARCHAR(50),
  color VARCHAR(20),
  description TEXT,
  is_system BOOLEAN DEFAULT false,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Message threads
CREATE TABLE message_threads (
  id INT PRIMARY KEY AUTO_INCREMENT,
  subject VARCHAR(255) NOT NULL,
  category_id INT,
  created_by INT NOT NULL,
  is_group_thread BOOLEAN DEFAULT false,
  is_closed BOOLEAN DEFAULT false,
  last_activity DATETIME,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES message_categories(id) ON DELETE SET NULL,
  FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Thread participants
CREATE TABLE thread_participants (
  thread_id INT NOT NULL,
  user_id INT NOT NULL,
  is_muted BOOLEAN DEFAULT false,
  last_read_at DATETIME,
  joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (thread_id, user_id),
  FOREIGN KEY (thread_id) REFERENCES message_threads(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Enhanced messages
CREATE TABLE messages (
  id INT PRIMARY KEY AUTO_INCREMENT,
  thread_id INT NOT NULL,
  sender_id INT NOT NULL,
  reply_to_id INT,
  content TEXT NOT NULL,
  has_attachments BOOLEAN DEFAULT false,
  priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
  is_system_message BOOLEAN DEFAULT false,
  status ENUM('draft', 'sent', 'delivered', 'read') DEFAULT 'sent',
  is_edited BOOLEAN DEFAULT false,
  edited_at DATETIME,
  is_deleted BOOLEAN DEFAULT false,
  deleted_at DATETIME,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (thread_id) REFERENCES message_threads(id) ON DELETE CASCADE,
  FOREIGN KEY (sender_id) REFERENCES users(id),
  FOREIGN KEY (reply_to_id) REFERENCES messages(id) ON DELETE SET NULL
);

-- Message attachments
CREATE TABLE message_attachments (
  id INT PRIMARY KEY AUTO_INCREMENT,
  message_id INT NOT NULL,
  file_name VARCHAR(255) NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  file_size INT NOT NULL, -- in KB
  file_type VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE
);

-- Message reads
CREATE TABLE message_reads (
  message_id INT NOT NULL,
  user_id INT NOT NULL,
  read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (message_id, user_id),
  FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Message templates
CREATE TABLE message_templates (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  title VARCHAR(255) NOT NULL,
  subject VARCHAR(255),
  content TEXT NOT NULL,
  category_id INT,
  is_public BOOLEAN DEFAULT false,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (category_id) REFERENCES message_categories(id) ON DELETE SET NULL
);

-- Quick contact lists
CREATE TABLE contact_lists (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  name VARCHAR(100) NOT NULL,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Contact list members
CREATE TABLE contact_list_members (
  contact_list_id INT NOT NULL,
  user_id INT NOT NULL,
  added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (contact_list_id, user_id),
  FOREIGN KEY (contact_list_id) REFERENCES contact_lists(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Announcements
CREATE TABLE announcements (
  id INT PRIMARY KEY AUTO_INCREMENT,
  author_id INT NOT NULL,
  title VARCHAR(255) NOT NULL,
  content TEXT NOT NULL,
  target_role_id INT,
  target_grade_level_id INT,
  target_course_id INT,
  publish_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  expiry_date DATETIME,
  is_pinned BOOLEAN DEFAULT false,
  is_published BOOLEAN DEFAULT true,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (author_id) REFERENCES users(id),
  FOREIGN KEY (target_role_id) REFERENCES roles(id) ON DELETE SET NULL,
  FOREIGN KEY (target_grade_level_id) REFERENCES grade_levels(id) ON DELETE SET NULL,
  FOREIGN KEY (target_course_id) REFERENCES courses(id) ON DELETE SET NULL
);

-- Announcement reads
CREATE TABLE announcement_reads (
  announcement_id INT NOT NULL,
  user_id INT NOT NULL,
  read_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (announcement_id, user_id),
  FOREIGN KEY (announcement_id) REFERENCES announcements(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Discussion forums
CREATE TABLE forums (
  id INT PRIMARY KEY AUTO_INCREMENT,
  course_id INT,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  is_public BOOLEAN DEFAULT false,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Forum topics
CREATE TABLE forum_topics (
  id INT PRIMARY KEY AUTO_INCREMENT,
  forum_id INT NOT NULL,
  author_id INT NOT NULL,
  title VARCHAR(255) NOT NULL,
  content TEXT NOT NULL,
  is_pinned BOOLEAN DEFAULT false,
  is_locked BOOLEAN DEFAULT false,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (forum_id) REFERENCES forums(id) ON DELETE CASCADE,
  FOREIGN KEY (author_id) REFERENCES users(id)
);

-- Forum posts (replies)
CREATE TABLE forum_posts (
  id INT PRIMARY KEY AUTO_INCREMENT,
  topic_id INT NOT NULL,
  author_id INT NOT NULL,
  parent_post_id INT,
  content TEXT NOT NULL,
  is_edited BOOLEAN DEFAULT false,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (topic_id) REFERENCES forum_topics(id) ON DELETE CASCADE,
  FOREIGN KEY (author_id) REFERENCES users(id),
  FOREIGN KEY (parent_post_id) REFERENCES forum_posts(id) ON DELETE CASCADE
);

-- CALENDARS AND EVENTS

-- Calendar events
CREATE TABLE events (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT,
  course_id INT,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  start_date DATETIME NOT NULL,
  end_date DATETIME NOT NULL,
  location VARCHAR(255),
  event_type ENUM('course', 'assignment', 'quiz', 'personal', 'school', 'holiday') NOT NULL,
  is_all_day BOOLEAN DEFAULT false,
  recurrence_rule VARCHAR(255),
  color VARCHAR(20),
  is_public BOOLEAN DEFAULT false,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Event attendees
CREATE TABLE event_attendees (
  event_id INT NOT NULL,
  user_id INT NOT NULL,
  status ENUM('pending', 'accepted', 'declined', 'tentative') DEFAULT 'pending',
  PRIMARY KEY (event_id, user_id),
  FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ATTENDANCE

-- Attendance records
CREATE TABLE attendance (
  id INT PRIMARY KEY AUTO_INCREMENT,
  course_id INT NOT NULL,
  student_id INT NOT NULL,
  date DATE NOT NULL,
  status ENUM('present', 'absent', 'late', 'excused') NOT NULL,
  remarks TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY (course_id, student_id, date),
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);

-- SYSTEM SETTINGS

-- System settings
CREATE TABLE settings (
  id INT PRIMARY KEY AUTO_INCREMENT,
  setting_key VARCHAR(100) NOT NULL UNIQUE,
  setting_value TEXT,
  setting_description TEXT,
  is_public BOOLEAN DEFAULT false,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Activity logs
CREATE TABLE activity_logs (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT,
  activity_type VARCHAR(100) NOT NULL,
  description TEXT,
  ip_address VARCHAR(45),
  user_agent TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- INITIAL DATA INSERTS

-- Insert roles
INSERT INTO roles (name, description) VALUES 
('admin', 'System administrator with full access'),
('teacher', 'Teacher who can manage courses, assignments, and grades'),
('student', 'Student who can take courses, submit assignments, and view grades'),
('parent', 'Parent who can view their children\'s progress');

-- Insert grade levels
INSERT INTO grade_levels (number, name, level, tier) VALUES
(1, 'Grade 1', 'elementary', 'lower'),
(2, 'Grade 2', 'elementary', 'lower'),
(3, 'Grade 3', 'elementary', 'lower'),
(4, 'Grade 4', 'elementary', 'upper'),
(5, 'Grade 5', 'elementary', 'upper'),
(6, 'Grade 6', 'elementary', 'upper'),
(7, 'Grade 7', 'middle', 'lower'),
(8, 'Grade 8', 'middle', 'upper'),
(9, 'Grade 9', 'high', 'lower'),
(10, 'Grade 10', 'high', 'lower'),
(11, 'Grade 11', 'high', 'upper'),
(12, 'Grade 12', 'high', 'upper');

-- Insert assessment types for different grade levels
INSERT INTO assessment_types (name, weight, grade_level_type, description) VALUES
-- Elementary school
('Quiz', 20.00, 'elementary', 'Short quizzes to assess understanding'),
('Classwork', 30.00, 'elementary', 'Work completed during class time'),
('Homework', 20.00, 'elementary', 'Work completed at home'),
('Project', 30.00, 'elementary', 'Longer-term assignments involving research and creation'),

-- Middle school
('Quiz', 20.00, 'middle', 'Short quizzes to assess understanding'),
('Test', 25.00, 'middle', 'More comprehensive assessments'),
('Assignment', 20.00, 'middle', 'Regular coursework tasks'),
('Project', 25.00, 'middle', 'Longer-term assignments involving research and creation'),
('Participation', 10.00, 'middle', 'Active involvement in class activities'),

-- High school
('Quiz', 15.00, 'high', 'Short quizzes to assess understanding'),
('Test', 25.00, 'high', 'Regular tests throughout the term'),
('Exam', 30.00, 'high', 'Comprehensive final assessments'),
('Assignment', 15.00, 'high', 'Regular coursework tasks'),
('Project', 15.00, 'high', 'Research and creation work');

-- Insert subjects
INSERT INTO subjects (name, code, icon, color, description, is_elective, is_advanced) VALUES
-- Core subjects
('Mathematics', 'MATH', 'Calculate', '#4caf50', 'Core mathematics curriculum', false, false),
('Language Arts', 'LANG', 'MenuBook', '#2196f3', 'Reading, writing, and language skills', false, false),
('Science', 'SCI', 'Science', '#9c27b0', 'General science curriculum', false, false),
('Social Studies', 'SOC', 'Public', '#ff9800', 'History, geography, and social sciences', false, false),
('Health Education', 'HLTH', 'LocalHospital', '#f44336', 'Health and wellness education', false, false),

-- Electives
('Fine Arts', 'ART', 'Palette', '#e91e63', 'Visual and performing arts', true, false),
('Computer Science', 'COMP', 'Computer', '#607d8b', 'Programming and computer skills', true, false),
('Music', 'MUS', 'MusicNote', '#9e9e9e', 'Musical education and performance', true, false),
('Physical Education', 'PE', 'FitnessCenter', '#795548', 'Physical activity and sports', true, false),
('Foreign Language', 'LANG2', 'Translate', '#00bcd4', 'Second language studies', true, false),
('Business Studies', 'BUS', 'Business', '#3f51b5', 'Business and entrepreneurship education', true, false),

-- Advanced subjects
('AP Mathematics', 'AP-MATH', 'Calculate', '#1b5e20', 'Advanced placement mathematics', false, true),
('AP Science', 'AP-SCI', 'Science', '#4a148c', 'Advanced placement science', false, true),
('AP Language', 'AP-LANG', 'MenuBook', '#0d47a1', 'Advanced placement language arts', false, true),
('AP Social Studies', 'AP-SOC', 'Public', '#e65100', 'Advanced placement social studies', false, true);

-- Update prerequisites for advanced courses
UPDATE subjects SET prerequisite_id = (SELECT id FROM subjects WHERE code = 'MATH') WHERE code = 'AP-MATH';
UPDATE subjects SET prerequisite_id = (SELECT id FROM subjects WHERE code = 'SCI') WHERE code = 'AP-SCI';
UPDATE subjects SET prerequisite_id = (SELECT id FROM subjects WHERE code = 'LANG') WHERE code = 'AP-LANG';
UPDATE subjects SET prerequisite_id = (SELECT id FROM subjects WHERE code = 'SOC') WHERE code = 'AP-SOC';

-- Insert academic terms
INSERT INTO terms (name, start_date, end_date, is_current) VALUES
('Fall 2024', '2024-09-01', '2024-12-20', false),
('Spring 2025', '2025-01-15', '2025-05-31', true);

-- Insert admin user
INSERT INTO users (role_id, email, password, first_name, last_name, is_active)
VALUES (
  (SELECT id FROM roles WHERE name = 'admin'),
  'admin@example.com',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
  'System',
  'Administrator',
  true
);

-- Insert demo teacher
INSERT INTO users (role_id, email, password, first_name, last_name, is_active)
VALUES (
  (SELECT id FROM roles WHERE name = 'teacher'),
  'teacher@example.com',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
  'Demo',
  'Teacher',
  true
);

-- Insert demo students for different grade levels
INSERT INTO users (role_id, email, password, first_name, last_name, grade_level_id, is_active)
VALUES 
(
  (SELECT id FROM roles WHERE name = 'student'),
  'elementary@example.com',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'Elementary',
  'Student',
  (SELECT id FROM grade_levels WHERE number = 4),
  true
),
(
  (SELECT id FROM roles WHERE name = 'student'),
  'middle@example.com',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'Middle',
  'Student',
  (SELECT id FROM grade_levels WHERE number = 7),
  true
),
(
  (SELECT id FROM roles WHERE name = 'student'),
  'high@example.com',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'High',
  'Student',
  (SELECT id FROM grade_levels WHERE number = 10),
  true
);

-- Insert demo parent
INSERT INTO users (role_id, email, password, first_name, last_name, is_active)
VALUES (
  (SELECT id FROM roles WHERE name = 'parent'),
  'parent@example.com',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'Demo',
  'Parent',
  true
);

-- Connect parent to students
UPDATE users 
SET parent_id = (SELECT id FROM (SELECT id FROM users WHERE email = 'parent@example.com') AS p) 
WHERE email IN ('elementary@example.com', 'middle@example.com', 'high@example.com');

-- Insert demo courses
INSERT INTO courses (subject_id, grade_level_id, teacher_id, term_id, name, code, description, max_students, is_active)
VALUES 
(
  (SELECT id FROM subjects WHERE code = 'MATH'),
  (SELECT id FROM grade_levels WHERE number = 4),
  (SELECT id FROM users WHERE email = 'teacher@example.com'),
  (SELECT id FROM terms WHERE is_current = true),
  'Elementary Mathematics',
  'MATH-4',
  'Fundamental mathematics for 4th grade students',
  25,
  true
),
(
  (SELECT id FROM subjects WHERE code = 'MATH'),
  (SELECT id FROM grade_levels WHERE number = 7),
  (SELECT id FROM users WHERE email = 'teacher@example.com'),
  (SELECT id FROM terms WHERE is_current = true),
  'Middle School Mathematics',
  'MATH-7',
  'Mathematics curriculum for 7th grade students',
  30,
  true
),
(
  (SELECT id FROM subjects WHERE code = 'MATH'),
  (SELECT id FROM grade_levels WHERE number = 10),
  (SELECT id FROM users WHERE email = 'teacher@example.com'),
  (SELECT id FROM terms WHERE is_current = true),
  'High School Mathematics',
  'MATH-10',
  'Advanced mathematics for 10th grade students',
  30,
  true
);

-- Enroll students in appropriate courses
INSERT INTO enrollments (course_id, student_id, enrollment_date, status)
VALUES 
(
  (SELECT id FROM courses WHERE code = 'MATH-4'),
  (SELECT id FROM users WHERE email = 'elementary@example.com'),
  CURDATE(),
  'active'
),
(
  (SELECT id FROM courses WHERE code = 'MATH-7'),
  (SELECT id FROM users WHERE email = 'middle@example.com'),
  CURDATE(),
  'active'
),
(
  (SELECT id FROM courses WHERE code = 'MATH-10'),
  (SELECT id FROM users WHERE email = 'high@example.com'),
  CURDATE(),
  'active'
);

-- Create modules for one course
INSERT INTO course_modules (course_id, title, description, sort_order)
VALUES
(
  (SELECT id FROM courses WHERE code = 'MATH-10'),
  'Algebra Fundamentals',
  'Introduction to algebraic concepts and operations',
  1
),
(
  (SELECT id FROM courses WHERE code = 'MATH-10'),
  'Geometry Basics',
  'Introduction to geometric principles',
  2
),
(
  (SELECT id FROM courses WHERE code = 'MATH-10'),
  'Trigonometric Functions',
  'Understanding sine, cosine, and tangent',
  3
);

-- Create a sample announcement
INSERT INTO announcements (author_id, title, content, is_published)
VALUES
(
  (SELECT id FROM users WHERE email = 'admin@example.com'),
  'Welcome to the E-Learning System',
  'Welcome to our new e-learning platform. We are excited to provide this comprehensive system to enhance your learning experience. Please explore the various features and reach out if you have any questions.',
  true
);

-- Insert message categories
INSERT INTO message_categories (name, icon, color, description, is_system) VALUES
('Academic', 'School', '#2196f3', 'Messages related to academic matters', true),
('Administrative', 'AdminPanelSettings', '#ff9800', 'Administrative communications', true),
('Attendance', 'EventAvailable', '#f44336', 'Messages about attendance and absences', true),
('Homework', 'Assignment', '#4caf50', 'Communications about homework and assignments', true),
('Behavior', 'Psychology', '#9c27b0', 'Messages regarding student behavior', true),
('Events', 'Event', '#009688', 'Information about school events', true),
('Technical Support', 'SupportAgent', '#607d8b', 'Help with technical issues', true),
('Personal', 'Person', '#795548', 'Personal communications', false);

-- Create system settings
INSERT INTO settings (setting_key, setting_value, setting_description, is_public)
VALUES
('site_name', 'E-Learning System', 'The name of the website', true),
('site_description', 'A comprehensive learning management system for Ethiopian schools', 'Brief description of the site', true),
('school_name', 'Ethiopian Education Academy', 'The name of the school', true),
('school_address', 'Addis Ababa, Ethiopia', 'The address of the school', true),
('enable_messaging', 'true', 'Allow messaging between users', false),
('enable_forum', 'true', 'Enable discussion forums', false),
('default_language', 'en', 'Default language for the site', true),
('grading_system', 'letter', 'The default grading system: letter, numerical, descriptive', false),
('academic_year', '2024-2025', 'Current academic year', true); 