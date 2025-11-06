const { User, hashPassword, comparePassword } = require('../models/User');
const jwt = require('jsonwebtoken');
const { validationResult } = require('express-validator');

// Register a new user
exports.register = async (req, res) => {
  const errors = validationResult(req);
  if (!errors.isEmpty()) {
    return res.status(400).json({ errors: errors.array() });
  }

  const { firstName, lastName, email, password, role, grade, parentId, preferredLanguage } = req.body;

  try {
    // Check if user already exists
    const existingUser = await User.findByEmail(email);
    if (existingUser) {
      return res.status(400).json({ msg: 'User already exists' });
    }

    // Create new user
    const userData = {
      firstName,
      lastName,
      email,
      password,
      role: role || 'student',
      grade,
      parentId,
      preferredLanguage
    };

    const user = await User.create(userData);

    // Map role_id to role name for response
    let roleString = 'student'; // Default
    if (user.role_id === 1) roleString = 'admin';
    if (user.role_id === 2) roleString = 'teacher';
    if (user.role_id === 4) roleString = 'parent';

    // Prepare user response object without password
    const userResponse = {
      id: user.id,
      firstName: userData.firstName,
      lastName: userData.lastName,
      email: userData.email,
      role: roleString,
      grade: userData.grade
    };

    // Create JWT token
    const payload = {
      user: {
        id: user.id,
        role: roleString
      }
    };

    jwt.sign(
      payload,
      process.env.JWT_SECRET || 'jwtsecret123',
      { expiresIn: '24h' },
      (err, token) => {
        if (err) throw err;
        res.json({ token, user: userResponse });
      }
    );
  } catch (err) {
    console.error(err.message);
    res.status(500).send('Server error');
  }
};

// Login user
exports.login = async (req, res) => {
  const errors = validationResult(req);
  if (!errors.isEmpty()) {
    return res.status(400).json({ errors: errors.array() });
  }

  const { email, password, adminLogin } = req.body;

  try {
    // Check if user exists
    const user = await User.findByEmail(email);
    if (!user) {
      return res.status(400).json({ msg: 'Invalid credentials' });
    }

    // Check password
    const isMatch = await comparePassword(password, user.password);
    if (!isMatch) {
      return res.status(400).json({ msg: 'Invalid credentials' });
    }

    // If adminLogin is true, check if user is in admin table
    if (adminLogin) {
      const db = require('../config/db.config').pool;
      const [adminRows] = await db.query('SELECT * FROM admin WHERE user_id = ?', [user.id]);
      if (adminRows.length === 0) {
        return res.status(403).json({ msg: 'Access denied: not an admin user' });
      }
    }

    // Map role_id to role name
    let role = 'student'; // Default
    if (user.role_id === 1) role = 'admin';
    if (user.role_id === 2) role = 'teacher';
    if (user.role_id === 4) role = 'parent';

    // Prepare user response object without password
    const userResponse = {
      id: user.id,
      firstName: user.first_name,
      lastName: user.last_name,
      email: user.email,
      role: role,
      grade: user.grade_level_id,
      avatar: user.avatar
    };

    // Create and return JWT token
    const payload = {
      user: {
        id: user.id,
        role: role
      }
    };

    jwt.sign(
      payload,
      process.env.JWT_SECRET || 'jwtsecret123',
      { expiresIn: '24h' },
      (err, token) => {
        if (err) throw err;
        res.json({ token, user: userResponse });
      }
    );
  } catch (err) {
    console.error(err.message);
    res.status(500).send('Server error');
  }
};

// Get current user
exports.getCurrentUser = async (req, res) => {
  try {
    const user = await User.findById(req.user.id);
    if (!user) {
      return res.status(404).json({ msg: 'User not found' });
    }
    
    // Map MySQL field names to API response field names and exclude password
    const userResponse = {
      id: user.id,
      firstName: user.first_name,
      lastName: user.last_name,
      email: user.email,
      role: user.role_id === 1 ? 'admin' : 
            user.role_id === 2 ? 'teacher' : 
            user.role_id === 4 ? 'parent' : 'student',
      grade: user.grade_level_id,
      parentId: user.parent_id,
      avatar: user.avatar
    };
    
    res.json(userResponse);
  } catch (err) {
    console.error(err.message);
    res.status(500).send('Server error');
  }
};