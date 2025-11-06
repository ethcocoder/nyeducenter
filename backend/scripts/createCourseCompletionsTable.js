const { pool } = require('../config/db.config');

const createCourseCompletionsTableSQL = `
CREATE TABLE IF NOT EXISTS course_completions (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  course_id INT NOT NULL,
  completed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);
`;

async function createCourseCompletionsTable() {
  try {
    await pool.query(createCourseCompletionsTableSQL);
    console.log('course_completions table created or already exists.');
    process.exit(0);
  } catch (err) {
    console.error('Error creating course_completions table:', err);
    process.exit(1);
  }
}

createCourseCompletionsTable(); 