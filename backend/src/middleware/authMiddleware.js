const jwt = require('jsonwebtoken');
const { findOneRecord } = require('../utils/database');

/**
 * Middleware to authenticate JWT token
 */
const authenticateToken = (req, res, next) => {
    const authHeader = req.headers['authorization'];
    const token = authHeader && authHeader.split(' ')[1]; // Bearer TOKEN format
    
    if (!token) {
        return res.status(401).json({ error: 'No token provided' });
    }
    
    jwt.verify(token, process.env.JWT_SECRET, (err, user) => {
        if (err) {
            return res.status(403).json({ error: 'Invalid or expired token' });
        }
        
        req.user = user;
        next();
    });
};

/**
 * Middleware to check if user is an admin
 */
const authorizeAdmin = (req, res, next) => {
    if (!req.user) {
        return res.status(401).json({ error: 'Authentication required' });
    }
    
    if (req.user.role !== 'admin') {
        return res.status(403).json({ error: 'Admin access required' });
    }
    
    next();
};

/**
 * Middleware to check if user is either an admin or a teacher
 */
const authorizeTeacher = (req, res, next) => {
    if (!req.user) {
        return res.status(401).json({ error: 'Authentication required' });
    }
    
    if (req.user.role !== 'admin' && req.user.role !== 'teacher') {
        return res.status(403).json({ error: 'Teacher access required' });
    }
    
    next();
};

/**
 * Middleware to check if user owns a resource or is an admin
 */
const authorizeResourceAccess = (resourceGetter) => {
    return async (req, res, next) => {
        if (!req.user) {
            return res.status(401).json({ error: 'Authentication required' });
        }
        
        // Admins have access to all resources
        if (req.user.role === 'admin') {
            return next();
        }
        
        try {
            const resource = await resourceGetter(req);
            
            // If resource doesn't exist
            if (!resource) {
                return res.status(404).json({ error: 'Resource not found' });
            }
            
            // Check if user owns the resource
            if (resource.userId === req.user.id) {
                return next();
            }
            
            // If it's a teacher and checking for class resources
            if (req.user.role === 'teacher' && resource.grade === req.user.grade) {
                return next();
            }
            
            return res.status(403).json({ error: 'You do not have permission to access this resource' });
        } catch (error) {
            console.error('Authorization error:', error);
            return res.status(500).json({ error: 'Internal server error' });
        }
    };
};

// Authentication middleware
const authenticate = (req, res, next) => {
  try {
    // Get token from header
    const token = req.headers.authorization?.split(' ')[1];
    
    if (!token) {
      return res.status(401).json({ 
        success: false, 
        message: 'Access denied. No token provided.' 
      });
    }
    
    // Verify token
    const decoded = jwt.verify(
      token, 
      process.env.JWT_SECRET || 'your-super-secret-key-change-this-in-production'
    );
    
    // Add user info to request
    req.user = decoded;
    
    next();
  } catch (error) {
    console.error('Authentication error:', error);
    res.status(401).json({ 
      success: false, 
      message: 'Invalid token.' 
    });
  }
};

// Role-based access control middleware
const authorize = (roles = []) => {
  return (req, res, next) => {
    if (!req.user) {
      return res.status(401).json({ 
        success: false, 
        message: 'Access denied. User not authenticated.' 
      });
    }
    
    // Check if user's role is in the allowed roles
    if (!roles.includes(req.user.role)) {
      return res.status(403).json({ 
        success: false, 
        message: 'Access denied. Insufficient permissions.' 
      });
    }
    
    next();
  };
};

// Grade access middleware to restrict access based on user role and grade
const gradeAccess = (req, res, next) => {
  try {
    const { role, grade } = req.params;
    
    // Admin can access any grade data
    if (req.user.role === 'admin') {
      return next();
    }
    
    // Teachers can access their own grade level data or student grade data they teach
    if (req.user.role === 'teacher') {
      if (role === 'teacher' && req.user.grade !== grade) {
        return res.status(403).json({
          success: false,
          message: 'Teachers can only access their own grade level data'
        });
      }
      
      // Allow teachers to access student data for grades they teach
      if (role === 'student' && req.user.grade !== grade) {
        return res.status(403).json({
          success: false,
          message: 'Teachers can only access student data for grades they teach'
        });
      }
      
      return next();
    }
    
    // Students can only access their own grade data
    if (req.user.role === 'student') {
      if (role !== 'student' || req.user.grade !== grade) {
        return res.status(403).json({
          success: false,
          message: 'Students can only access their own grade data'
        });
      }
      
      return next();
    }
    
    // Default deny
    return res.status(403).json({
      success: false,
      message: 'Access denied'
    });
  } catch (error) {
    console.error('Grade access error:', error);
    res.status(500).json({
      success: false,
      message: 'Error checking grade access',
      error: error.message
    });
  }
};

module.exports = {
    authenticateToken,
    authorizeAdmin,
    authorizeTeacher,
    authorizeResourceAccess,
    authenticate,
    authorize,
    gradeAccess
}; 