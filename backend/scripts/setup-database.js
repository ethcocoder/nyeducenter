const { exec } = require('child_process');
const path = require('path');
const { pool } = require('../config/db.config');


// Function to run a script
function runScript(scriptPath) {
  return new Promise((resolve, reject) => {
    // Use quotes around the path to handle spaces
    const command = `node "${scriptPath}"`;
    exec(command, (error, stdout, stderr) => {
      if (error) {
        console.error(`Error executing ${scriptPath}:`, error);
        reject(error);
        return;
      }
      console.log(`Output from ${scriptPath}:`, stdout);
      resolve();
    });
  });
}

async function setupDatabase() {
  try {
    // Get the directory path
    const dirPath = path.dirname(__filename);
    
    // Run scripts in sequence
    console.log('Creating database tables...');
    await runScript(path.join(dirPath, 'createAdminAndAuthTables.js'));
    
    console.log('\nCreating registration requests table...');
    await runScript(path.join(dirPath, 'createRegistrationRequestsTable.js'));
    
    console.log('\nSeeding admin users...');
    await runScript(path.join(dirPath, 'seed-admin-users.js'));

    console.log('\nDatabase setup completed successfully!');
  } catch (error) {
    console.error('Database setup failed:', error);
    process.exit(1);
  }
}

// Export the setup function
module.exports = setupDatabase;

// If this file is run directly, execute setup
if (require.main === module) {
  setupDatabase();
} 