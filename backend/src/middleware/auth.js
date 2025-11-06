const jwt = require('jsonwebtoken');
const { findOneRecord } = require('../utils/db');

/**
 * Middleware to verify JWT token and attach user to request
 * @param {Object} req - Express request object
 * @param {Object} res - Express response object
 * @param {Function} next - Express next function
 */
function authenticateToken(req, res, next) {
  // Get the authorization header
  const authHeader = req.headers['authorization'];
  const token = authHeader && authHeader.split(' ')[1]; // Bearer TOKEN format
  
  if (!token) {
    return res.status(401).json({ error: 'Access token is required' });
  }
  
  try {
    // Verify the token
    const decoded = jwt.verify(token, process.env.JWT_SECRET);
    
    // Find the user in the database
    const user = findOneRecord('users', { id: decoded.userId });
    
    if (!user) {
      return res.status(403).json({ error: 'Invalid token - user not found' });
    }
    
    // Attach user to request
    req.user = {
      id: user.id,
      username: user.username,
      role: user.role
    };
    
    next();
  } catch (error) {
    return res.status(403).json({ error: 'Invalid or expired token' });
  }
}

/**
 * Middleware to check if user has admin role
 * @param {Object} req - Express request object
 * @param {Object} res - Express response object
 * @param {Function} next - Express next function
 */
function requireAdmin(req, res, next) {
  if (!req.user || req.user.role !== 'admin') {
    return res.status(403).json({ error: 'Admin access required' });
  }
  next();
}

/**
 * Middleware to check if user has teacher role
 * @param {Object} req - Express request object
 * @param {Object} res - Express response object
 * @param {Function} next - Express next function
 */
function requireTeacher(req, res, next) {
  if (!req.user || (req.user.role !== 'teacher' && req.user.role !== 'admin')) {
    return res.status(403).json({ error: 'Teacher access required' });
  }
  next();
}

/**
 * Middleware to check if user is the requested student or has higher privileges
 * @param {Object} req - Express request object
 * @param {Object} res - Express response object
 * @param {Function} next - Express next function
 */
function requireSelfOrHigher(req, res, next) {
  const requestedUserId = req.params.userId || req.body.userId;
  
  if (!req.user) {
    return res.status(403).json({ error: 'Authentication required' });
  }
  
  // Admin can access everything
  if (req.user.role === 'admin') {
    return next();
  }
  
  // Teacher can access student data but not other teachers
  if (req.user.role === 'teacher') {
    const requestedUser = requestedUserId && findOneRecord('users', { id: requestedUserId });
    if (requestedUser && requestedUser.role === 'teacher' && requestedUser.id !== req.user.id) {
      return res.status(403).json({ error: 'Access denied to other teacher\'s data' });
    }
    return next();
  }
  
  // Students can only access their own data
  if (req.user.role === 'student' && requestedUserId && req.user.id !== requestedUserId) {
    return res.status(403).json({ error: 'Access denied to other user\'s data' });
  }
  
  next();
}

module.exports = {
  authenticateToken,
  requireAdmin,
  requireTeacher,
  requireSelfOrHigher
}; 