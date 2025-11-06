const express = require('express');
const router = express.Router();
const auth = require('../middleware/auth');
const {
  getConversations,
  getMessages,
  sendMessage,
  startConversation
} = require('../controllers/message.controller');

// List all conversations for the logged-in user
router.get('/conversations', auth, getConversations);

// Get all messages in a conversation
router.get('/:conversationId', auth, getMessages);

// Send a message in an existing conversation
router.post('/', auth, sendMessage);

// Start a new conversation (or reuse existing), send first message
router.post('/conversation', auth, startConversation);

module.exports = router; 