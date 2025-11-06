const fs = require('fs');
const path = require('path');
const bcrypt = require('bcryptjs');

const dataDir = path.join(__dirname, '..', 'data');
const usersFilePath = path.join(dataDir, 'users.json');

async function resetPassword(email, newPassword) {
  try {
    console.log(`Attempting to reset password for: ${email}`);
    
    // Read existing users
    const usersData = fs.readFileSync(usersFilePath, 'utf8');
    const users = JSON.parse(usersData);
    
    // Find the user by email
    const userIndex = users.findIndex(user => user.email.toLowerCase() === email.toLowerCase());
    
    if (userIndex === -1) {
      console.error(`User with email ${email} not found`);
      return false;
    }
    
    // Hash the new password
    const salt = await bcrypt.genSalt(10);
    const hashedPassword = await bcrypt.hash(newPassword, salt);
    
    // Update the user's password
    users[userIndex].password = hashedPassword;
    users[userIndex].updatedAt = new Date().toISOString();
    
    // Save the updated users
    fs.writeFileSync(usersFilePath, JSON.stringify(users, null, 2), 'utf8');
    
    console.log(`Password reset successful for ${email}`);
    return true;
  } catch (error) {
    console.error('Error resetting password:', error);
    return false;
  }
}

// Get email and new password from command line arguments
const args = process.argv.slice(2);
const email = args[0] || 'natnaeldaniel8@gmail.com';
const newPassword = args[1] || 'password123';

// Execute the password reset
resetPassword(email, newPassword)
  .then(success => {
    if (success) {
      console.log(`\nYou can now log in with:`);
      console.log(`Email: ${email}`);
      console.log(`Password: ${newPassword}`);
    } else {
      console.log('\nPassword reset failed.');
    }
  }); 