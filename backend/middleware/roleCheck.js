// Middleware to check user role
module.exports = function(allowedRoles) {
  return function(req, res, next) {
    // Convert to array if single role was passed
    const roles = Array.isArray(allowedRoles) ? allowedRoles : [allowedRoles];
    
    if (!roles.includes(req.user.role)) {
      return res.status(403).json({ msg: 'Access denied: insufficient permissions' });
    }
    next();
  };
};