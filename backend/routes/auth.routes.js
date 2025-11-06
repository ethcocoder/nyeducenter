const express = require('express');
const router = express.Router();
const { check } = require('express-validator');
const authController = require('../controllers/auth.controller');
const auth = require('../middleware/auth');

// @route   POST api/auth/register
// @desc    Register a user
// @access  Public
router.post(
  '/register',
  [
    check('firstName', 'First name is required').not().isEmpty(),
    check('lastName', 'Last name is required').not().isEmpty(),
    check('email', 'Please include a valid email').isEmail(),
    check('password', 'Please enter a password with 6 or more characters').isLength({ min: 6 })
  ],
  authController.register
);

// @route   POST api/auth/login
// @desc    Authenticate user & get token
// @access  Public
router.post(
  '/login',
  [
    check('email', 'Please include a valid email').isEmail(),
    check('password', 'Password is required').exists()
  ],
  authController.login
);

// @route   GET api/auth/user
// @desc    Get logged in user
// @access  Private
router.get('/user', auth, authController.getCurrentUser);

module.exports = router;

// ====== REMOVE EVERYTHING BELOW THIS LINE ======
// The following server configuration code should only exist in server.js