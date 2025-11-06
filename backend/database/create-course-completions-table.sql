CREATE TABLE IF NOT EXISTS course_completions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  course_id INT NOT NULL,
  completed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_completion (user_id, course_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
); 