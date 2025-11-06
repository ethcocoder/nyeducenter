const express = require('express');
const { teacherController } = require('../controllers/teacherController');
const { authMiddleware } = require('../middlewares/auth');
const { roleMiddleware } = require('../middlewares/role');

const router = express.Router();

// Apply authentication and teacher role check to all routes in this file
router.use(authMiddleware);
router.use(roleMiddleware('teacher'));

// Dashboard
router.get('/dashboard', teacherController.getDashboard);

// Course Management
router.get('/courses', teacherController.getCourses);
router.get('/courses/:courseId', teacherController.getCourseById);

// Student Management
router.get('/courses/:courseId/students', teacherController.getStudentsInCourse);

// Assignment Management
router.post('/assignments', teacherController.createAssignment); // Likely needs courseId in body or param
router.get('/assignments', teacherController.getAssignments);
router.get('/assignments/:assignmentId', teacherController.getAssignmentById);
router.put('/assignments/:assignmentId', teacherController.updateAssignment);
router.delete('/assignments/:assignmentId', teacherController.deleteAssignment);
router.get('/assignments/:assignmentId/submissions', teacherController.getSubmissionsForAssignment);
router.post('/submissions/:submissionId/grade', teacherController.gradeSubmission); // Needs assignmentId context potentially

// Quiz Management
router.post('/quizzes', teacherController.createQuiz); // Likely needs courseId
router.get('/quizzes', teacherController.getQuizzes);
router.get('/quizzes/:quizId', teacherController.getQuizById);
router.put('/quizzes/:quizId', teacherController.updateQuiz);
router.delete('/quizzes/:quizId', teacherController.deleteQuiz);
router.get('/quizzes/:quizId/attempts', teacherController.getAttemptsForQuiz);

module.exports = router; 