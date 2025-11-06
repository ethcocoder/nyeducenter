/**
 * Role-based authorization middleware
 */

// Middleware to authorize admin users
const authorizeAdmin = (req, res, next) => {
  if (req.user && req.user.role === 'admin') {
    return next();
  }
  
  return res.status(403).json({ error: 'Access denied. Admin privileges required.' });
};

// Middleware to authorize teacher users
const authorizeTeacher = (req, res, next) => {
  if (req.user && req.user.role === 'teacher') {
    return next();
  }
  
  return res.status(403).json({ error: 'Access denied. Teacher privileges required.' });
};

// Middleware to authorize student users
const authorizeStudent = (req, res, next) => {
  if (req.user && req.user.role === 'student') {
    return next();
  }
  
  return res.status(403).json({ error: 'Access denied. Student privileges required.' });
};

// Middleware to authorize both teachers and admins
const authorizeStaff = (req, res, next) => {
  if (req.user && (req.user.role === 'admin' || req.user.role === 'teacher')) {
    return next();
  }
  
  return res.status(403).json({ error: 'Access denied. Staff privileges required.' });
};

module.exports = {
  authorizeAdmin,
  authorizeTeacher,
  authorizeStudent,
  authorizeStaff
}; 