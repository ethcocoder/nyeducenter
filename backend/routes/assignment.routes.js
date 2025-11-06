const express = require('express');
const router = express.Router();
const auth = require('../middleware/auth');
const roleCheck = require('../middleware/roleCheck');
const { validateAssignment } = require('../models/Assignment');
const JsonDB = require('../utils/jsonDb');
const assignmentCtrl = require('../controllers/assignment.controller');

// Initialize JsonDB for assignments, courses, and users
const assignmentsDb = new JsonDB('assignments');
const coursesDb = new JsonDB('courses');
const usersDb = new JsonDB('users');

// @route   POST api/assignments
// @desc    Create new assignment
// @access  Private/Teacher
router.post('/', [auth, roleCheck('teacher')], assignmentCtrl.createAssignment);

// @route   GET api/assignments
// @desc    Get all assignments
// @access  Private
router.get('/', [auth, roleCheck('teacher')], assignmentCtrl.getAssignmentsByTeacher);

// @route   GET api/assignments/:id
// @desc    Get assignment by ID
// @access  Private
router.get('/:id', auth, async (req, res) => {
  try {
    const assignment = assignmentsDb.findById(req.params.id);
    
    if (!assignment) {
      return res.status(404).json({ msg: 'Assignment not found' });
    }
    
    // Populate course and createdBy data
    const course = coursesDb.findById(assignment.course);
    const creator = usersDb.findById(assignment.createdBy);
    
    // Populate student data in submissions
    const populatedSubmissions = assignment.submissions.map(submission => {
      const student = usersDb.findById(submission.student);
      return {
        ...submission,
        student: student ? {
          id: student.id,
          firstName: student.firstName,
          lastName: student.lastName,
          email: student.email
        } : null
      };
    });
    
    const populatedAssignment = {
      ...assignment,
      course: course ? { 
        id: course.id, 
        title: course.title 
      } : null,
      createdBy: creator ? { 
        id: creator.id, 
        firstName: creator.firstName, 
        lastName: creator.lastName 
      } : null,
      submissions: populatedSubmissions
    };
    
    res.json(populatedAssignment);
  } catch (err) {
    console.error(err.message);
    res.status(500).send('Server error');
  }
});

// @route   PUT api/assignments/:id
// @desc    Update assignment
// @access  Private/Teacher
router.put('/:id', [auth, roleCheck(['teacher', 'admin'])], async (req, res) => {
  try {
    const assignment = assignmentsDb.findById(req.params.id);
    
    if (!assignment) {
      return res.status(404).json({ msg: 'Assignment not found' });
    }
    
    // Check if user is authorized to update
    if (assignment.createdBy !== req.user.id && req.user.role !== 'admin') {
      return res.status(403).json({ msg: 'Not authorized' });
    }
    
    const { title, description, dueDate, totalPoints } = req.body;
    
    const updatedAssignment = {
      ...assignment,
      title: title || assignment.title,
      description: description || assignment.description,
      dueDate: dueDate || assignment.dueDate,
      totalPoints: totalPoints || assignment.totalPoints
    };
    
    // Validate updated assignment
    if (!validateAssignment(updatedAssignment)) {
      return res.status(400).json({ msg: 'Invalid assignment data' });
    }
    
    const result = assignmentsDb.updateById(req.params.id, updatedAssignment);
    res.json(result);
  } catch (err) {
    console.error(err.message);
    res.status(500).send('Server error');
  }
});

module.exports = router;