const express = require('express');
const { authenticate, requireAdmin } = require('../middlewares/auth');
const {
  findRecords,
  findOneRecord,
  updateRecord,
  deleteRecord,
  tableExists
} = require('../utils/db');

const router = express.Router();

/**
 * Get all users (admin only)
 * @route GET /api/users
 * @access Admin only
 */
router.get('/', authenticate, requireAdmin, async (req, res) => {
  try {
    if (!await tableExists('users')) {
      return res.status(500).json({ message: 'Users table not found' });
    }
    
    const users = await findRecords('users', {});
    
    // Remove password from response
    const sanitizedUsers = users.map(user => {
      const { password, ...userData } = user;
      return userData;
    });
    
    res.status(200).json({ users: sanitizedUsers });
  } catch (error) {
    res.status(500).json({ 
      message: 'Failed to retrieve users', 
      error: error.message 
    });
  }
});

/**
 * Get user by ID
 * @route GET /api/users/:id
 * @access Admin or self
 */
router.get('/:id', authenticate, async (req, res) => {
  try {
    const { id } = req.params;
    
    // Check if user is admin or getting their own profile
    if (req.user.role !== 'admin' && req.user.id !== id) {
      return res.status(403).json({ message: 'Access denied' });
    }
    
    if (!await tableExists('users')) {
      return res.status(500).json({ message: 'Users table not found' });
    }
    
    const user = await findOneRecord('users', { id });
    
    if (!user) {
      return res.status(404).json({ message: 'User not found' });
    }
    
    // Remove password from response
    const { password, ...userData } = user;
    
    res.status(200).json({ user: userData });
  } catch (error) {
    res.status(500).json({ 
      message: 'Failed to retrieve user', 
      error: error.message 
    });
  }
});

/**
 * Update user
 * @route PUT /api/users/:id
 * @access Admin or self
 */
router.put('/:id', authenticate, async (req, res) => {
  try {
    const { id } = req.params;
    const updates = req.body;
    
    // Check if user is admin or updating their own profile
    if (req.user.role !== 'admin' && req.user.id !== id) {
      return res.status(403).json({ message: 'Access denied' });
    }
    
    // Only admin can update role
    if (updates.role && req.user.role !== 'admin') {
      return res.status(403).json({ message: 'Only admin can change roles' });
    }
    
    if (!await tableExists('users')) {
      return res.status(500).json({ message: 'Users table not found' });
    }
    
    // Don't allow password updates through this endpoint
    if (updates.password) {
      delete updates.password;
    }
    
    // Add metadata
    updates.updatedAt = new Date().toISOString();
    updates.updatedBy = req.user.id;
    
    const updatedUser = await updateRecord('users', id, updates);
    
    if (!updatedUser) {
      return res.status(404).json({ message: 'User not found' });
    }
    
    // Remove password from response
    const { password, ...userData } = updatedUser;
    
    res.status(200).json({ 
      message: 'User updated successfully',
      user: userData
    });
  } catch (error) {
    res.status(500).json({ 
      message: 'Failed to update user', 
      error: error.message 
    });
  }
});

/**
 * Delete user
 * @route DELETE /api/users/:id
 * @access Admin only
 */
router.delete('/:id', authenticate, requireAdmin, async (req, res) => {
  try {
    const { id } = req.params;
    
    // Prevent deleting the last admin
    const users = await findRecords('users', { role: 'admin' });
    if (users.length === 1 && users[0].id === id) {
      return res.status(400).json({ message: 'Cannot delete the last admin user' });
    }
    
    if (!await tableExists('users')) {
      return res.status(500).json({ message: 'Users table not found' });
    }
    
    const success = await deleteRecord('users', id);
    
    if (!success) {
      return res.status(404).json({ message: 'User not found' });
    }
    
    res.status(200).json({ message: 'User deleted successfully' });
  } catch (error) {
    res.status(500).json({ 
      message: 'Failed to delete user', 
      error: error.message 
    });
  }
});

module.exports = router; 