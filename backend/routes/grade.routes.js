const express = require('express');
const router = express.Router();
const auth = require('../middleware/auth');
const roleCheck = require('../middleware/roleCheck');
const { validateGrade } = require('../models/Grade');
const JsonDB = require('../utils/jsonDb');

// Initialize JsonDB for grades, assignments, and users
const gradesDb = new JsonDB('grades');
const assignmentsDb = new JsonDB('assignments');
const usersDb = new JsonDB('users');

// @route   POST api/grades
// @desc    Create/Update grade
// @access  Private/Teacher
router.post('/', [auth, roleCheck(['teacher', 'admin'])], async (req, res) => {
  try {
    const { assignment, student, score, feedback } = req.body;
    
    // Verify assignment and student exist
    const assignmentExists = assignmentsDb.findById(assignment);
    const studentExists = usersDb.findById(student);
    
    if (!assignmentExists) {
      return res.status(400).json({ msg: 'Assignment not found' });
    }
    
    if (!studentExists) {
      return res.status(400).json({ msg: 'Student not found' });
    }
    
    const newGrade = {
      assignment,
      student,
      score,
      feedback: feedback || '',
      gradedBy: req.user.id
    };
    
    // Validate grade data
    if (!validateGrade(newGrade)) {
      return res.status(400).json({ msg: 'Invalid grade data' });
    }

    const grade = gradesDb.create(newGrade);
    res.json(grade);
  } catch (err) {
    console.error(err.message);
    res.status(500).send('Server error');
  }
});

// @route   GET api/grades
// @desc    Get all grades
// @access  Private
router.get('/', auth, async (req, res) => {
  try {
    const grades = gradesDb.findAll();
    
    // Filter grades based on user role
    let filteredGrades;
    if (req.user.role === 'student') {
      // Students can only see their own grades
      filteredGrades = grades.filter(grade => grade.student === req.user.id);
    } else if (req.user.role === 'teacher') {
      // Teachers can see grades they've submitted
      filteredGrades = grades.filter(grade => grade.gradedBy === req.user.id);
    } else {
      // Admin can see all grades
      filteredGrades = grades;
    }
    
    // Populate assignment and student data
    const populatedGrades = filteredGrades.map(grade => {
      const assignment = assignmentsDb.findById(grade.assignment);
      const student = usersDb.findById(grade.student);
      const gradedBy = usersDb.findById(grade.gradedBy);
      
      return {
        ...grade,
        assignment: assignment ? { 
          id: assignment.id, 
          title: assignment.title 
        } : null,
        student: student ? { 
          id: student.id, 
          firstName: student.firstName, 
          lastName: student.lastName 
        } : null,
        gradedBy: gradedBy ? {
          id: gradedBy.id,
          firstName: gradedBy.firstName,
          lastName: gradedBy.lastName
        } : null
      };
    });
    
    res.json(populatedGrades);
  } catch (err) {
    console.error(err.message);
    res.status(500).send('Server error');
  }
});

// @route   PUT api/grades/:id
// @desc    Update grade
// @access  Private/Teacher
router.put('/:id', [auth, roleCheck(['teacher', 'admin'])], async (req, res) => {
  try {
    const grade = gradesDb.findById(req.params.id);
    
    if (!grade) {
      return res.status(404).json({ msg: 'Grade not found' });
    }
    
    // Check if user is authorized to update
    if (grade.gradedBy !== req.user.id && req.user.role !== 'admin') {
      return res.status(403).json({ msg: 'Not authorized' });
    }
    
    const { score, feedback } = req.body;
    
    const updatedGrade = {
      ...grade,
      score: score !== undefined ? score : grade.score,
      feedback: feedback !== undefined ? feedback : grade.feedback
    };
    
    // Validate updated grade
    if (!validateGrade(updatedGrade)) {
      return res.status(400).json({ msg: 'Invalid grade data' });
    }
    
    const result = gradesDb.updateById(req.params.id, updatedGrade);
    res.json(result);
  } catch (err) {
    console.error(err.message);
    res.status(500).send('Server error');
  }
});

module.exports = router;