const express = require('express');
const router = express.Router();
const auth = require('../middleware/auth');
const roleCheck = require('../middleware/roleCheck');
const { validateCourse } = require('../models/Course');
const JsonDB = require('../utils/jsonDb');
const courseCtrl = require('../controllers/course.controller');

// Initialize JsonDB for courses and users
const coursesDb = new JsonDB('courses');
const usersDb = new JsonDB('users');

// @route   GET api/courses
// @desc    Get all courses
// @access  Private
router.get('/', auth, async (req, res) => {
  try {
    // If user is admin or teacher, get all courses
    // If user is student, get only enrolled courses
    const allCourses = coursesDb.findAll();
    
    let courses;
    
    if (req.user.role === 'admin') {
      courses = allCourses;
    } else if (req.user.role === 'teacher') {
      courses = allCourses.filter(course => course.teacher === req.user.id);
    } else {
      courses = allCourses.filter(course => course.students.includes(req.user.id));
    }
    
    // Populate teacher data
    const populatedCourses = courses.map(course => {
      const teacher = usersDb.findById(course.teacher);
      return {
        ...course,
        teacher: teacher ? { 
          id: teacher.id, 
          firstName: teacher.firstName, 
          lastName: teacher.lastName, 
          email: teacher.email 
        } : null
      };
    });
    
    // Sort by createdAt in descending order
    populatedCourses.sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt));
    
    res.json(populatedCourses);
  } catch (err) {
    console.error(err.message);
    res.status(500).send('Server Error');
  }
});

// @route   GET api/courses/:id
// @desc    Get course by ID
// @access  Private
router.get('/:id', auth, async (req, res) => {
  try {
    const course = coursesDb.findById(req.params.id);
    
    if (!course) {
      return res.status(404).json({ msg: 'Course not found' });
    }
    
    // Check if user has access to this course
    if (req.user.role === 'student' && !course.students.includes(req.user.id)) {
      return res.status(403).json({ msg: 'Access denied' });
    }
    
    if (req.user.role === 'teacher' && course.teacher !== req.user.id) {
      return res.status(403).json({ msg: 'Access denied' });
    }
    
    // Populate teacher and students data
    const teacher = usersDb.findById(course.teacher);
    const students = course.students.map(studentId => {
      const student = usersDb.findById(studentId);
      return student ? { 
        id: student.id, 
        firstName: student.firstName, 
        lastName: student.lastName, 
        email: student.email,
        grade: student.grade 
      } : null;
    }).filter(Boolean);
    
    const populatedCourse = {
      ...course,
      teacher: teacher ? { 
        id: teacher.id, 
        firstName: teacher.firstName, 
        lastName: teacher.lastName, 
        email: teacher.email 
      } : null,
      students
    };
    
    res.json(populatedCourse);
  } catch (err) {
    console.error(err.message);
    res.status(500).send('Server Error');
  }
});

// @route   POST api/courses
// @desc    Create a course
// @access  Private/Teacher
router.post('/', [auth, roleCheck('teacher')], courseCtrl.createCourse);

// @route   GET api/courses/teacher
// @desc    Get courses by teacher
// @access  Private/Teacher
router.get('/teacher', [auth, roleCheck('teacher')], courseCtrl.getCoursesByTeacher);

module.exports = router;  // MUST be last line with no code after