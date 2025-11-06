const express = require('express');
const { studentController } = require('../controllers/studentController');
const authMiddleware = require('../middlewares/auth');
const { authorizeStudent } = require('../middlewares/authorize');

const router = express.Router();

// Apply auth middleware to all routes
router.use(authMiddleware);
router.use(authorizeStudent);

// Dashboard
router.get('/dashboard', studentController.getDashboard);

// Profile
router.get('/profile', studentController.getProfile);
router.put('/profile', studentController.updateProfile);

// Courses
router.get('/courses', studentController.getCourses);
router.get('/courses/:courseId', studentController.getCourseById);

// Assignments
router.get('/assignments', studentController.getAssignments);
router.get('/assignments/:assignmentId', studentController.getAssignmentById);
router.post('/assignments/:assignmentId/submit', studentController.submitAssignment);
router.get('/submissions', studentController.getSubmissions);

// Quizzes
router.get('/quizzes', studentController.getQuizzes);
router.get('/quizzes/:quizId', studentController.getQuizById);
router.post('/quizzes/:quizId/start', studentController.startQuiz);
router.post('/quizzes/:quizId/submit', studentController.submitQuiz);
router.get('/quiz-results', studentController.getQuizResults);
router.get('/quiz-results/:resultId', studentController.getQuizResultById);

// Grades
router.get('/grades', studentController.getGrades);
router.get('/courses/:courseId/grades', studentController.getCourseGrades);

module.exports = router; 