/**
 * Announcement model schema for validation
 * No longer using Mongoose - now using JSON storage instead
 */

const AnnouncementSchema = {
  id: String,
  title: String,
  content: String,
  author: String, // ID reference to a User
  targetAudience: String, // 'all', 'teachers', 'students', 'parents', 'grade-9', 'grade-10', 'grade-11', 'grade-12'
  createdAt: String,
  updatedAt: String
};

/**
 * Validate an announcement object against the schema
 * @param {Object} announcement - The announcement object to validate
 * @returns {Boolean} - Whether the announcement is valid
 */
const validateAnnouncement = (announcement) => {
  if (!announcement.title || typeof announcement.title !== 'string') return false;
  if (!announcement.content || typeof announcement.content !== 'string') return false;
  if (!announcement.author || typeof announcement.author !== 'string') return false;
  
  // Validate target audience
  const validAudiences = ['all', 'teachers', 'students', 'parents', 'grade-9', 'grade-10', 'grade-11', 'grade-12'];
  if (!announcement.targetAudience || !validAudiences.includes(announcement.targetAudience)) return false;
  
  return true;
};

module.exports = {
  AnnouncementSchema,
  validateAnnouncement
};