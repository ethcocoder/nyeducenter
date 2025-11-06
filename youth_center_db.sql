CREATE DATABASE IF NOT EXISTS youth_center_db;

USE youth_center_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'admin'
);

CREATE TABLE IF NOT EXISTS questionnaires (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    questionnaire_id INT NOT NULL,
    type VARCHAR(50) NOT NULL, -- e.g., 'text', 'radio', 'checkbox'
    text TEXT NOT NULL,
    options TEXT, -- JSON string for radio/checkbox options
    `order` INT NOT NULL,
    FOREIGN KEY (questionnaire_id) REFERENCES questionnaires(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS user_responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    gender VARCHAR(10),
    age VARCHAR(10),
    education VARCHAR(50),
    work_experience VARCHAR(20),
    workplace_name VARCHAR(255),
    department VARCHAR(255),
    job_position VARCHAR(255),
    field_of_study VARCHAR(255),
    topics TEXT,
    other_topics TEXT,
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP
);


CREATE INDEX idx_responses_questionnaire_id ON responses (questionnaire_id);

CREATE TABLE IF NOT EXISTS answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    response_id INT NOT NULL,
    question_id INT NOT NULL,
    answer_text TEXT, -- For text answers
    answer_value TEXT, -- For selected options (e.g., 'option1', 'option1,option2')
    FOREIGN KEY (response_id) REFERENCES responses(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
);

CREATE INDEX idx_answers_response_id ON answers (response_id);
CREATE INDEX idx_answers_question_id ON answers (question_id);

CREATE TABLE IF NOT EXISTS subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_subscribers_email ON subscribers (email);

-- Add index for users.username
CREATE INDEX idx_users_username ON users (username);