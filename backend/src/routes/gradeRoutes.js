const express = require('express');
const router = express.Router();
const gradeController = require('../controllers/gradeController');
const { authenticateToken, authorizeAdmin } = require('../middleware/authMiddleware');

// All routes require authentication
router.use(authenticateToken);

// Routes for managing grades
// Role can be 'student', 'teacher', or 'admin'
// Grade can be '9', '10', '11', '12' or empty for admin

// Get all grades for a role and grade level
router.get('/:role/:grade', gradeController.getGrades);

// Get a specific grade by ID
router.get('/:role/:grade/:id', gradeController.getGradeById);

// Create a new grade - only admin and teachers can create grades
router.post('/:role/:grade', gradeController.createGrade);

// Update an existing grade - only admin and teachers can update grades
router.put('/:role/:grade/:id', gradeController.updateGrade);

// Delete a grade - only admin can delete grades
router.delete('/:role/:grade/:id', authorizeAdmin, gradeController.deleteGrade);

module.exports = router; 