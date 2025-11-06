const express = require('express');
const router = express.Router();
const fs = require('fs').promises;
const fsSync = require('fs');
const path = require('path');
const { DB_DIR, USER_DB_DIR } = require('../config/database');
const authController = require('../controllers/authController');

// Debug route to list all users in the system
router.get('/users', async (req, res) => {
    try {
        const users = [];
        
        // Loop through roles
        for (const role of ['admin', 'teacher', 'student']) {
            let rolePath = path.join(USER_DB_DIR, role);
            
            if (!fsSync.existsSync(rolePath)) {
                console.log(`Role directory does not exist: ${rolePath}`);
                continue;
            }
            
            if (role === 'admin') {
                // For admin, check directly in the admin folder
                try {
                    const gradesPath = path.join(rolePath, 'grades', 'grades.json');
                    if (fsSync.existsSync(gradesPath)) {
                        const data = await fs.readFile(gradesPath, 'utf8');
                        const adminUsers = JSON.parse(data);
                        if (Array.isArray(adminUsers)) {
                            adminUsers.forEach(user => {
                                const { password, ...userWithoutPassword } = user;
                                users.push({
                                    ...userWithoutPassword,
                                    role: 'admin',
                                    grade: '',
                                    filePath: gradesPath
                                });
                            });
                        }
                    }
                } catch (error) {
                    console.error(`Error reading admin users: ${error.message}`);
                }
            } else {
                // For teachers and students, check grade directories
                const gradeDirectories = fsSync.readdirSync(rolePath)
                    .filter(dir => dir.startsWith('grade') && fsSync.statSync(path.join(rolePath, dir)).isDirectory());
                
                for (const gradeDir of gradeDirectories) {
                    try {
                        const gradesPath = path.join(rolePath, gradeDir, 'grades', 'grades.json');
                        if (fsSync.existsSync(gradesPath)) {
                            const data = await fs.readFile(gradesPath, 'utf8');
                            const gradeUsers = JSON.parse(data);
                            if (Array.isArray(gradeUsers)) {
                                gradeUsers.forEach(user => {
                                    const { password, ...userWithoutPassword } = user;
                                    users.push({
                                        ...userWithoutPassword,
                                        role: role,
                                        grade: gradeDir.replace('grade', '').replace(/[ts]$/, ''), // Remove 't' or 's' suffix
                                        filePath: gradesPath
                                    });
                                });
                            }
                        }
                    } catch (error) {
                        console.error(`Error reading ${role} users in ${gradeDir}: ${error.message}`);
                    }
                }
            }
        }
        
        // Return the list of users
        res.json({
            total: users.length,
            users: users
        });
    } catch (error) {
        console.error('Error listing users:', error);
        res.status(500).json({ error: 'Server error', details: error.message });
    }
});

// Debug login route with more information
router.post('/login', async (req, res) => {
    try {
        console.log('Debug login endpoint called');
        console.log('Login payload:', req.body);
        
        // Use the existing login handler
        await authController.login(req, res);
    } catch (error) {
        console.error('Debug login error:', error);
        res.status(500).json({ error: 'Server error', details: error.message });
    }
});

// Debug registration route
router.post('/register', async (req, res) => {
    try {
        console.log('Debug register endpoint called');
        console.log('Registration payload:', {...req.body, password: '[HIDDEN]'});
        
        // Use the existing register handler
        await authController.register(req, res);
    } catch (error) {
        console.error('Debug registration error:', error);
        res.status(500).json({ error: 'Server error', details: error.message });
    }
});

// Special direct login route that looks in known teacher directories
router.post('/direct-login', async (req, res) => {
    try {
        const { username, password } = req.body;
        console.log(`Direct login attempt for: ${username}`);
        
        // Look directly in the grade9 teacher file where we know users exist
        const teacherGrade9Path = path.join(USER_DB_DIR, 'teacher', 'grade9', 'grades', 'grades.json');
        console.log(`Looking in file: ${teacherGrade9Path}`);
        
        if (!fsSync.existsSync(teacherGrade9Path)) {
            console.error(`File not found: ${teacherGrade9Path}`);
            return res.status(404).json({ error: 'User database not found' });
        }
        
        const data = await fs.readFile(teacherGrade9Path, 'utf8');
        const users = JSON.parse(data);
        
        // Find user by username
        const user = users.find(u => 
            u.username === username || 
            (u.email && u.email.toLowerCase() === username.toLowerCase())
        );
        
        if (!user) {
            console.log(`User not found in teacher grade 9`);
            return res.status(401).json({ error: 'Invalid credentials' });
        }
        
        console.log(`User found: ${user.username}, email: ${user.email || 'none'}`);
        
        // Validate password with bcrypt
        const bcrypt = require('bcrypt');
        const passwordMatch = await bcrypt.compare(password, user.password);
        console.log(`Password match: ${passwordMatch}`);
        
        if (!passwordMatch) {
            console.log(`Invalid password for user ${username}`);
            return res.status(401).json({ error: 'Invalid credentials' });
        }
        
        // Generate token
        const jwt = require('jsonwebtoken');
        const token = jwt.sign({ 
            id: user.id,
            username: user.username,
            role: user.role,
            grade: user.grade
        }, process.env.JWT_SECRET || 'your-secret-key', {
            expiresIn: '24h'
        });
        
        console.log(`Login successful for ${username}`);
        
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
        console.error('Direct login error:', error);
        res.status(500).json({ error: 'Server error', details: error.message });
    }
});

// Emergency bypass login (for testing only)
router.post('/bypass-login', async (req, res) => {
    try {
        const { username } = req.body;
        console.log(`BYPASS LOGIN attempt for: ${username}`);
        
        // Look directly in the grade9 teacher file
        const teacherGrade9Path = path.join(USER_DB_DIR, 'teacher', 'grade9', 'grades', 'grades.json');
        console.log(`Looking in file: ${teacherGrade9Path}`);
        
        if (!fsSync.existsSync(teacherGrade9Path)) {
            console.error(`File not found: ${teacherGrade9Path}`);
            return res.status(404).json({ error: 'User database not found' });
        }
        
        const data = await fs.readFile(teacherGrade9Path, 'utf8');
        let users = JSON.parse(data);
        
        // Find the first user if no username specified, or match by username/email
        let user;
        if (!username && users.length > 0) {
            // Just get the first user if no username provided
            user = users[0];
            console.log(`No username provided, using first user: ${user.username}`);
        } else {
            // Find user by username or email
            user = users.find(u => 
                u.username === username || 
                (u.email && u.email.toLowerCase() === username.toLowerCase())
            );
        }
        
        if (!user) {
            // If no specific user found, take the first one
            if (users.length > 0) {
                user = users[0];
                console.log(`User not found, using first user: ${user.username}`);
            } else {
                console.log(`No users found in teacher grade 9`);
                return res.status(401).json({ error: 'No users found' });
            }
        }
        
        console.log(`Bypassing login for user: ${user.username}, email: ${user.email || 'none'}`);
        
        // Generate token WITHOUT password verification
        const jwt = require('jsonwebtoken');
        const token = jwt.sign({ 
            id: user.id,
            username: user.username,
            role: user.role,
            grade: user.grade
        }, process.env.JWT_SECRET || 'your-secret-key', {
            expiresIn: '24h'
        });
        
        console.log(`Bypass login successful for ${user.username}`);
        
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
        console.error('Bypass login error:', error);
        res.status(500).json({ error: 'Server error', details: error.message });
    }
});

module.exports = router; 