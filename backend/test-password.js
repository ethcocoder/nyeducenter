const bcrypt = require('bcryptjs');
const { pool } = require('./config/db.config');

async function testPassword() {
  try {
    console.log('Testing login for seeded users...');
    
    // Fetch admin user from database
    const [rows] = await pool.query(
      'SELECT id, email, password, first_name, last_name FROM users WHERE email = ?', 
      ['admin@example.com']
    );
    
    if (rows.length === 0) {
      console.log('Admin user not found in database');
      return;
    }
    
    const user = rows[0];
    console.log('Found user:', {
      id: user.id,
      email: user.email,
      first_name: user.first_name,
      last_name: user.last_name
    });
    
    // Test the password from seed-auth.sql
    const testPassword = 'admin123';
    const hashedPassword = user.password;
    
    console.log('Stored hashed password:', hashedPassword);
    
    // Test if password matches
    const isMatch = await bcrypt.compare(testPassword, hashedPassword);
    console.log(`Password "${testPassword}" matches:`, isMatch);
    
    // If it doesn't match, create a proper hash for reference
    if (!isMatch) {
      const salt = await bcrypt.genSalt(10);
      const correctHash = await bcrypt.hash(testPassword, salt);
      console.log('Correct hash should be something like:', correctHash);
    }
    
    process.exit(0);
  } catch (error) {
    console.error('Error testing password:', error);
    process.exit(1);
  }
}

testPassword(); 