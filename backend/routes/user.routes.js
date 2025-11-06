const express = require('express');
const router = express.Router();
const auth = require('../middleware/auth');
const { User } = require('../models/User');
const roleCheck = require('../middleware/roleCheck');
const studentCtrl = require('../controllers/student.controller');

// @route   GET api/users
// @desc    Get all users (for admin dashboard)
// @access  Private/Admin
router.get('/', [auth, roleCheck('admin')], async (req, res) => {
  try {
    const db = require('../config/db.config');
    const [users] = await db.query(
      'SELECT id, first_name AS firstName, last_name AS lastName, email, role_id, avatar, is_active FROM users'
    );
    const roleMap = { 1: 'Admin', 2: 'Teacher', 3: 'Student', 4: 'Parent' };
    const usersFormatted = users.map(u => ({
      id: u.id,
      firstName: u.firstName,
      lastName: u.lastName,
      email: u.email,
      role: roleMap[u.role_id] || 'Student',
      avatar: u.avatar,
      is_active: u.is_active,
      last_active: u.last_active
    }));
    res.json(usersFormatted);
  } catch (err) {
    console.error(err.message);
    res.status(500).send('Server Error');
  }
});

// @route   GET api/users/:id
// @desc    Get user by ID
// @access  Private/Admin
router.get('/:id', [auth, roleCheck('admin')], async (req, res) => {
  try {
    const user = await User.findById(req.params.id);
    if (!user) {
      return res.status(404).json({ msg: 'User not found' });
    }
    
    // Format user object for API response
    const userFormatted = {
      id: user.id,
      firstName: user.first_name,
      lastName: user.last_name,
      email: user.email,
      role_id: user.role_id,
      role: user.role_id === 1 ? 'admin' : 
            user.role_id === 2 ? 'teacher' : 
            user.role_id === 4 ? 'parent' : 'student',
      grade: user.grade_level_id,
      is_active: user.is_active,
      created_at: user.created_at,
      updated_at: user.updated_at
    };
    
    res.json(userFormatted);
  } catch (err) {
    console.error(err.message);
    res.status(500).send('Server Error');
  }
});

// @route   PUT api/users/:id
// @desc    Update user 
// @access  Private/Admin
router.put('/:id', [auth, roleCheck('admin')], async (req, res) => {
  try {
    const { firstName, lastName, email, role, grade, is_active } = req.body;
    
    // Map role name to role_id
    let roleId;
    if (role) {
      if (role === 'admin') roleId = 1;
      else if (role === 'teacher') roleId = 2;
      else if (role === 'parent') roleId = 4;
      else roleId = 3; // Default to student
    }
    
    const userData = {
      firstName,
      lastName,
      email,
      role_id: roleId,
      grade,
      is_active
    };
    
    const updated = await User.update(req.params.id, userData);
    
    if (!updated) {
      return res.status(404).json({ msg: 'User not found' });
    }
    
    res.json({ msg: 'User updated successfully' });
  } catch (err) {
    console.error(err.message);
    res.status(500).send('Server Error');
  }
});

// @route   DELETE api/users/:id
// @desc    Delete user
// @access  Private/Admin
router.delete('/:id', [auth, roleCheck('admin')], async (req, res) => {
  try {
    const deleted = await User.delete(req.params.id);
    
    if (!deleted) {
      return res.status(404).json({ msg: 'User not found' });
    }
    
    res.json({ msg: 'User deleted successfully' });
  } catch (err) {
    console.error(err.message);
    res.status(500).send('Server Error');
  }
});

// @route   GET api/users/recipients
// @desc    Get all users except current user (for messaging)
// @access  Private
router.get('/recipients', auth, async (req, res) => {
  try {
    const db = require('../config/db.config');
    const [users] = await db.query(
      'SELECT id, first_name AS firstName, last_name AS lastName, email, role_id, avatar, is_active FROM users WHERE id != ?',
      [req.user.id]
    );
    const roleMap = { 1: 'Admin', 2: 'Teacher', 3: 'Student', 4: 'Parent' };
    const usersFormatted = users.map(u => ({
      id: u.id,
      firstName: u.firstName,
      lastName: u.lastName,
      email: u.email,
      role: roleMap[u.role_id] || 'Student',
      avatar: u.avatar,
      is_active: u.is_active
    }));
    res.json(usersFormatted);
  } catch (err) {
    console.error(err.message);
    res.status(500).send('Server Error');
  }
});

router.get('/dashboard', auth, studentCtrl.getDashboard);

module.exports = router;

// ====== REMOVE EVERYTHING BELOW THIS LINE ======
// The following server configuration code should only exist in server.js