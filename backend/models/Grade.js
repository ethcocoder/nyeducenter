/**
 * Grade model schema for validation
 * No longer using Mongoose - now using JSON storage instead
 */

const GradeSchema = {
  id: String,
  assignment: String, // ID reference to an Assignment
  student: String, // ID reference to a User
  score: Number, // 0-100
  feedback: String,
  gradedBy: String, // ID reference to a User
  createdAt: String,
  updatedAt: String
};

/**
 * Validate a grade object against the schema
 * @param {Object} grade - The grade object to validate
 * @returns {Boolean} - Whether the grade is valid
 */
const validateGrade = (grade) => {
  if (!grade.assignment || typeof grade.assignment !== 'string') return false;
  if (!grade.student || typeof grade.student !== 'string') return false;
  
  if (grade.score === undefined || 
      isNaN(grade.score) || 
      grade.score < 0 || 
      grade.score > 100) return false;
  
  if (grade.feedback && typeof grade.feedback !== 'string') return false;
  if (!grade.gradedBy || typeof grade.gradedBy !== 'string') return false;
  
  return true;
};

module.exports = {
  GradeSchema,
  validateGrade
};