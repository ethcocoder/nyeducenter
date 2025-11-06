const express = require('express');
const router = express.Router();
const auth = require('../middleware/auth');
const roleCheck = require('../middleware/roleCheck');
const Quiz = require('../models/Quiz');

// @route   POST api/quizzes
// @desc    Create new quiz
// @access  Private/Teacher
router.post('/', [auth, roleCheck(['teacher', 'admin'])], async (req, res) => {
  try {
    const { title, questions, course, timeLimit } = req.body;
    
    const newQuiz = new Quiz({
      title,
      questions,
      course,
      timeLimit,
      createdBy: req.user.id
    });

    const quiz = await newQuiz.save();
    res.json(quiz);
  } catch (err) {
    console.error(err.message);
    res.status(500).send('Server error');
  }
});

// @route   GET api/quizzes
// @desc    Get all quizzes
// @access  Private
router.get('/', auth, async (req, res) => {
  try {
    const quizzes = await Quiz.find()
      .populate('course', 'name')
      .populate('createdBy', 'firstName lastName');
    res.json(quizzes);
  } catch (err) {
    console.error(err.message);
    res.status(500).send('Server error');
  }
});

// @route   GET api/quizzes/:id
// @desc    Get quiz by ID
// @access  Private
router.get('/:id', auth, async (req, res) => {
  try {
    const quiz = await Quiz.findById(req.params.id)
      .populate('course', 'name')
      .populate('createdBy', 'firstName lastName');
      
    if (!quiz) {
      return res.status(404).json({ msg: 'Quiz not found' });
    }
    
    res.json(quiz);
  } catch (err) {
    console.error(err.message);
    res.status(500).send('Server error');
  }
});

module.exports = router;