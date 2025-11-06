const { pool } = require('../config/db.config');

const createAdminTableSQL = `
CREATE TABLE IF NOT EXISTS admin (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  admin_level VARCHAR(50) DEFAULT 'super',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY unique_user_id (user_id)
);
`;

const createAuthTableSQL = `
CREATE TABLE IF NOT EXISTS auth (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY unique_user_id (user_id)
);
`;

async function createTables() {
  try {
    // Check if users table exists
    const [usersTable] = await pool.query('SHOW TABLES LIKE "users"');
    if (usersTable.length === 0) {
      // Create users table first if it doesn't exist
      const createUsersTableSQL = `
      CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        role_id INT NOT NULL DEFAULT 3,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        grade_level_id INT,
        is_active BOOLEAN DEFAULT true,
        avatar VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
      );
      `;
      await pool.query(createUsersTableSQL);
      console.log('Users table created.');
    } else {
      console.log('Users table already exists.');
    }

    // Check if admin table exists
    const [adminTable] = await pool.query('SHOW TABLES LIKE "admin"');
    if (adminTable.length === 0) {
      await pool.query(createAdminTableSQL);
      console.log('Admin table created.');
    } else {
      console.log('Admin table already exists.');
    }
    
    // Check if auth table exists
    const [authTable] = await pool.query('SHOW TABLES LIKE "auth"');
    if (authTable.length === 0) {
      await pool.query(createAuthTableSQL);
      console.log('Auth table created.');
    } else {
      console.log('Auth table already exists.');
    }

    process.exit(0);
  } catch (err) {
    console.error('Error creating tables:', err);
    process.exit(1);
  }
}

createTables();
