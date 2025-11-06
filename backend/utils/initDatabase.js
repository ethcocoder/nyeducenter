const fs = require('fs');
const path = require('path');
const db = require('../config/db.config');

/**
 * Initialize the database by running the schema SQL script
 */
const initDatabase = async () => {
  try {
    const schemaPath = path.join(__dirname, '../database/schema.sql');
    
    if (!fs.existsSync(schemaPath)) {
      console.error(`Schema file not found at ${schemaPath}`);
      return false;
    }
    
    const schemaSql = fs.readFileSync(schemaPath, 'utf8');
    
    // Split SQL statements by semicolon
    const statements = schemaSql
      .split(';')
      .filter(stmt => stmt.trim() !== '');
    
    console.log(`Initializing database with ${statements.length} SQL statements...`);
    
    // Execute each statement
    for (const statement of statements) {
      try {
        await db.query(statement + ';');
      } catch (error) {
        console.error(`Error executing statement: ${statement.substring(0, 100)}...`);
        console.error(error.message);
        // Continue with other statements even if one fails
      }
    }
    
    console.log('Database initialized successfully');
    return true;
  } catch (error) {
    console.error('Error initializing database:', error.message);
    return false;
  }
};

/**
 * Migrate data from JSON files to MySQL database
 */
const migrateJsonToMysql = async () => {
  try {
    const dataDir = path.join(__dirname, '../data');
    const collections = [
      { file: 'users.json', table: 'users' },
      { file: 'courses.json', table: 'courses' },
      { file: 'assignments.json', table: 'assignments' },
      { file: 'quizzes.json', table: 'quizzes' },
      { file: 'announcements.json', table: 'announcements' }
    ];
    
    if (!fs.existsSync(dataDir)) {
      console.log('No data directory found to migrate from');
      return false;
    }
    
    // Check if all required seed files exist
    for (const collection of collections) {
      const jsonPath = path.join(dataDir, collection.file);
      if (!fs.existsSync(jsonPath)) {
        console.log(`${collection.file} not found, skipping migration for ${collection.table}`);
        continue;
      }
      
      // Read JSON data
      const jsonData = JSON.parse(fs.readFileSync(jsonPath, 'utf8'));
      console.log(`Migrating ${jsonData.length} records from ${collection.file} to ${collection.table}...`);
      
      if (jsonData.length === 0) {
        console.log(`No data to migrate for ${collection.table}`);
        continue;
      }
      
      // Insert data into MySQL table
      for (const item of jsonData) {
        const keys = Object.keys(item);
        const values = Object.values(item);
        const placeholders = Array(keys.length).fill('?').join(', ');
        
        try {
          const query = `INSERT INTO ${collection.table} (${keys.join(', ')}) VALUES (${placeholders})`;
          await db.query(query, values);
        } catch (error) {
          console.error(`Error inserting item into ${collection.table}:`, error.message);
          console.error('Item:', JSON.stringify(item).substring(0, 100) + '...');
          // Continue with other items even if one fails
        }
      }
      
      console.log(`Migration complete for ${collection.table}`);
    }
    
    console.log('Data migration from JSON to MySQL completed successfully');
    return true;
  } catch (error) {
    console.error('Error migrating data:', error.message);
    return false;
  }
};

// If this file is run directly from the command line
if (require.main === module) {
  (async () => {
    try {
      // Test database connection
      let connected = false;
      try {
        const connection = await db.getConnection();
        console.log('MySQL database connected successfully');
        connection.release();
        connected = true;
      } catch (error) {
        console.error('Database connection failed:', error.message);
        connected = false;
      }
      if (!connected) {
        console.error('Could not connect to MySQL database. Please make sure XAMPP is running and MySQL service is started.');
        process.exit(1);
      }
      
      console.log('Database connected successfully.');
      
      console.log('Initializing database schema...');
      await initDatabase();
      
      const shouldMigrate = process.env.MIGRATE_JSON_TO_MYSQL === 'true';
      if (shouldMigrate) {
        console.log('Migrating JSON data to MySQL...');
        await migrateJsonToMysql();
      }
      
      console.log('Database setup completed successfully.');
      process.exit(0);
    } catch (error) {
      console.error('Error:', error.message);
      process.exit(1);
    }
  })();
}

module.exports = {
  initDatabase,
  migrateJsonToMysql
}; 