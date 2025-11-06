const { pool } = require('../config/db.config');

const createRegistrationRequestsTableSQL = `
CREATE TABLE IF NOT EXISTS registration_requests (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  registration_code VARCHAR(10),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
`;

async function createTable() {
  try {
    // Drop existing table if it exists
    await pool.query('DROP TABLE IF EXISTS registration_requests');
    
    // Create new table
    await pool.query(createRegistrationRequestsTableSQL);
    console.log('Registration requests table created successfully!');
    
    process.exit(0);
  } catch (err) {
    console.error('Error creating registration requests table:', err);
    process.exit(1);
  }
}

createTable(); 