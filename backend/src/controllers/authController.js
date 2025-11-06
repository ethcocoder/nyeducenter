const jwt = require('jsonwebtoken');
const bcrypt = require('bcrypt');
const fs = require('fs').promises;
const fsSync = require('fs'); // Add regular fs for synchronous operations
const path = require('path');
const { DB_DIR, USER_DB_DIR } = require('../config/database');
const { ActivityTypes, logActivity } = require('../utils/activityLogger');

// Add debugging for database paths
console.log('Database directories:');
console.log(`- DB_DIR: ${DB_DIR}`);
console.log(`- USER_DB_DIR: ${USER_DB_DIR}`);

// Helper function to get user file path by role and grade
const getUserGradeFilePath = (role, grade) => {
    let filePath;
    if (role === 'admin') {
        filePath = path.join(USER_DB_DIR, role, 'grades', 'grades.json');
    } else {
        filePath = path.join(USER_DB_DIR, role, `grade${grade}`, 'grades', 'grades.json');
    }
    console.log(`Looking for user in: ${filePath}`);
    return filePath;
};

// Helper function to ensure directory exists
const ensureDirectoryExists = async (dirPath) => {
    try {
        if (!fsSync.existsSync(dirPath)) {
            await fs.mkdir(dirPath, { recursive: true });
            console.log(`Created directory: ${dirPath}`);
        }
    } catch (error) {
        console.error(`Error creating directory ${dirPath}:`, error);
        throw error;
    }
};

// Helper function to read users from grade file
const readUsersByGrade = async (role, grade) => {
    try {
        // Get file path for this role and grade
        const filePath = getUserGradeFilePath(role, grade);
        
        // Ensure the directory exists
        const dirPath = path.dirname(filePath);
        await ensureDirectoryExists(dirPath);
        
        if (!fsSync.existsSync(filePath)) {
            console.log(`Grade file for ${role} grade ${grade} does not exist, creating empty array`);
            await fs.writeFile(filePath, JSON.stringify([], null, 2));
            return [];
        }
        
        const data = await fs.readFile(filePath, 'utf8');
        const users = JSON.parse(data);
        return Array.isArray(users) ? users : [];
    } catch (error) {
        console.error(`Error reading users for ${role} grade ${grade}:`, error);
        return [];
    }
};

// Helper function to write users to grade file
const writeUsersToGrade = async (role, grade, users) => {
    try {
        // Get file path for this role and grade
        const filePath = getUserGradeFilePath(role, grade);
        
        // Ensure the directory exists
        const dirPath = path.dirname(filePath);
        await ensureDirectoryExists(dirPath);
        
        await fs.writeFile(filePath, JSON.stringify(users, null, 2));
        console.log(`Successfully wrote users to ${role} grade ${grade}`);
        return true;
    } catch (error) {
        console.error(`Error writing users for ${role} grade ${grade}:`, error);
        return false;
    }
};

// Helper function to find user by username or email across all roles and grades
const findUserByUsernameOrEmail = async (usernameOrEmail) => {
    const isEmail = usernameOrEmail.includes('@');
    
    // Check all roles
    for (const role of ['admin', 'teacher', 'student']) {
        // For admin, only one grade file
        if (role === 'admin') {
            const users = await readUsersByGrade(role, '');
            let user;
            
            if (isEmail) {
                user = users.find(u => u.email && u.email.toLowerCase() === usernameOrEmail.toLowerCase());
            } else {
                user = users.find(u => u.username.toLowerCase() === usernameOrEmail.toLowerCase());
            }
            
            if (user) {
                return { user, role, grade: '' };
            }
        } else {
            // For teachers and students, check all grades
            for (const grade of ['9', '10', '11', '12']) {
                const users = await readUsersByGrade(role, grade);
                let user;
                
                if (isEmail) {
                    user = users.find(u => u.email && u.email.toLowerCase() === usernameOrEmail.toLowerCase());
                } else {
                    user = users.find(u => u.username.toLowerCase() === usernameOrEmail.toLowerCase());
                }
                
                if (user) {
                    return { user, role, grade };
                }
            }
        }
    }
    return null;
};

// Maintain the old function for backward compatibility
const findUserByUsername = async (username) => {
    return findUserByUsernameOrEmail(username);
};

const authController = {
    async login(req, res) {
        const { username, password } = req.body;
        
        try {
            console.log(`================================`);
            console.log(`Login attempt for username/email: ${username}`);
            console.log(`Using password: [HIDDEN]`);
            
            // Check if input looks like an email
            const isEmail = username.includes('@');
            console.log(`Input appears to be ${isEmail ? 'an email' : 'a username'}`);
            
            // Find user across all roles and grades using the enhanced function
            console.log(`Searching for user...`);
            const userInfo = await findUserByUsernameOrEmail(username);
            
            if (!userInfo || !userInfo.user) {
                console.log(`User with ${isEmail ? 'email' : 'username'} "${username}" not found in any role or grade`);
                return res.status(401).json({ error: 'Invalid credentials' });
            }
            
            const { user, role, grade } = userInfo;
            console.log(`User found: ${user.username} (${user.role}, grade ${user.grade})`);
            console.log(`User email: ${user.email || 'not set'}`);
            
            // Validate password
            console.log(`Comparing password...`);
            const passwordMatch = await bcrypt.compare(password, user.password);
            console.log(`Password match: ${passwordMatch}`);
            
            if (!passwordMatch) {
                // Log failed login attempt
                await logActivity({
                    type: ActivityTypes.FAILED_LOGIN,
                    userId: user.id,
                    userRole: user.role,
                    metadata: {
                        reason: 'Invalid password',
                        ip: req.ip || req.headers['x-forwarded-for'] || null
                    }
                });
                
                console.log(`Invalid password for user ${username}`);
                return res.status(401).json({ error: 'Invalid credentials' });
            }

            // Generate token with user info including role
            const token = jwt.sign({ 
                id: user.id,
                username: user.username,
                role: user.role,
                grade: user.grade
            }, process.env.JWT_SECRET, {
                expiresIn: process.env.JWT_EXPIRES_IN || '24h'
            });
            
            // Log successful login
            await logActivity({
                type: ActivityTypes.USER_LOGIN,
                userId: user.id,
                userRole: user.role,
                ip: req.ip || req.headers['x-forwarded-for'] || null
            });
            
            console.log(`User ${username} logged in successfully`);
            
            res.json({ 
                token,
                user: {
                    id: user.id,
                    username: user.username,
                    role: user.role,
                    grade: user.grade,
                    fullName: user.fullName
                }
            });
        } catch (error) {
            console.error('Login error:', error);
            res.status(500).json({ error: 'Server error' });
        }
    },

    async register(req, res) {
        const { username, password, role, grade, fullName, email } = req.body;
        
        console.log('Registration attempt:', { username, role, grade, fullName, email }); 
        
        try {
            // Validate input
            if (!username || !password) {
                console.log('Registration failed: Missing username or password');
                return res.status(400).json({ error: 'Username and password are required' });
            }
            
            // Set default role to student if not provided
            const userRole = role || 'student';
            
            // Set grade (default to 9 for teachers and students)
            const userGrade = grade || (userRole === 'student' || userRole === 'teacher' ? '9' : null);
            
            if (!userGrade && userRole !== 'admin') {
                console.log('Registration failed: Grade is required for teachers and students');
                return res.status(400).json({ error: 'Grade is required for teachers and students' });
            }
            
            console.log(`Registering user with role: ${userRole}, grade: ${userGrade}`);
            
            // Check if username already exists
            const existingUser = await findUserByUsername(username);
            if (existingUser) {
                console.log('Registration failed: Username already exists');
                return res.status(400).json({ error: 'Username already exists' });
            }
            
            // For admin role, only allow one admin account in total
            if (userRole === 'admin') {
                const adminUsers = await readUsersByGrade('admin', '');
                if (adminUsers.length > 0) {
                    console.log('Registration failed: Admin account already exists');
                    return res.status(403).json({ error: 'Admin account already exists. New admin accounts can only be created by existing admins.' });
                }
            }
            
            console.log('Hashing password');
            const hashedPassword = await bcrypt.hash(password, 10);
            
            // Create user object with ID and additional fields
            const newUser = { 
                id: Date.now().toString(), // Simple ID generation
                username, 
                password: hashedPassword,
                role: userRole,
                grade: userGrade, 
                fullName: fullName || username,
                email: email || null,
                createdAt: new Date().toISOString(),
                updatedAt: new Date().toISOString()
            };
            
            console.log(`Adding new ${userRole} user to grade ${userGrade}`);
            console.log('User object:', { ...newUser, password: '[HIDDEN]' });
            
            // Read existing users in this role and grade
            const users = await readUsersByGrade(userRole, userGrade);
            
            // Add new user
            users.push(newUser);
            
            // Write back to file
            const success = await writeUsersToGrade(userRole, userGrade, users);
            if (!success) {
                console.error(`Failed to write user to ${userRole} grade ${userGrade}`);
                return res.status(500).json({ error: 'Failed to create user account' });
            }
            
            // Generate token for immediate login
            console.log('Generating token');
            const token = jwt.sign({ 
                id: newUser.id,
                username: newUser.username,
                role: newUser.role,
                grade: newUser.grade
            }, process.env.JWT_SECRET, {
                expiresIn: process.env.JWT_EXPIRES_IN || '24h'
            });
            
            console.log('Logging activity');
            // Log user creation
            await logActivity({
                type: ActivityTypes.USER_CREATED,
                userId: newUser.id,
                userRole: newUser.role,
                metadata: {
                    createdBy: 'self-registration'
                },
                ip: req.ip || req.headers['x-forwarded-for'] || null
            });
            
            // Return user info (excluding password)
            const { password: _, ...userWithoutPassword } = newUser;
            
            console.log('Registration successful');
            res.status(201).json({ 
                message: 'User registered successfully',
                token,
                user: userWithoutPassword
            });
        } catch (error) {
            console.error('Registration error details:', error.message);
            console.error('Error stack:', error.stack);
            res.status(500).json({ error: 'Server error', details: error.message });
        }
    },
    
    async logout(req, res) {
        try {
            // JWT tokens are stateless, so we cannot invalidate them server-side
            // Client should discard the token
            // However, we can log the logout activity if user is authenticated

            if (req.user) {
                await logActivity({
                    type: ActivityTypes.USER_LOGOUT,
                    userId: req.user.id,
                    userRole: req.user.role,
                    ip: req.ip || req.headers['x-forwarded-for'] || null
                });
            }
            
            res.json({ message: 'Logged out successfully' });
        } catch (error) {
            console.error('Logout error:', error);
            res.status(500).json({ error: 'Server error' });
        }
    },
    
    async verifyToken(req, res) {
        // This endpoint can be used by the client to verify if a token is valid
        // The authenticateToken middleware will have already verified the token
        // and attached the user to req.user
        
        try {
            if (!req.user) {
                return res.status(401).json({ error: 'Invalid token' });
            }
            
            res.json({ 
                valid: true,
                user: {
                    id: req.user.id,
                    username: req.user.username,
                    role: req.user.role,
                    grade: req.user.grade
                }
            });
        } catch (error) {
            console.error('Token verification error:', error);
            res.status(500).json({ error: 'Server error' });
        }
    }
};

module.exports = authController;