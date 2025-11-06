/**
 * Assignment model for MySQL database
 */

const { pool } = require('../config/db.config');

class Assignment {
  /**
   * Get an assignment by ID
   * @param {Number} id - The assignment ID
   * @returns {Promise<Object>} - The assignment object
   */
  static async findById(id) {
    try {
      const [rows] = await pool.query('SELECT * FROM assignments WHERE id = ?', [id]);
      return rows[0] || null;
    } catch (error) {
      console.error('Error fetching assignment by ID:', error);
      throw error;
    }
  }

  /**
   * Create a new assignment
   * @param {Object} assignmentData - The assignment data
   * @returns {Promise<Object>} - The created assignment
   */
  static async create(assignmentData) {
    try {
      const [result] = await pool.query(
        `INSERT INTO assignments (
          title, description, course_id, due_date,
          total_points, created_by, instructions, is_group_work
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)`,
        [
          assignmentData.title,
          assignmentData.description,
          assignmentData.course,
          new Date(assignmentData.dueDate),
          assignmentData.totalPoints,
          assignmentData.createdBy,
          assignmentData.instructions || null,
          assignmentData.isGroupWork || false
        ]
      );
      
      return { id: result.insertId, ...assignmentData };
    } catch (error) {
      console.error('Error creating assignment:', error);
      throw error;
    }
  }

  /**
   * Update an assignment
   * @param {Number} id - The assignment ID
   * @param {Object} assignmentData - The updated assignment data
   * @returns {Promise<Boolean>} - Whether the update was successful
   */
  static async update(id, assignmentData) {
    try {
      const updateFields = [];
      const updateValues = [];
      
      if (assignmentData.title) {
        updateFields.push('title = ?');
        updateValues.push(assignmentData.title);
      }
      
      if (assignmentData.description) {
        updateFields.push('description = ?');
        updateValues.push(assignmentData.description);
      }
      
      if (assignmentData.dueDate) {
        updateFields.push('due_date = ?');
        updateValues.push(new Date(assignmentData.dueDate));
      }
      
      if (assignmentData.totalPoints) {
        updateFields.push('total_points = ?');
        updateValues.push(assignmentData.totalPoints);
      }
      
      if (assignmentData.instructions) {
        updateFields.push('instructions = ?');
        updateValues.push(assignmentData.instructions);
      }
      
      // Add assignment ID to values array for WHERE clause
      updateValues.push(id);
      
      const [result] = await pool.query(
        `UPDATE assignments SET ${updateFields.join(', ')}, updated_at = NOW() WHERE id = ?`,
        updateValues
      );
      
      return result.affectedRows > 0;
    } catch (error) {
      console.error('Error updating assignment:', error);
      throw error;
    }
  }

  /**
   * Delete an assignment
   * @param {Number} id - The assignment ID
   * @returns {Promise<Boolean>} - Whether the deletion was successful
   */
  static async delete(id) {
    try {
      const [result] = await pool.query('DELETE FROM assignments WHERE id = ?', [id]);
      return result.affectedRows > 0;
    } catch (error) {
      console.error('Error deleting assignment:', error);
      throw error;
    }
  }

  /**
   * Get all assignments
   * @param {Object} filters - Optional filters like course, teacher
   * @returns {Promise<Array>} - Array of assignment objects
   */
  static async findAll(filters = {}) {
    try {
      let query = 'SELECT * FROM assignments';
      const whereConditions = [];
      const queryParams = [];
      
      if (filters.course) {
        whereConditions.push('course_id = ?');
        queryParams.push(filters.course);
      }
      
      if (filters.teacher) {
        whereConditions.push('created_by = ?');
        queryParams.push(filters.teacher);
      }
      
      if (filters.dueAfter) {
        whereConditions.push('due_date >= ?');
        queryParams.push(new Date(filters.dueAfter));
      }
      
      if (filters.dueBefore) {
        whereConditions.push('due_date <= ?');
        queryParams.push(new Date(filters.dueBefore));
      }
      
      if (whereConditions.length > 0) {
        query += ' WHERE ' + whereConditions.join(' AND ');
      }
      
      const [rows] = await pool.query(query, queryParams);
      return rows;
    } catch (error) {
      console.error('Error fetching assignments:', error);
      throw error;
    }
  }

  /**
   * Get assignments for a specific student
   * @param {Number} studentId - The student ID
   * @returns {Promise<Array>} - Array of assignment objects
   */
  static async findByStudent(studentId) {
    try {
      const query = `
        SELECT a.* FROM assignments a
        JOIN courses c ON a.course_id = c.id
        JOIN enrollments e ON c.id = e.course_id
        WHERE e.student_id = ? AND e.status = 'active'
      `;
      
      const [rows] = await pool.query(query, [studentId]);
      return rows;
    } catch (error) {
      console.error('Error fetching student assignments:', error);
      throw error;
    }
  }
}

/**
 * Submission model for MySQL database
 */
class Submission {
  /**
   * Create a new submission
   * @param {Object} submissionData - The submission data
   * @returns {Promise<Object>} - The created submission
   */
  static async create(submissionData) {
    try {
      const [result] = await pool.query(
        `INSERT INTO assignment_submissions (
          assignment_id, student_id, content, 
          submission_date, status
        ) VALUES (?, ?, ?, ?, ?)`,
        [
          submissionData.assignmentId,
          submissionData.student,
          submissionData.submissionContent,
          new Date(),
          'submitted'
        ]
      );
      
      return { id: result.insertId, ...submissionData };
    } catch (error) {
      console.error('Error creating submission:', error);
      throw error;
    }
  }

  /**
   * Get all submissions for an assignment
   * @param {Number} assignmentId - The assignment ID
   * @returns {Promise<Array>} - Array of submission objects
   */
  static async findByAssignment(assignmentId) {
    try {
      const [rows] = await pool.query(
        'SELECT * FROM assignment_submissions WHERE assignment_id = ?',
        [assignmentId]
      );
      return rows;
    } catch (error) {
      console.error('Error fetching submissions:', error);
      throw error;
    }
  }

  /**
   * Grade a submission
   * @param {Number} id - The submission ID
   * @param {Object} gradeData - The grade data
   * @returns {Promise<Boolean>} - Whether the grading was successful
   */
  static async grade(id, gradeData) {
    try {
      const [result] = await pool.query(
        `UPDATE assignment_submissions 
         SET points_earned = ?, feedback = ?, graded_by = ?, status = 'graded', graded_at = NOW() 
         WHERE id = ?`,
        [
          gradeData.grade,
          gradeData.feedback,
          gradeData.gradedBy,
          id
        ]
      );
      
      return result.affectedRows > 0;
    } catch (error) {
      console.error('Error grading submission:', error);
      throw error;
    }
  }
}

module.exports = {
  Assignment,
  Submission
};