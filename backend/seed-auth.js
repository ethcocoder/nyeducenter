const fs = require('fs');
const path = require('path');
const { pool } = require('./config/db.config');

async function seedAuthData() {
  try {
    console.log('Seeding authentication data...');
    
    // Read the SQL file
    const seedFile = path.join(__dirname, './database/seed-auth.sql');
    const sql = fs.readFileSync(seedFile, 'utf8');
    
    // Split SQL statements by semicolon
    const statements = sql
      .split(';')
      .filter(stmt => stmt.trim() !== '');
    
    // Execute each statement
    for (const statement of statements) {
      try {
        await pool.query(statement);
        console.log('Executed statement successfully');
      } catch (error) {
        console.error(`Error executing statement: ${statement.substring(0, 100)}...`);
        console.error(error.message);
      }
    }
    
    console.log('Authentication data seeded successfully');
    process.exit(0);
  } catch (error) {
    console.error('Error seeding auth data:', error);
    process.exit(1);
  }
}

seedAuthData(); 