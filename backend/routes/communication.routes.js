const express = require('express');
const router = express.Router();
const auth = require('../middleware/auth');
const Message = require('../models/Message');

// @route   POST api/communication
// @desc    Create new message
// @access  Private
router.post('/', auth, async (req, res) => {
  try {
    const { content, recipient, course } = req.body;
    
    const newMessage = new Message({
      sender: req.user.id,
      recipient,
      content,
      course,
      read: false
    });

    const message = await newMessage.save();
    res.json(message);
  } catch (err) {
    console.error(err.message);
    res.status(500).send('Server error');
  }
});

// @route   GET api/communication
// @desc    Get user messages
// @access  Private
router.get('/', auth, async (req, res) => {
  try {
    const messages = await Message.find({
      $or: [{ sender: req.user.id }, { recipient: req.user.id }]
    })
    .populate('sender', 'firstName lastName')
    .populate('recipient', 'firstName lastName')
    .sort('-createdAt');
    
    res.json(messages);
  } catch (err) {
    console.error(err.message);
    res.status(500).send('Server error');
  }
});

module.exports = router;