const bcrypt = require('bcryptjs');
const { pool } = require('../config/db.config');

/**
 * User model for MySQL database
 */

class User {
  /**
   * Get a user by ID
   * @param {Number} id - The user ID
   * @returns {Promise<Object>} - The user object
   */
  static async findById(id) {
    try {
      const [rows] = await pool.query('SELECT * FROM users WHERE id = ?', [id]);
      return rows[0] || null;
    } catch (error) {
      console.error('Error fetching user by ID:', error);
      throw error;
    }
  }

  /**
   * Get a user by email
   * @param {String} email - The user email
   * @returns {Promise<Object>} - The user object
   */
  static async findByEmail(email) {
    try {
      const [rows] = await pool.query('SELECT * FROM users WHERE email = ?', [email]);
      return rows[0] || null;
    } catch (error) {
      console.error('Error fetching user by email:', error);
      throw error;
    }
  }

  /**
   * Create a new user
   * @param {Object} userData - The user data
   * @returns {Promise<Object>} - The created user
   */
  static async create(userData) {
    try {
      // Hash the password
      const hashedPassword = await hashPassword(userData.password);
      
      // Set role_id based on role
      let roleId = 3; // Default to student (role_id = 3)
      if (userData.role === 'admin') roleId = 1;
      if (userData.role === 'teacher') roleId = 2;
      if (userData.role === 'parent') roleId = 4;
      
      const [result] = await pool.query(
        `INSERT INTO users (
          role_id, email, password, first_name, last_name, 
          phone, grade_level_id, parent_id, avatar
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)`,
        [
          roleId,
          userData.email,
          hashedPassword,
          userData.firstName,
          userData.lastName,
          userData.phone || null,
          userData.grade || null,
          userData.parentId || null,
          userData.profilePicture || null
        ]
      );
      
      return { id: result.insertId, ...userData, password: undefined };
    } catch (error) {
      console.error('Error creating user:', error);
      throw error;
    }
  }

  /**
   * Update a user
   * @param {Number} id - The user ID
   * @param {Object} userData - The updated user data
   * @returns {Promise<Object>} - The updated user
   */
  static async update(id, userData) {
    try {
      const updateFields = [];
      const updateValues = [];
      
      // Build dynamic update query
      if (userData.firstName) {
        updateFields.push('first_name = ?');
        updateValues.push(userData.firstName);
      }
      
      if (userData.lastName) {
        updateFields.push('last_name = ?');
        updateValues.push(userData.lastName);
      }
      
      if (userData.email) {
        updateFields.push('email = ?');
        updateValues.push(userData.email);
      }
      
      if (userData.password) {
        const hashedPassword = await hashPassword(userData.password);
        updateFields.push('password = ?');
        updateValues.push(hashedPassword);
      }
      
      if (userData.phone) {
        updateFields.push('phone = ?');
        updateValues.push(userData.phone);
      }
      
      if (userData.grade) {
        updateFields.push('grade_level_id = ?');
        updateValues.push(userData.grade);
      }
      
      if (userData.profilePicture) {
        updateFields.push('avatar = ?');
        updateValues.push(userData.profilePicture);
      }
      
      // Add user ID to values array for WHERE clause
      updateValues.push(id);
      
      const [result] = await pool.query(
        `UPDATE users SET ${updateFields.join(', ')}, updated_at = NOW() WHERE id = ?`,
        updateValues
      );
      
      return result.affectedRows > 0;
    } catch (error) {
      console.error('Error updating user:', error);
      throw error;
    }
  }

  /**
   * Delete a user
   * @param {Number} id - The user ID
   * @returns {Promise<Boolean>} - Whether the user was deleted
   */
  static async delete(id) {
    try {
      const [result] = await pool.query('DELETE FROM users WHERE id = ?', [id]);
      return result.affectedRows > 0;
    } catch (error) {
      console.error('Error deleting user:', error);
      throw error;
    }
  }

  /**
   * Get all users
   * @param {Object} filters - Optional filters
   * @returns {Promise<Array>} - Array of user objects
   */
  static async findAll(filters = {}) {
    try {
      let query = 'SELECT * FROM users';
      const whereConditions = [];
      const queryParams = [];
      
      if (filters.role) {
        // Convert role name to role_id
        let roleId = 3; // Default to student
        if (filters.role === 'admin') roleId = 1;
        if (filters.role === 'teacher') roleId = 2;
        if (filters.role === 'parent') roleId = 4;
        
        whereConditions.push('role_id = ?');
        queryParams.push(roleId);
      }
      
      if (whereConditions.length > 0) {
        query += ' WHERE ' + whereConditions.join(' AND ');
      }
      
      const [rows] = await pool.query(query, queryParams);
      return rows;
    } catch (error) {
      console.error('Error fetching users:', error);
      throw error;
    }
  }
}

/**
 * Hash a password using bcrypt
 * @param {String} password - The plain text password
 * @returns {Promise<String>} - The hashed password
 */
const hashPassword = async (password) => {
  const salt = await bcrypt.genSalt(10);
  return await bcrypt.hash(password, salt);
};

/**
 * Compare a password with a hashed password
 * @param {String} password - The plain text password
 * @param {String} hashedPassword - The hashed password
 * @returns {Promise<Boolean>} - Whether the passwords match
 */
const comparePassword = async (password, hashedPassword) => {
  return await bcrypt.compare(password, hashedPassword);
};

module.exports = {
  User,
  hashPassword,
  comparePassword
};