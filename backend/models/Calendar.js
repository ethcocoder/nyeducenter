/**
 * Calendar event model schema for validation
 * No longer using Mongoose - now using JSON storage instead
 */

const CalendarEventSchema = {
  id: String,
  title: String,
  description: String,
  startDate: String,
  endDate: String,
  eventType: String, // 'class', 'exam', 'holiday', 'meeting', 'other'
  course: String, // ID reference to a Course (optional)
  createdBy: String, // ID reference to a User
  participants: Array, // Array of User IDs
  createdAt: String,
  updatedAt: String
};

/**
 * Validate a calendar event object against the schema
 * @param {Object} event - The calendar event object to validate
 * @returns {Boolean} - Whether the event is valid
 */
const validateCalendarEvent = (event) => {
  if (!event.title || typeof event.title !== 'string') return false;
  if (event.description && typeof event.description !== 'string') return false;
  
  // Validate dates
  if (!event.startDate) return false;
  if (!event.endDate) return false;
  
  // Try to parse the dates
  try {
    const start = new Date(event.startDate);
    const end = new Date(event.endDate);
    if (isNaN(start.getTime()) || isNaN(end.getTime())) return false;
    if (end < start) return false; // End date must be after start date
  } catch (e) {
    return false;
  }
  
  // Validate event type
  const validTypes = ['class', 'exam', 'holiday', 'meeting', 'other'];
  if (!event.eventType || !validTypes.includes(event.eventType)) return false;
  
  // Course is optional but must be a string if present
  if (event.course && typeof event.course !== 'string') return false;
  
  if (!event.createdBy || typeof event.createdBy !== 'string') return false;
  
  // Participants must be an array
  if (event.participants && !Array.isArray(event.participants)) return false;
  
  return true;
};

module.exports = {
  CalendarEventSchema,
  validateCalendarEvent
};