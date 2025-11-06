const { pool } = require('../config/db.config');

const createUsersTableSQL = `
CREATE TABLE IF NOT EXISTS users (
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
`;

async function createUsersTable() {
  try {
    await pool.query(createUsersTableSQL);
    console.log('Users table created or already exists.');
    process.exit(0);
  } catch (err) {
    console.error('Error creating users table:', err);
    process.exit(1);
  }
}

createUsersTable(); 