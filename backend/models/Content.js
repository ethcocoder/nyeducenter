/**
 * Content model schema for validation
 * No longer using Mongoose - now using JSON storage instead
 */

const ContentSchema = {
  id: String,
  title: String,
  description: String,
  contentType: String, // 'video', 'document', 'link', 'text'
  content: String,
  course: String, // ID reference to a Course
  createdBy: String, // ID reference to a User
  createdAt: String,
  updatedAt: String
};

/**
 * Validate a content object against the schema
 * @param {Object} content - The content object to validate
 * @returns {Boolean} - Whether the content is valid
 */
const validateContent = (content) => {
  if (!content.title || typeof content.title !== 'string') return false;
  if (content.description && typeof content.description !== 'string') return false;
  
  // Validate content type
  const validTypes = ['video', 'document', 'link', 'text'];
  if (!content.contentType || !validTypes.includes(content.contentType)) return false;
  
  if (!content.content || typeof content.content !== 'string') return false;
  if (!content.course || typeof content.course !== 'string') return false;
  if (!content.createdBy || typeof content.createdBy !== 'string') return false;
  
  return true;
};

module.exports = {
  ContentSchema,
  validateContent
};