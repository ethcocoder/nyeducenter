/**
 * Message model schema for validation
 * No longer using Mongoose - now using JSON storage instead
 */

const MessageSchema = {
  id: String,
  sender: String, // ID reference to a User
  receiver: String, // ID reference to a User
  subject: String,
  content: String,
  isRead: Boolean,
  createdAt: String,
  updatedAt: String
};

/**
 * Validate a message object against the schema
 * @param {Object} message - The message object to validate
 * @returns {Boolean} - Whether the message is valid
 */
const validateMessage = (message) => {
  if (!message.sender || typeof message.sender !== 'string') return false;
  if (!message.receiver || typeof message.receiver !== 'string') return false;
  if (!message.subject || typeof message.subject !== 'string') return false;
  if (!message.content || typeof message.content !== 'string') return false;
  
  // isRead should be a boolean
  if (typeof message.isRead !== 'boolean') return false;
  
  return true;
};

module.exports = {
  MessageSchema,
  validateMessage
};