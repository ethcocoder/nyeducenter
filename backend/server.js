require('dotenv').config();
const express = require('express');
const cors = require('cors');
const { initializeDatabase } = require('./src/config/database');
const fs = require('fs').promises;
const fsSync = require('fs'); // Add regular fs for synchronous operations
const path = require('path');
const bcrypt = require('bcrypt');
const jwt = require('jsonwebtoken');

const app = express();
const PORT = process.env.PORT || 3000;

// Delete legacy users.json files if they exist
const centralUsersFile = path.join(__dirname, 'database', 'users.json');
const roleUserFiles = [
  path.join(__dirname, 'database', 'user', 'admin', 'users.json'),
  path.join(__dirname, 'database', 'user', 'teacher', 'users.json'),
  path.join(__dirname, 'database', 'user', 'student', 'users.json')
];

// Delete central users.json if it exists
if (fsSync.existsSync(centralUsersFile)) {
  console.log('Removing legacy central users.json file...');
  try {
    fsSync.unlinkSync(centralUsersFile);
    console.log('Removed legacy users.json successfully');
  } catch (error) {
    console.error('Failed to remove legacy users.json:', error);
  }
}

// Delete role-level users.json files if they exist
for (const file of roleUserFiles) {
  if (fsSync.existsSync(file)) {
    console.log(`Removing legacy role users file: ${file}`);
    try {
      fsSync.unlinkSync(file);
      console.log(`Removed ${file} successfully`);
    } catch (error) {
      console.error(`Failed to remove ${file}:`, error);
    }
  }
}

// Middleware
app.use(cors({
    origin: '*', // Allow any origin during development
    methods: ['GET', 'POST', 'PUT', 'DELETE'],
    allowedHeaders: ['Content-Type', 'Authorization']
}));
app.use(express.json());

// Routes
const authRoutes = require('./src/routes/authRoutes');
const tableRoutes = require('./src/routes/tableRoutes');
const adminRoutes = require('./src/routes/adminRoutes');
const databaseRoutes = require('./src/routes/databaseRoutes');
const gradeRoutes = require('./src/routes/gradeRoutes');

// Log available routes
console.log('Registering routes:');
console.log('- /api/auth');
console.log('- /api/tables');
console.log('- /api/admin');
console.log('- /api/database');
console.log('- /api/grades');

app.use('/api/auth', authRoutes);
app.use('/api/tables', tableRoutes);
app.use('/api/admin', adminRoutes);
app.use('/api/database', databaseRoutes);
app.use('/api/grades', gradeRoutes);

// Root route for API testing
app.get('/', (req, res) => {
  res.json({ message: 'API is running' });
});

// Direct registration endpoint for debugging
app.post('/debug/register', async (req, res) => {
  try {
    const { username, password, fullName, email, role, grade } = req.body;
    
    console.log('Debug registration attempt:', { username, fullName, email, role, grade });
    
    if (!username || !password) {
      return res.status(400).json({ error: 'Username and password are required' });
    }
    
    // Set default role
    const userRole = role || 'student';
    
    // Set grade properly (don't default to null for teachers)
    const userGrade = grade || (userRole === 'student' || userRole === 'teacher' ? '9' : null);
    
    console.log(`Setting user role: ${userRole}, grade: ${userGrade}`);
    
    // Check if username exists in any grade file
    let usernameExists = false;
    
    for (const checkRole of ['admin', 'teacher', 'student']) {
      if (usernameExists) break;
      
      if (checkRole === 'admin') {
        // Check admin grades file
        const adminGradesFile = path.join(__dirname, 'database', 'user', 'admin', 'grades', 'grades.json');
        if (fsSync.existsSync(adminGradesFile)) {
          try {
            const data = await fs.readFile(adminGradesFile, 'utf8');
            const users = JSON.parse(data);
            if (Array.isArray(users) && users.some(u => u.username === username)) {
              usernameExists = true;
              break;
            }
          } catch (error) {
            console.error('Error checking admin grades file:', error);
          }
        }
      } else {
        // Check grade-specific files for teachers and students
        for (const gradeNum of ['9', '10', '11', '12']) {
          const gradeFile = path.join(__dirname, 'database', 'user', checkRole, `grade${gradeNum}`, 'grades', 'grades.json');
          if (fsSync.existsSync(gradeFile)) {
            try {
              const data = await fs.readFile(gradeFile, 'utf8');
              const users = JSON.parse(data);
              if (Array.isArray(users) && users.some(u => u.username === username)) {
                usernameExists = true;
                break;
              }
            } catch (error) {
              console.error(`Error checking ${checkRole} grade ${gradeNum} file:`, error);
            }
          }
        }
      }
    }
    
    if (usernameExists) {
      return res.status(400).json({ error: 'Username already exists' });
    }
    
    // Hash password
    const hashedPassword = await bcrypt.hash(password, 10);
    
    // Create user object
    const newUser = {
      id: Date.now().toString(),
      username,
      password: hashedPassword,
      fullName: fullName || username,
      email: email || null,
      role: userRole,
      grade: userGrade,
      createdAt: new Date().toISOString(),
      updatedAt: new Date().toISOString()
    };
    
    console.log('Created user object:', { ...newUser, password: '[HIDDEN]' });
    
    // Determine file path based on role and grade
    let filePath;
    if (userRole === 'admin') {
      filePath = path.join(__dirname, 'database', 'user', 'admin', 'grades', 'grades.json');
    } else {
      filePath = path.join(__dirname, 'database', 'user', userRole, `grade${userGrade}`, 'grades', 'grades.json');
    }
    
    // Ensure directory exists
    const dirPath = path.dirname(filePath);
    if (!fsSync.existsSync(dirPath)) {
      await fs.mkdir(dirPath, { recursive: true });
    }
    
    // Read existing users from grade file
    let users = [];
    if (fsSync.existsSync(filePath)) {
      try {
        const data = await fs.readFile(filePath, 'utf8');
        users = JSON.parse(data);
        if (!Array.isArray(users)) {
          users = [];
        }
      } catch (error) {
        console.error(`Error reading file ${filePath}:`, error);
      }
    }
    
    // Add new user and write back to file
    users.push(newUser);
    await fs.writeFile(filePath, JSON.stringify(users, null, 2));
    console.log(`User added to ${filePath} successfully`);
    
    // Create JWT token
    const token = jwt.sign({
      id: newUser.id,
      username: newUser.username,
      role: newUser.role,
      grade: newUser.grade
    }, process.env.JWT_SECRET || 'your-super-secret-key-change-this-in-production', {
      expiresIn: '24h'
    });
    
    // Remove password from response
    const { password: _, ...userWithoutPassword } = newUser;
    
    res.status(201).json({
      message: 'User registered successfully',
      token,
      user: userWithoutPassword
    });
  } catch (error) {
    console.error('Debug registration error:', error);
    res.status(500).json({ error: 'Server error', details: error.message });
  }
});

// Direct login endpoint for debugging
app.post('/debug/login', async (req, res) => {
  try {
    const { username, password } = req.body;
    
    console.log('Debug login attempt:', { username });
    
    if (!username || !password) {
      return res.status(400).json({ error: 'Username and password are required' });
    }
    
    // Find user in any grade file across all roles
    let user = null;
    
    // Check all roles
    for (const role of ['admin', 'teacher', 'student']) {
      if (user) break;
      
      if (role === 'admin') {
        // Check admin grades file
        const adminGradesFile = path.join(__dirname, 'database', 'user', 'admin', 'grades', 'grades.json');
        if (fsSync.existsSync(adminGradesFile)) {
          try {
            const data = await fs.readFile(adminGradesFile, 'utf8');
            const users = JSON.parse(data);
            if (Array.isArray(users)) {
              const foundUser = users.find(u => u.username === username);
              if (foundUser) {
                user = foundUser;
                break;
              }
            }
          } catch (error) {
            console.error('Error checking admin grades file:', error);
          }
        }
      } else {
        // Check grade-specific files for teachers and students
        for (const grade of ['9', '10', '11', '12']) {
          if (user) break;
          
          const gradeFile = path.join(__dirname, 'database', 'user', role, `grade${grade}`, 'grades', 'grades.json');
          if (fsSync.existsSync(gradeFile)) {
            try {
              const data = await fs.readFile(gradeFile, 'utf8');
              const users = JSON.parse(data);
              if (Array.isArray(users)) {
                const foundUser = users.find(u => u.username === username);
                if (foundUser) {
                  user = foundUser;
                  break;
                }
              }
            } catch (error) {
              console.error(`Error checking ${role} grade ${grade} file:`, error);
            }
          }
        }
      }
    }
    
    if (!user) {
      return res.status(401).json({ error: 'Invalid credentials: User not found' });
    }
    
    // Compare password
    const isMatch = await bcrypt.compare(password, user.password);
    
    if (!isMatch) {
      return res.status(401).json({ error: 'Invalid credentials: Password incorrect' });
    }
    
    // Create JWT token
    const token = jwt.sign({
      id: user.id,
      username: user.username,
      role: user.role,
      grade: user.grade
    }, process.env.JWT_SECRET || 'your-super-secret-key-change-this-in-production', {
      expiresIn: '24h'
    });
    
    // Remove password from response
    const { password: _, ...userWithoutPassword } = user;
    
    res.json({
      token,
      user: userWithoutPassword
    });
  } catch (error) {
    console.error('Debug login error:', error);
    res.status(500).json({ error: 'Server error', details: error.message });
  }
});

// Error handling middleware
app.use((err, req, res, next) => {
    console.error('Error:', err.stack);
    res.status(500).json({ error: 'Something went wrong!' });
});

// Initialize database and start server
initializeDatabase().then(() => {
    app.listen(PORT, () => {
        console.log(`Server running on port ${PORT}`);
        console.log(`API root: http://localhost:${PORT}/`);
    });
}).catch(err => {
    console.error('Failed to initialize database:', err);
    process.exit(1);
}); 