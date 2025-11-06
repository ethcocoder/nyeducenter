const pool = require('../config/db.config');

// List all conversations for the logged-in user
async function getConversations(req, res) {
  const userId = req.user.id;
  try {
    const [rows] = await pool.query(`
      SELECT c.id as conversationId,
             u.id as userId,
             u.firstName, u.lastName, u.role,
             u.avatar,
             m.content as lastMessage,
             m.created_at as lastMessageTime,
             SUM(CASE WHEN m.is_read = 0 AND m.sender_id != ? THEN 1 ELSE 0 END) as unread
      FROM conversations c
      JOIN users u ON (u.id = IF(c.user1_id = ?, c.user2_id, c.user1_id))
      LEFT JOIN messages m ON m.conversation_id = c.id
      WHERE c.user1_id = ? OR c.user2_id = ?
      GROUP BY c.id
      ORDER BY MAX(m.created_at) DESC
    `, [userId, userId, userId, userId]);
    res.json(rows);
  } catch (err) {
    res.status(500).json({ error: 'Failed to fetch conversations' });
  }
}

// Get all messages in a conversation
async function getMessages(req, res) {
  const userId = req.user.id;
  const conversationId = req.params.conversationId;
  try {
    // Optionally, check if user is part of the conversation
    const [check] = await pool.query(
      'SELECT * FROM conversations WHERE id = ? AND (user1_id = ? OR user2_id = ?)',
      [conversationId, userId, userId]
    );
    if (check.length === 0) return res.status(403).json({ error: 'Forbidden' });
    const [messages] = await pool.query(
      'SELECT id, sender_id, content, created_at FROM messages WHERE conversation_id = ? ORDER BY created_at ASC',
      [conversationId]
    );
    res.json(messages.map(m => ({
      id: m.id,
      sender: m.sender_id,
      content: m.content,
      timestamp: m.created_at
    })));
  } catch (err) {
    res.status(500).json({ error: 'Failed to fetch messages' });
  }
}

// Send a message in an existing conversation
async function sendMessage(req, res) {
  const userId = req.user.id;
  const { to, content } = req.body;
  try {
    // Find conversation between userId and to
    const [convos] = await pool.query(
      'SELECT id FROM conversations WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)',
      [userId, to, to, userId]
    );
    if (convos.length === 0) return res.status(404).json({ error: 'Conversation not found' });
    const conversationId = convos[0].id;
    const [result] = await pool.query(
      'INSERT INTO messages (conversation_id, sender_id, content) VALUES (?, ?, ?)',
      [conversationId, userId, content]
    );
    const [msg] = await pool.query('SELECT id, sender_id, content, created_at FROM messages WHERE id = ?', [result.insertId]);
    res.json({
      id: msg[0].id,
      sender: msg[0].sender_id,
      content: msg[0].content,
      timestamp: msg[0].created_at
    });
  } catch (err) {
    res.status(500).json({ error: 'Failed to send message' });
  }
}

// Start a new conversation (or reuse existing), send first message
async function startConversation(req, res) {
  const userId = req.user.id;
  const { to, content } = req.body;
  try {
    // Check if conversation exists
    let [convos] = await pool.query(
      'SELECT id FROM conversations WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)',
      [userId, to, to, userId]
    );
    let conversationId;
    if (convos.length === 0) {
      // Create new conversation
      const [result] = await pool.query(
        'INSERT INTO conversations (user1_id, user2_id) VALUES (?, ?)',
        [userId, to]
      );
      conversationId = result.insertId;
    } else {
      conversationId = convos[0].id;
    }
    // Insert first message
    const [msgResult] = await pool.query(
      'INSERT INTO messages (conversation_id, sender_id, content) VALUES (?, ?, ?)',
      [conversationId, userId, content]
    );
    const [msg] = await pool.query('SELECT id, sender_id, content, created_at FROM messages WHERE id = ?', [msgResult.insertId]);
    // Return conversation info and first message
    const [users] = await pool.query('SELECT id, firstName, lastName, role, avatar FROM users WHERE id = ?', [to]);
    res.json({
      conversation: {
        id: conversationId,
        userId: users[0].id,
        name: users[0].firstName + ' ' + users[0].lastName,
        role: users[0].role,
        avatar: users[0].avatar,
        lastMessage: msg[0].content,
        lastMessageTime: msg[0].created_at,
        unread: 0
      },
      message: {
        id: msg[0].id,
        sender: msg[0].sender_id,
        content: msg[0].content,
        timestamp: msg[0].created_at
      }
    });
  } catch (err) {
    res.status(500).json({ error: 'Failed to start conversation' });
  }
}

module.exports = {
  getConversations,
  getMessages,
  sendMessage,
  startConversation
}; 