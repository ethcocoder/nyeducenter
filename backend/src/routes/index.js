const express = require('express');
const authRoutes = require('./auth');
const tableRoutes = require('./tables');
const dataRoutes = require('./data');
const userRoutes = require('./users');

const router = express.Router();

// Health check endpoint
router.get('/health', (req, res) => {
  res.status(200).json({ status: 'OK', message: 'API is running' });
});

// Register routes
router.use('/auth', authRoutes);
router.use('/tables', tableRoutes);
router.use('/data', dataRoutes);
router.use('/users', userRoutes);

module.exports = router; 