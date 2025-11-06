const fs = require('fs');
const path = require('path');
const bcrypt = require('bcryptjs');
const { v4: uuidv4 } = require('uuid');

const dataDir = path.join(__dirname, '..', 'data');
const usersFilePath = path.join(dataDir, 'users.json');

// Check if the data directory exists
if (!fs.existsSync(dataDir)) {
  fs.mkdirSync(dataDir, { recursive: true });
}

// Check if users.json exists, create it if it doesn't
if (!fs.existsSync(usersFilePath)) {
  fs.writeFileSync(usersFilePath, JSON.stringify([]), 'utf8');
}

async function createAdminUser() {
  try {
    const email = 'natnaeldaniel8@gmail.com'; // Should match admin-credentials.md
    const password = 'password123'; // Changed to match documentation
    
    // Read existing users
    const usersData = fs.readFileSync(usersFilePath, 'utf8');
    const users = JSON.parse(usersData);
    
    // Check if the email already exists
    const existingUser = users.find(user => user.email === email);
    if (existingUser) {
      if (existingUser.role === 'admin') {
        console.log(`Admin user with email ${email} already exists`);
        return;
      } else {
        console.log(`User with email ${email} exists but is not an admin. Updating role to admin...`);
        existingUser.role = 'admin';
        existingUser.updatedAt = new Date().toISOString();
        fs.writeFileSync(usersFilePath, JSON.stringify(users, null, 2), 'utf8');
        console.log(`User ${email} has been updated to admin role`);
        return;
      }
    }
    
    // Hash password
    const salt = await bcrypt.genSalt(10);
    const hashedPassword = await bcrypt.hash(password, salt);
    
    // Create new admin user
    const newAdmin = {
      firstName: 'Admin',
      lastName: 'User',
      email,
      password: hashedPassword,
      role: 'admin',
      profilePicture: 'https://i.pravatar.cc/150?img=5',
      preferredLanguage: 'en',
      id: uuidv4(),
      createdAt: new Date().toISOString(),
      updatedAt: new Date().toISOString()
    };
    
    // Add the new admin to users
    users.push(newAdmin);
    
    // Save updated users
    fs.writeFileSync(usersFilePath, JSON.stringify(users, null, 2), 'utf8');
    
    console.log(`Admin user created successfully with email: ${email}`);
    console.log('Default password is: admin123');
    console.log('Please change this password after first login for security');
  } catch (error) {
    console.error('Error creating admin user:', error);
  }
}

// Execute the function
createAdminUser();