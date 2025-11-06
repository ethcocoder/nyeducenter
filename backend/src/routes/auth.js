const express = require('express');
const jwt = require('jsonwebtoken');
const { createRecord, findOneRecord, hashPassword, comparePassword } = require('../utils/database');
const db = require('../utils/db');

const router = express.Router();

/**
 * @route   POST /api/auth/register
 * @desc    Register new user
 * @access  Public
 */
router.post('/register', async (req, res) => {
    try {
        const { 
            username, 
            password, 
            firstName, 
            lastName, 
            email, 
            phone, 
            role,
            grade 
        } = req.body;

        // Validate required fields
        if (!username || !password || !firstName || !lastName || !email || !role) {
            return res.status(400).json({ 
                success: false, 
                message: 'Please provide all required fields' 
            });
        }

        // Validate role
        const validRoles = ['admin', 'teacher', 'student'];
        if (!validRoles.includes(role)) {
            return res.status(400).json({
                success: false,
                message: 'Role must be admin, teacher, or student'
            });
        }

        // Check if username already exists
        const existingUser = db.findUserByUsername(username);
        if (existingUser) {
            return res.status(400).json({ 
                success: false, 
                message: 'Username already taken' 
            });
        }

        // Create user
        const userData = {
            username,
            password,
            firstName,
            lastName,
            email,
            phone,
            role,
            grade: grade || null,
            isActive: true
        };

        const newUser = db.createUser(userData);

        // Generate JWT token
        const token = jwt.sign(
            { id: newUser.id, username: newUser.username, role: newUser.role },
            process.env.JWT_SECRET || 'your-secret-key',
            { expiresIn: '24h' }
        );

        res.status(201).json({
            success: true,
            message: 'User registered successfully',
            data: {
                ...newUser,
                token
            }
        });
    } catch (error) {
        console.error('Registration error:', error);
        res.status(500).json({ 
            success: false, 
            message: 'Registration failed', 
            error: error.message 
        });
    }
});

/**
 * @route   POST /api/auth/login
 * @desc    Login user
 * @access  Public
 */
router.post('/login', async (req, res) => {
    try {
        const { username, password } = req.body;

        // Validate required fields
        if (!username || !password) {
            return res.status(400).json({ 
                success: false, 
                message: 'Please provide username and password' 
            });
        }

        // Validate user
        const user = db.validateUser(username, password);
        if (!user) {
            return res.status(401).json({ 
                success: false, 
                message: 'Invalid credentials' 
            });
        }

        // Check if user is active
        if (user.isActive === false) {
            return res.status(403).json({
                success: false,
                message: 'Account is disabled. Please contact administrator.'
            });
        }

        // Generate JWT token
        const token = jwt.sign(
            { id: user.id, username: user.username, role: user.role },
            process.env.JWT_SECRET || 'your-secret-key',
            { expiresIn: '24h' }
        );

        res.json({
            success: true,
            message: 'Login successful',
            data: {
                ...user,
                token
            }
        });
    } catch (error) {
        console.error('Login error:', error);
        res.status(500).json({ 
            success: false, 
            message: 'Login failed', 
            error: error.message 
        });
    }
});

/**
 * @route   GET /api/auth/users
 * @desc    Get all users (admin only)
 * @access  Private/Admin
 */
router.get('/users', async (req, res) => {
    try {
        // This route should be protected by auth middleware
        // Check if requester is admin
        if (!req.user || req.user.role !== 'admin') {
            return res.status(403).json({
                success: false,
                message: 'Access denied. Admin only.'
            });
        }

        // Read all users
        const users = db.readUsers();

        // Remove passwords from response
        const usersWithoutPasswords = users.map(user => {
            const { password, ...userWithoutPassword } = user;
            return userWithoutPassword;
        });

        res.json({
            success: true,
            count: usersWithoutPasswords.length,
            data: usersWithoutPasswords
        });
    } catch (error) {
        console.error('Error fetching users:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to fetch users',
            error: error.message
        });
    }
});

/**
 * @route   POST /api/auth/users
 * @desc    Create new user (admin only)
 * @access  Private/Admin
 */
router.post('/users', async (req, res) => {
    try {
        // This route should be protected by auth middleware
        // Check if requester is admin
        if (!req.user || req.user.role !== 'admin') {
            return res.status(403).json({
                success: false,
                message: 'Access denied. Admin only.'
            });
        }

        const { 
            username, 
            password, 
            firstName, 
            lastName, 
            email, 
            phone, 
            role,
            grade,
            isActive
        } = req.body;

        // Validate required fields
        if (!username || !password || !firstName || !lastName || !email || !role) {
            return res.status(400).json({ 
                success: false, 
                message: 'Please provide all required fields' 
            });
        }

        // Validate role
        const validRoles = ['admin', 'teacher', 'student'];
        if (!validRoles.includes(role)) {
            return res.status(400).json({
                success: false,
                message: 'Role must be admin, teacher, or student'
            });
        }

        // Check if username already exists
        const existingUser = db.findUserByUsername(username);
        if (existingUser) {
            return res.status(400).json({ 
                success: false, 
                message: 'Username already taken' 
            });
        }

        // Create user
        const userData = {
            username,
            password,
            firstName,
            lastName,
            email,
            phone,
            role,
            grade: grade || null,
            isActive: isActive !== undefined ? isActive : true
        };

        const newUser = db.createUser(userData);

        res.status(201).json({
            success: true,
            message: 'User created successfully',
            data: newUser
        });
    } catch (error) {
        console.error('Error creating user:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to create user',
            error: error.message
        });
    }
});

/**
 * @route   PUT /api/auth/users/:id
 * @desc    Update user (admin only)
 * @access  Private/Admin
 */
router.put('/users/:id', async (req, res) => {
    try {
        // This route should be protected by auth middleware
        // Check if requester is admin
        if (!req.user || req.user.role !== 'admin') {
            return res.status(403).json({
                success: false,
                message: 'Access denied. Admin only.'
            });
        }

        const userId = req.params.id;
        const updateData = { ...req.body };

        // Don't allow role change to admin except by admin
        if (updateData.role === 'admin' && req.user.role !== 'admin') {
            return res.status(403).json({
                success: false,
                message: 'Cannot promote to admin role'
            });
        }

        // Find user
        const users = db.readUsers();
        const userIndex = users.findIndex(user => user.id === userId);

        if (userIndex === -1) {
            return res.status(404).json({
                success: false,
                message: 'User not found'
            });
        }

        // If password is provided, hash it
        if (updateData.password) {
            updateData.password = db.hashPassword(updateData.password);
        }

        // Update user
        const updatedUser = {
            ...users[userIndex],
            ...updateData,
            updatedAt: new Date().toISOString()
        };

        users[userIndex] = updatedUser;
        db.writeUsers(users);

        // Remove password from response
        const { password, ...userWithoutPassword } = updatedUser;

        res.json({
            success: true,
            message: 'User updated successfully',
            data: userWithoutPassword
        });
    } catch (error) {
        console.error('Error updating user:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to update user',
            error: error.message
        });
    }
});

/**
 * @route   DELETE /api/auth/users/:id
 * @desc    Delete user (admin only)
 * @access  Private/Admin
 */
router.delete('/users/:id', async (req, res) => {
    try {
        // This route should be protected by auth middleware
        // Check if requester is admin
        if (!req.user || req.user.role !== 'admin') {
            return res.status(403).json({
                success: false,
                message: 'Access denied. Admin only.'
            });
        }

        const userId = req.params.id;

        // Don't allow deleting yourself
        if (userId === req.user.id) {
            return res.status(400).json({
                success: false,
                message: 'Cannot delete yourself'
            });
        }

        // Find user
        const users = db.readUsers();
        const userIndex = users.findIndex(user => user.id === userId);

        if (userIndex === -1) {
            return res.status(404).json({
                success: false,
                message: 'User not found'
            });
        }

        // Remove user
        users.splice(userIndex, 1);
        db.writeUsers(users);

        res.json({
            success: true,
            message: 'User deleted successfully'
        });
    } catch (error) {
        console.error('Error deleting user:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to delete user',
            error: error.message
        });
    }
});

module.exports = router; 