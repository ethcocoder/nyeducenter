const express = require('express');
const router = express.Router();
const adminController = require('../controllers/adminController');
const { authenticateToken, requireAdmin } = require('../middleware/auth');

// All admin routes require authentication and admin authorization
router.use(authenticateToken, requireAdmin);

// Dashboard analytics
router.get('/dashboard', adminController.getDashboardStats);

// User management
router.get('/users', adminController.getAllUsers);
router.get('/users/:id', adminController.getUserById);
router.post('/users', adminController.createUser);
router.put('/users/:id', adminController.updateUser);
router.delete('/users/:id', adminController.deleteUser);

// Course management
router.get('/courses', adminController.getAllCourses);
router.get('/courses/:id', adminController.getCourseById);
router.post('/courses', adminController.createCourse);
router.put('/courses/:id', adminController.updateCourse);
router.delete('/courses/:id', adminController.deleteCourse);

// System settings
router.get('/settings', adminController.getSettings);
router.put('/settings', adminController.updateSettings);

// Reports
router.get('/reports/users', adminController.getUsersReport);
router.get('/reports/courses', adminController.getCoursesReport);
router.get('/reports/activities', adminController.getActivitiesReport);

module.exports = router; 