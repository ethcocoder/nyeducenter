const bcrypt = require('bcryptjs');

async function createHashes() {
  const passwords = {
    'admin123': 'Admin password',
    'teacher123': 'Teacher password',
    'student123': 'Student password'
  };

  console.log('Generating bcrypt hashes for seed users...');
  
  for (const [password, label] of Object.entries(passwords)) {
    const salt = await bcrypt.genSalt(10);
    const hash = await bcrypt.hash(password, salt);
    console.log(`${label}: '${hash}',  // ${password}`);
  }
}

createHashes(); 