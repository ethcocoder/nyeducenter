const { spawn, exec } = require('child_process');
const os = require('os');
const fs = require('fs');
const path = require('path');

// Make the shell script executable on Unix-like systems
if (os.platform() !== 'win32') {
  try {
    fs.chmodSync(path.join(__dirname, 'setup.sh'), '755');
    console.log('Made setup.sh executable');
  } catch (error) {
    console.error('Failed to make setup.sh executable:', error);
  }
}

console.log('E-Learning System Setup');
console.log('=======================');
console.log('Detected OS:', os.platform());

// Function to run a command and pipe output
function runCommand(command, args) {
  return new Promise((resolve, reject) => {
    console.log(`Running: ${command} ${args.join(' ')}`);
    
    const childProcess = spawn(command, args, {
      stdio: 'inherit',
      shell: true
    });
    
    childProcess.on('close', (code) => {
      if (code === 0) {
        resolve();
      } else {
        reject(new Error(`Command failed with exit code ${code}`));
      }
    });
    
    childProcess.on('error', (error) => {
      reject(error);
    });
  });
}

async function runSetup() {
  try {
    // Check if XAMPP is running
    console.log('Checking if XAMPP services are running...');
    
    let isRunning = false;
    
    if (os.platform() === 'win32') {
      // Run the Windows batch file
      await runCommand('setup.bat', []);
    } else {
      // Run the shell script for Unix-like systems
      await runCommand('./setup.sh', []);
    }
    
    console.log('Setup completed successfully');
    
  } catch (error) {
    console.error('Setup failed:', error.message);
    process.exit(1);
  }
}

runSetup(); 