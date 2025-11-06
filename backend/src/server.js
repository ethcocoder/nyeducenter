/**
 * Main Server Entry Point
 */
const express = require('express');
const cors = require('cors');
const morgan = require('morgan');
const path = require('path');
const dotenv = require('dotenv');

// Load environment variables
dotenv.config();

// Initialize database
const db = require('./utils/db');
db.initializeDatabase();

// Create Express app
const app = express();

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(morgan('dev'));

// Set port
const PORT = process.env.PORT || 5000;

// Routes
const authRoutes = require('./routes/auth');
const courseRoutes = require('./routes/course.routes');
const debugRoutes = require('./routes/debugRoutes');
// Import other routes here

// API endpoints
app.use('/api/auth', authRoutes);
app.use('/api/courses', courseRoutes);
app.use('/debug', debugRoutes);
// Add other routes here

// Serve static files (e.g., uploaded images)
app.use('/uploads', express.static(path.join(__dirname, '../uploads')));

// Error handling middleware
app.use((err, req, res, next) => {
  console.error(err.stack);
  res.status(500).json({
    success: false,
    message: 'Server error',
    error: process.env.NODE_ENV === 'production' ? 'An unexpected error occurred' : err.message
  });
});

// Start server
app.listen(PORT, () => {
  console.log(`Server running on port ${PORT}`);
  console.log(`API available at http://localhost:${PORT}/api`);
});

// Handle unhandled promise rejections
process.on('unhandledRejection', (err) => {
  console.error('Unhandled Promise Rejection:', err);
  // Don't crash the server in production
  if (process.env.NODE_ENV !== 'production') {
    process.exit(1);
  }
}); 