const { pool } = require('../config/db.config');

/**
 * Quiz model for MySQL database
 */

class Quiz {
  /**
   * Get a quiz by ID
   * @param {Number} id - The quiz ID
   * @returns {Promise<Object>} - The quiz object
   */
  static async findById(id) {
    try {
      const [quizRows] = await pool.query('SELECT * FROM quizzes WHERE id = ?', [id]);
      
      if (!quizRows[0]) return null;
      
      const quiz = quizRows[0];
      
      // Get questions for this quiz
      const [questionRows] = await pool.query('SELECT * FROM quiz_questions WHERE quiz_id = ?', [id]);
      quiz.questions = questionRows;
      
      // Get options for each question
      for (const question of quiz.questions) {
        const [optionRows] = await pool.query(
          'SELECT * FROM question_options WHERE question_id = ?',
          [question.id]
        );
        question.options = optionRows;
      }
      
      return quiz;
    } catch (error) {
      console.error('Error fetching quiz by ID:', error);
      throw error;
    }
  }

  /**
   * Create a new quiz
   * @param {Object} quizData - The quiz data
   * @returns {Promise<Object>} - The created quiz
   */
  static async create(quizData) {
    try {
      // Start a transaction
      const connection = await pool.getConnection();
      await connection.beginTransaction();
      
      try {
        // Insert quiz
        const [quizResult] = await connection.query(
          `INSERT INTO quizzes (
            title, description, course_id, 
            time_limit, available_from, available_to, 
            created_by, is_published
          ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)`,
          [
            quizData.title,
            quizData.description,
            quizData.course,
            quizData.timeLimit || 30,
            quizData.availableFrom ? new Date(quizData.availableFrom) : new Date(),
            quizData.availableTo ? new Date(quizData.availableTo) : null,
            quizData.createdBy,
            quizData.isPublished || false
          ]
        );
        
        const quizId = quizResult.insertId;
        
        // Insert questions
        if (quizData.questions && Array.isArray(quizData.questions)) {
          for (const question of quizData.questions) {
            const [questionResult] = await connection.query(
              `INSERT INTO quiz_questions (
                quiz_id, question_text, question_type, 
                correct_answer, points
              ) VALUES (?, ?, ?, ?, ?)`,
              [
                quizId,
                question.questionText,
                question.questionType,
                question.correctAnswer || null,
                question.points || 1
              ]
            );
            
            const questionId = questionResult.insertId;
            
            // Insert options for multiple choice questions
            if (question.questionType === 'multiple-choice' && Array.isArray(question.options)) {
              for (const option of question.options) {
                await connection.query(
                  `INSERT INTO question_options (
                    question_id, option_text, is_correct
                  ) VALUES (?, ?, ?)`,
                  [
                    questionId,
                    option.text,
                    option.isCorrect
                  ]
                );
              }
            }
          }
        }
        
        // Commit the transaction
        await connection.commit();
        
        return { id: quizId, ...quizData };
      } catch (error) {
        // Rollback in case of error
        await connection.rollback();
        throw error;
      } finally {
        connection.release();
      }
    } catch (error) {
      console.error('Error creating quiz:', error);
      throw error;
    }
  }

  /**
   * Update a quiz
   * @param {Number} id - The quiz ID
   * @param {Object} quizData - The updated quiz data
   * @returns {Promise<Boolean>} - Whether the update was successful
   */
  static async update(id, quizData) {
    try {
      const updateFields = [];
      const updateValues = [];
      
      if (quizData.title) {
        updateFields.push('title = ?');
        updateValues.push(quizData.title);
      }
      
      if (quizData.description !== undefined) {
        updateFields.push('description = ?');
        updateValues.push(quizData.description);
      }
      
      if (quizData.timeLimit) {
        updateFields.push('time_limit = ?');
        updateValues.push(quizData.timeLimit);
      }
      
      if (quizData.availableFrom) {
        updateFields.push('available_from = ?');
        updateValues.push(new Date(quizData.availableFrom));
      }
      
      if (quizData.availableTo) {
        updateFields.push('available_to = ?');
        updateValues.push(new Date(quizData.availableTo));
      }
      
      if (quizData.isPublished !== undefined) {
        updateFields.push('is_published = ?');
        updateValues.push(quizData.isPublished);
      }
      
      if (updateFields.length === 0) return true; // Nothing to update
      
      // Add quiz ID to values array for WHERE clause
      updateValues.push(id);
      
      const [result] = await pool.query(
        `UPDATE quizzes SET ${updateFields.join(', ')}, updated_at = NOW() WHERE id = ?`,
        updateValues
      );
      
      return result.affectedRows > 0;
    } catch (error) {
      console.error('Error updating quiz:', error);
      throw error;
    }
  }

  /**
   * Delete a quiz
   * @param {Number} id - The quiz ID
   * @returns {Promise<Boolean>} - Whether the deletion was successful
   */
  static async delete(id) {
    try {
      // Start a transaction
      const connection = await pool.getConnection();
      await connection.beginTransaction();
      
      try {
        // Get all questions for this quiz
        const [questionRows] = await connection.query(
          'SELECT id FROM quiz_questions WHERE quiz_id = ?',
          [id]
        );
        
        // Delete options for each question
        for (const question of questionRows) {
          await connection.query(
            'DELETE FROM question_options WHERE question_id = ?',
            [question.id]
          );
        }
        
        // Delete questions
        await connection.query(
          'DELETE FROM quiz_questions WHERE quiz_id = ?',
          [id]
        );
        
        // Delete attempts
        await connection.query(
          'DELETE FROM quiz_attempts WHERE quiz_id = ?',
          [id]
        );
        
        // Delete the quiz
        const [result] = await connection.query(
          'DELETE FROM quizzes WHERE id = ?',
          [id]
        );
        
        // Commit the transaction
        await connection.commit();
        
        return result.affectedRows > 0;
      } catch (error) {
        // Rollback in case of error
        await connection.rollback();
        throw error;
      } finally {
        connection.release();
      }
    } catch (error) {
      console.error('Error deleting quiz:', error);
      throw error;
    }
  }

  /**
   * Get all quizzes
   * @param {Object} filters - Optional filters like course, teacher
   * @returns {Promise<Array>} - Array of quiz objects
   */
  static async findAll(filters = {}) {
    try {
      let query = 'SELECT * FROM quizzes';
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
      
      if (filters.isPublished !== undefined) {
        whereConditions.push('is_published = ?');
        queryParams.push(filters.isPublished);
      }
      
      if (whereConditions.length > 0) {
        query += ' WHERE ' + whereConditions.join(' AND ');
      }
      
      const [rows] = await pool.query(query, queryParams);
      return rows;
    } catch (error) {
      console.error('Error fetching quizzes:', error);
      throw error;
    }
  }

  /**
   * Get quizzes for a specific student (from courses they're enrolled in)
   * @param {Number} studentId - The student ID
   * @returns {Promise<Array>} - Array of quiz objects
   */
  static async findByStudent(studentId) {
    try {
      const query = `
        SELECT q.* FROM quizzes q
        JOIN courses c ON q.course_id = c.id
        JOIN enrollments e ON c.id = e.course_id
        WHERE e.student_id = ? AND e.status = 'active' AND q.is_published = true
      `;
      
      const [rows] = await pool.query(query, [studentId]);
      return rows;
    } catch (error) {
      console.error('Error fetching student quizzes:', error);
      throw error;
    }
  }
}

/**
 * Quiz Attempt model for MySQL database
 */
class QuizAttempt {
  /**
   * Create a new quiz attempt
   * @param {Object} attemptData - The attempt data
   * @returns {Promise<Object>} - The created attempt
   */
  static async create(attemptData) {
    try {
      // Start a transaction
      const connection = await pool.getConnection();
      await connection.beginTransaction();
      
      try {
        // Insert attempt
        const [attemptResult] = await connection.query(
          `INSERT INTO quiz_attempts (
            quiz_id, student_id, start_time, 
            status
          ) VALUES (?, ?, ?, ?)`,
          [
            attemptData.quizId,
            attemptData.student,
            new Date(),
            'in_progress'
          ]
        );
        
        const attemptId = attemptResult.insertId;
        
        // Commit the transaction
        await connection.commit();
        
        return { id: attemptId, ...attemptData };
      } catch (error) {
        // Rollback in case of error
        await connection.rollback();
        throw error;
      } finally {
        connection.release();
      }
    } catch (error) {
      console.error('Error creating quiz attempt:', error);
      throw error;
    }
  }

  /**
   * Submit a quiz attempt
   * @param {Number} id - The attempt ID
   * @param {Array} answers - The answers to submit
   * @returns {Promise<Object>} - The submitted attempt with results
   */
  static async submit(id, answers) {
    try {
      // Start a transaction
      const connection = await pool.getConnection();
      await connection.beginTransaction();
      
      try {
        // Get the attempt
        const [attemptRows] = await connection.query(
          'SELECT * FROM quiz_attempts WHERE id = ?',
          [id]
        );
        
        if (!attemptRows[0]) throw new Error('Attempt not found');
        
        const attempt = attemptRows[0];
        
        // Get the quiz
        const [quizRows] = await connection.query(
          'SELECT * FROM quizzes WHERE id = ?',
          [attempt.quiz_id]
        );
        
        if (!quizRows[0]) throw new Error('Quiz not found');
        
        // Process and save each answer
        let totalScore = 0;
        
        for (const answer of answers) {
          // Get the question
          const [questionRows] = await connection.query(
            'SELECT * FROM quiz_questions WHERE id = ?',
            [answer.questionId]
          );
          
          if (!questionRows[0]) continue;
          
          const question = questionRows[0];
          let isCorrect = false;
          let pointsEarned = 0;
          
          // Check if answer is correct based on question type
          if (question.question_type === 'multiple-choice') {
            // For multiple choice, need to check against the options
            const [optionRows] = await connection.query(
              'SELECT * FROM question_options WHERE id = ?',
              [answer.answer]
            );
            
            if (optionRows[0] && optionRows[0].is_correct) {
              isCorrect = true;
              pointsEarned = question.points;
            }
          } else if (question.question_type === 'true-false') {
            // For true-false, direct comparison
            if (answer.answer === question.correct_answer) {
              isCorrect = true;
              pointsEarned = question.points;
            }
          } else if (question.question_type === 'short-answer') {
            // For short-answer, case-insensitive comparison
            if (answer.answer.toLowerCase() === question.correct_answer.toLowerCase()) {
              isCorrect = true;
              pointsEarned = question.points;
            }
          }
          
          // Save the response
          await connection.query(
            `INSERT INTO quiz_responses (
              attempt_id, question_id, student_answer,
              is_correct, points_earned
            ) VALUES (?, ?, ?, ?, ?)`,
            [
              id,
              answer.questionId,
              answer.answer,
              isCorrect,
              pointsEarned
            ]
          );
          
          totalScore += pointsEarned;
        }
        
        // Update the attempt
        await connection.query(
          `UPDATE quiz_attempts 
           SET status = 'completed', end_time = NOW(), 
           total_score = ? 
           WHERE id = ?`,
          [
            totalScore,
            id
          ]
        );
        
        // Commit the transaction
        await connection.commit();
        
        return { id, totalScore, submittedAt: new Date() };
      } catch (error) {
        // Rollback in case of error
        await connection.rollback();
        throw error;
      } finally {
        connection.release();
      }
    } catch (error) {
      console.error('Error submitting quiz attempt:', error);
      throw error;
    }
  }

  /**
   * Get attempts for a specific quiz
   * @param {Number} quizId - The quiz ID
   * @returns {Promise<Array>} - Array of attempt objects
   */
  static async findByQuiz(quizId) {
    try {
      const [rows] = await pool.query(
        'SELECT * FROM quiz_attempts WHERE quiz_id = ?',
        [quizId]
      );
      return rows;
    } catch (error) {
      console.error('Error fetching quiz attempts:', error);
      throw error;
    }
  }

  /**
   * Get attempts for a specific student
   * @param {Number} studentId - The student ID
   * @returns {Promise<Array>} - Array of attempt objects
   */
  static async findByStudent(studentId) {
    try {
      const [rows] = await pool.query(
        'SELECT * FROM quiz_attempts WHERE student_id = ?',
        [studentId]
      );
      return rows;
    } catch (error) {
      console.error('Error fetching student quiz attempts:', error);
      throw error;
    }
  }
}

module.exports = {
  Quiz,
  QuizAttempt
};