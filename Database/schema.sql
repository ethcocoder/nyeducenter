-- Course Management System Schema

-- Users and Authentication
CREATE TABLE IF NOT EXISTS admin (
    admin_id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS instructor (
    instructor_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    date_of_birth DATE,
    date_of_joined DATE NOT NULL,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    profile_img VARCHAR(255) DEFAULT 'default.jpg',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS student (
    student_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    date_of_birth DATE,
    date_of_joined DATE NOT NULL,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    profile_img VARCHAR(255) DEFAULT 'default.jpg',
    preferred_language ENUM('en', 'am', 'ti', 'om') DEFAULT 'en',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Course Management
CREATE TABLE IF NOT EXISTS course (
    course_id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    title_am VARCHAR(255),
    title_ti VARCHAR(255),
    title_om VARCHAR(255),
    description TEXT,
    description_am TEXT,
    description_ti TEXT,
    description_om TEXT,
    cover_img VARCHAR(255) DEFAULT 'default_course.jpg',
    status ENUM('public', 'private') DEFAULT 'public',
    created_by INT NOT NULL,
    created_by_role ENUM('admin', 'instructor') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES instructor(instructor_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS course_objective (
    objective_id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    objective_number INT NOT NULL,
    description TEXT NOT NULL,
    description_am TEXT,
    description_ti TEXT,
    description_om TEXT,
    bloom_level ENUM('remember', 'understand', 'apply', 'analyze', 'evaluate', 'create') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES course(course_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS chapter (
    chapter_id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    title_am VARCHAR(255),
    title_ti VARCHAR(255),
    title_om VARCHAR(255),
    description TEXT,
    description_am TEXT,
    description_ti TEXT,
    description_om TEXT,
    week_number INT NOT NULL,
    `order` INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES course(course_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS module_objective (
    objective_id INT PRIMARY KEY AUTO_INCREMENT,
    chapter_id INT NOT NULL,
    objective_number INT NOT NULL,
    description TEXT NOT NULL,
    description_am TEXT,
    description_ti TEXT,
    description_om TEXT,
    bloom_level ENUM('remember', 'understand', 'apply', 'analyze', 'evaluate', 'create') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (chapter_id) REFERENCES chapter(chapter_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS topic (
    topic_id INT PRIMARY KEY AUTO_INCREMENT,
    chapter_id INT NOT NULL,
    course_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    title_am VARCHAR(255),
    title_ti VARCHAR(255),
    title_om VARCHAR(255),
    content TEXT,
    content_am TEXT,
    content_ti TEXT,
    content_om TEXT,
    `order` INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (chapter_id) REFERENCES chapter(chapter_id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES course(course_id) ON DELETE CASCADE
);

-- Quiz Management
CREATE TABLE IF NOT EXISTS quiz (
    quiz_id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    title_am VARCHAR(255),
    title_ti VARCHAR(255),
    title_om VARCHAR(255),
    description TEXT,
    description_am TEXT,
    description_ti TEXT,
    description_om TEXT,
    passing_score INT NOT NULL DEFAULT 70,
    time_limit INT NOT NULL DEFAULT 30, -- in minutes
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES course(course_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS question (
    question_id INT PRIMARY KEY AUTO_INCREMENT,
    quiz_id INT NOT NULL,
    question_text TEXT NOT NULL,
    question_text_am TEXT,
    question_text_ti TEXT,
    question_text_om TEXT,
    question_type ENUM('multiple_choice', 'true_false', 'short_answer') NOT NULL,
    points INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (quiz_id) REFERENCES quiz(quiz_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS answer_option (
    option_id INT PRIMARY KEY AUTO_INCREMENT,
    question_id INT NOT NULL,
    option_text TEXT NOT NULL,
    option_text_am TEXT,
    option_text_ti TEXT,
    option_text_om TEXT,
    is_correct BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (question_id) REFERENCES question(question_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS quiz_attempt (
    attempt_id INT PRIMARY KEY AUTO_INCREMENT,
    quiz_id INT NOT NULL,
    student_id INT NOT NULL,
    score INT,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (quiz_id) REFERENCES quiz(quiz_id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES student(student_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS student_answer (
    answer_id INT PRIMARY KEY AUTO_INCREMENT,
    attempt_id INT NOT NULL,
    question_id INT NOT NULL,
    selected_option_id INT,
    text_answer TEXT,
    is_correct BOOLEAN,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (attempt_id) REFERENCES quiz_attempt(attempt_id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES question(question_id) ON DELETE CASCADE,
    FOREIGN KEY (selected_option_id) REFERENCES answer_option(option_id) ON DELETE CASCADE
);

-- Enrollment and Progress
CREATE TABLE IF NOT EXISTS enrolled_student (
    enrolled_id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    student_id INT NOT NULL,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES course(course_id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES student(student_id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (course_id, student_id)
);

CREATE TABLE IF NOT EXISTS student_course (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    student_id INT NOT NULL,
    progress DECIMAL(5,2) DEFAULT 0.00,
    last_accessed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES course(course_id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES student(student_id) ON DELETE CASCADE,
    UNIQUE KEY unique_student_course (course_id, student_id)
);

-- Certificates
CREATE TABLE IF NOT EXISTS certificate (
    certificate_id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    student_id INT NOT NULL,
    issue_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    certificate_number VARCHAR(50) UNIQUE NOT NULL,
    FOREIGN KEY (course_id) REFERENCES course(course_id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES student(student_id) ON DELETE CASCADE,
    UNIQUE KEY unique_certificate (course_id, student_id)
); 