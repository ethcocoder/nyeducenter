const express = require('express');
const router = express.Router();
const adminCtrl = require('../controllers/admin.controller');
const auth = require('../middleware/auth');
const roleCheck = require('../middleware/roleCheck');

// Get all users
router.get('/users', [auth, roleCheck('admin')], adminCtrl.getAllUsers);

// Update user
router.put('/users/:id', [auth, roleCheck('admin')], adminCtrl.updateUser);

// Delete user
router.delete('/users/:id', [auth, roleCheck('admin')], adminCtrl.deleteUser);

// Dashboard stats
router.get('/dashboard-stats', [auth, roleCheck('admin')], adminCtrl.getDashboardStats);

module.exports = router; 