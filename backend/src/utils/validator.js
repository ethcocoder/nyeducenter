const schemas = require('../models/schemas');

/**
 * Validates data against a schema
 * @param {Object} data - The data to validate
 * @param {Object} schema - The schema to validate against
 * @returns {Object} - Object with isValid and errors properties
 */
function validateAgainstSchema(data, schema) {
  const errors = [];
  
  // Check all required fields
  Object.entries(schema).forEach(([field, definition]) => {
    // Skip validation if the field is not required and not present
    if (!definition.required && (data[field] === undefined || data[field] === null)) {
      return;
    }
    
    // Check if required field is present
    if (definition.required && (data[field] === undefined || data[field] === null)) {
      errors.push(`${field} is required`);
      return;
    }
    
    // If field exists, validate its type
    if (data[field] !== undefined && data[field] !== null) {
      // Type validation
      if (definition.type === 'string' && typeof data[field] !== 'string') {
        errors.push(`${field} must be a string`);
      } else if (definition.type === 'number' && typeof data[field] !== 'number') {
        errors.push(`${field} must be a number`);
      } else if (definition.type === 'boolean' && typeof data[field] !== 'boolean') {
        errors.push(`${field} must be a boolean`);
      } else if (definition.type === 'object' && (typeof data[field] !== 'object' || Array.isArray(data[field]))) {
        errors.push(`${field} must be an object`);
      } else if (definition.type === 'array' && !Array.isArray(data[field])) {
        errors.push(`${field} must be an array`);
      }
      
      // Enum validation
      if (definition.enum && !definition.enum.includes(data[field])) {
        errors.push(`${field} must be one of: ${definition.enum.join(', ')}`);
      }
      
      // Format validation for date-time
      if (definition.format === 'date-time' && !isValidDateTime(data[field])) {
        errors.push(`${field} must be a valid date-time string`);
      }
      
      // Array item validation
      if (definition.type === 'array' && definition.items && Array.isArray(data[field])) {
        data[field].forEach((item, index) => {
          if (definition.items.type === 'string' && typeof item !== 'string') {
            errors.push(`${field}[${index}] must be a string`);
          } else if (definition.items.type === 'number' && typeof item !== 'number') {
            errors.push(`${field}[${index}] must be a number`);
          } else if (definition.items.type === 'object' && (typeof item !== 'object' || Array.isArray(item))) {
            errors.push(`${field}[${index}] must be an object`);
          }
          
          // Validate object properties in array
          if (definition.items.type === 'object' && definition.items.properties && typeof item === 'object') {
            const itemValidation = validateAgainstSchema(item, definition.items.properties);
            if (!itemValidation.isValid) {
              itemValidation.errors.forEach(error => {
                errors.push(`${field}[${index}]: ${error}`);
              });
            }
          }
        });
      }
    }
  });
  
  return {
    isValid: errors.length === 0,
    errors
  };
}

/**
 * Checks if a string is a valid date-time format
 * @param {string} dateTimeString - The string to check
 * @returns {boolean} - Whether the string is a valid date-time
 */
function isValidDateTime(dateTimeString) {
  if (typeof dateTimeString !== 'string') return false;
  
  // Simple validation - could be expanded for stricter checking
  const date = new Date(dateTimeString);
  return !isNaN(date.getTime());
}

/**
 * Validate user data against the user schema
 * @param {Object} userData - The user data to validate
 * @returns {Object} - Object with isValid and errors properties
 */
function validateUser(userData) {
  return validateAgainstSchema(userData, schemas.userSchema);
}

/**
 * Validate course data against the course schema
 * @param {Object} courseData - The course data to validate
 * @returns {Object} - Object with isValid and errors properties
 */
function validateCourse(courseData) {
  return validateAgainstSchema(courseData, schemas.courseSchema);
}

/**
 * Validate activity data against the activity schema
 * @param {Object} activityData - The activity data to validate
 * @returns {Object} - Object with isValid and errors properties
 */
function validateActivity(activityData) {
  return validateAgainstSchema(activityData, schemas.activitySchema);
}

/**
 * Validate settings data against the settings schema
 * @param {Object} settingsData - The settings data to validate
 * @returns {Object} - Object with isValid and errors properties
 */
function validateSettings(settingsData) {
  return validateAgainstSchema(settingsData, schemas.settingsSchema);
}

/**
 * Validate quiz data against the quiz schema
 * @param {Object} quizData - The quiz data to validate
 * @returns {Object} - Object with isValid and errors properties
 */
function validateQuiz(quizData) {
  return validateAgainstSchema(quizData, schemas.quizSchema);
}

/**
 * Validate assignment data against the assignment schema
 * @param {Object} assignmentData - The assignment data to validate
 * @returns {Object} - Object with isValid and errors properties
 */
function validateAssignment(assignmentData) {
  return validateAgainstSchema(assignmentData, schemas.assignmentSchema);
}

/**
 * Validate quiz submission data against the quiz submission schema
 * @param {Object} submissionData - The submission data to validate
 * @returns {Object} - Object with isValid and errors properties
 */
function validateQuizSubmission(submissionData) {
  return validateAgainstSchema(submissionData, schemas.quizSubmissionSchema);
}

/**
 * Validate assignment submission data against the assignment submission schema
 * @param {Object} submissionData - The submission data to validate
 * @returns {Object} - Object with isValid and errors properties
 */
function validateAssignmentSubmission(submissionData) {
  return validateAgainstSchema(submissionData, schemas.assignmentSubmissionSchema);
}

module.exports = {
  validateUser,
  validateCourse,
  validateActivity,
  validateSettings,
  validateQuiz,
  validateAssignment,
  validateQuizSubmission,
  validateAssignmentSubmission,
  validateAgainstSchema
}; 