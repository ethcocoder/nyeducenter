const jwt = require('jsonwebtoken');
const { findOneRecord } = require('../utils/db');
require('dotenv').config();

/**
 * Authentication middleware
 * Verifies JWT token in the Authorization header
 * Sets req.user with the decoded token payload
 */
const authMiddleware = (req, res, next) => {
  // Get token from Authorization header
  const authHeader = req.headers.authorization;
  
  if (!authHeader || !authHeader.startsWith('Bearer ')) {
    return res.status(401).json({ message: 'Access denied. No token provided' });
  }

  const token = authHeader.split(' ')[1];
  
  try {
    // Verify token
    const decoded = jwt.verify(token, process.env.JWT_SECRET);
    
    // Set user info on request object
    req.user = decoded;
    
    next();
  } catch (error) {
    res.status(401).json({ message: 'Invalid token', error: error.message });
  }
};

/**
 * Middleware to restrict routes to admin users only
 */
const requireAdmin = (req, res, next) => {
  if (!req.user) {
    return res.status(401).json({ message: 'Authentication required' });
  }
  
  if (req.user.role !== 'admin') {
    return res.status(403).json({ message: 'Access denied. Admin privileges required' });
  }
  
  next();
};

/**
 * Middleware to restrict routes to teacher users only
 */
const requireTeacher = (req, res, next) => {
  if (!req.user) {
    return res.status(401).json({ message: 'Authentication required' });
  }
  
  if (req.user.role !== 'teacher') {
    return res.status(403).json({ message: 'Access denied. Teacher privileges required' });
  }
  
  next();
};

/**
 * Student role check middleware
 */
const requireStudent = (req, res, next) => {
  if (!req.user || (req.user.role !== 'student' && req.user.role !== 'admin')) {
    return res.status(403).json({ message: 'Student access required' });
  }
  next();
};

module.exports = {
  authMiddleware,
  requireAdmin,
  requireTeacher,
  requireStudent
}; 