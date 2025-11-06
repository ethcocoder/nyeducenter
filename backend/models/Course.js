const { pool } = require('../config/db.config');

/**
 * Course model for MySQL database
 */

class Course {
  /**
   * Get a course by ID
   * @param {Number} id - The course ID
   * @returns {Promise<Object>} - The course object
   */
  static async findById(id) {
    try {
      const [rows] = await pool.query('SELECT * FROM courses WHERE id = ?', [id]);
      return rows[0] || null;
    } catch (error) {
      console.error('Error fetching course by ID:', error);
      throw error;
    }
  }

  /**
   * Create a new course
   * @param {Object} courseData - The course data
   * @returns {Promise<Object>} - The created course
   */
  static async create(courseData) {
    try {
      const [result] = await pool.query(
        `INSERT INTO courses (
          subject_id, grade_level_id, teacher_id, term_id, 
          name, code, description, syllabus
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)`,
        [
          courseData.subjectId,
          courseData.grade,
          courseData.teacher,
          courseData.termId || 1, // Default to first term if not specified
          courseData.title,
          courseData.code || `COURSE-${Date.now()}`, // Generate a code if not provided
          courseData.description,
          courseData.syllabus || null
        ]
      );
      
      return { id: result.insertId, ...courseData };
    } catch (error) {
      console.error('Error creating course:', error);
      throw error;
    }
  }

  /**
   * Update a course
   * @param {Number} id - The course ID
   * @param {Object} courseData - The updated course data
   * @returns {Promise<Boolean>} - Whether the update was successful
   */
  static async update(id, courseData) {
    try {
      const updateFields = [];
      const updateValues = [];
      
      if (courseData.title) {
        updateFields.push('name = ?');
        updateValues.push(courseData.title);
      }
      
      if (courseData.description) {
        updateFields.push('description = ?');
        updateValues.push(courseData.description);
      }
      
      if (courseData.grade) {
        updateFields.push('grade_level_id = ?');
        updateValues.push(courseData.grade);
      }
      
      if (courseData.subjectId) {
        updateFields.push('subject_id = ?');
        updateValues.push(courseData.subjectId);
      }
      
      if (courseData.teacher) {
        updateFields.push('teacher_id = ?');
        updateValues.push(courseData.teacher);
      }
      
      if (courseData.syllabus) {
        updateFields.push('syllabus = ?');
        updateValues.push(courseData.syllabus);
      }
      
      // Add course ID to values array for WHERE clause
      updateValues.push(id);
      
      const [result] = await pool.query(
        `UPDATE courses SET ${updateFields.join(', ')}, updated_at = NOW() WHERE id = ?`,
        updateValues
      );
      
      return result.affectedRows > 0;
    } catch (error) {
      console.error('Error updating course:', error);
      throw error;
    }
  }

  /**
   * Delete a course
   * @param {Number} id - The course ID
   * @returns {Promise<Boolean>} - Whether the deletion was successful
   */
  static async delete(id) {
    try {
      const [result] = await pool.query('DELETE FROM courses WHERE id = ?', [id]);
      return result.affectedRows > 0;
    } catch (error) {
      console.error('Error deleting course:', error);
      throw error;
    }
  }

  /**
   * Get all courses
   * @param {Object} filters - Optional filters like grade, teacher
   * @returns {Promise<Array>} - Array of course objects
   */
  static async findAll(filters = {}) {
    try {
      let query = 'SELECT * FROM courses';
      const whereConditions = [];
      const queryParams = [];
      
      if (filters.grade) {
        whereConditions.push('grade_level_id = ?');
        queryParams.push(filters.grade);
      }
      
      if (filters.teacher) {
        whereConditions.push('teacher_id = ?');
        queryParams.push(filters.teacher);
      }
      
      if (filters.subject) {
        whereConditions.push('subject_id = ?');
        queryParams.push(filters.subject);
      }
      
      if (whereConditions.length > 0) {
        query += ' WHERE ' + whereConditions.join(' AND ');
      }
      
      const [rows] = await pool.query(query, queryParams);
      return rows;
    } catch (error) {
      console.error('Error fetching courses:', error);
      throw error;
    }
  }

  /**
   * Get courses for a specific student (enrolled courses)
   * @param {Number} studentId - The student ID
   * @returns {Promise<Array>} - Array of course objects
   */
  static async findByStudent(studentId) {
    try {
      const query = `
        SELECT c.* FROM courses c
        JOIN enrollments e ON c.id = e.course_id
        WHERE e.student_id = ? AND e.status = 'active'
      `;
      
      const [rows] = await pool.query(query, [studentId]);
      return rows;
    } catch (error) {
      console.error('Error fetching student courses:', error);
      throw error;
    }
  }
}

module.exports = {
  Course
};