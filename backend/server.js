const express = require('express');
const cors = require('cors');
const fs = require('fs');
const path = require('path');
const i18next = require('i18next');
const i18nextMiddleware = require('i18next-http-middleware');
const Backend = require('i18next-fs-backend');
require('dotenv').config();

// Database imports
const { pool } = require('./config/db.config');
const { initDatabase, migrateJsonToMysql } = require('./utils/initDatabase');

const app = express();

// Initialize i18next
i18next
  .use(Backend)
  .use(i18nextMiddleware.LanguageDetector)
  .init({
    backend: {
      loadPath: './locales/{{lng}}.json',
    },
    fallbackLng: 'en',
    preload: ['en', 'am', 'ti', 'or'],
  });

// Middleware
app.use(cors());
app.use(express.json());
app.use(i18nextMiddleware.handle(i18next));

// Import routes
const authRoutes = require('./routes/auth.routes');
const userRoutes = require('./routes/user.routes');
const courseRoutes = require('./routes/course.routes');
const contentRoutes = require('./routes/content.routes');
const assignmentRoutes = require('./routes/assignment.routes');
const gradeRoutes = require('./routes/grade.routes');
const communicationRoutes = require('./routes/communication.routes');
const quizRoutes = require('./routes/quiz.routes');
const calendarRoutes = require('./routes/calendar.routes');
const registrationRequestsRoutes = require('./routes/registrationRequests.routes');
const adminRoutes = require('./routes/admin.routes');
const messageRoutes = require('./routes/message.routes');

// Use routes
app.use('/api/auth', authRoutes);
app.use('/api/users', userRoutes);
app.use('/api/courses', courseRoutes);
app.use('/api/content', contentRoutes);
app.use('/api/assignments', assignmentRoutes);
app.use('/api/grades', gradeRoutes);
app.use('/api/communication', communicationRoutes);
app.use('/api/quizzes', quizRoutes);
app.use('/api/calendar', calendarRoutes);
app.use('/api/registration-requests', registrationRequestsRoutes);
app.use('/api/admin/registration-requests', registrationRequestsRoutes);
app.use('/api/admin', adminRoutes);
app.use('/api/messages', messageRoutes);

const PORT = process.env.PORT || 5001;
const HOST = '0.0.0.0';

// Home route
app.get('/', (req, res) => {
  res.json({ 
    message: 'EduN Backend API', 
    version: '1.0.0',
    endpoints: {
      auth: '/api/auth',
      users: '/api/users',
      courses: '/api/courses'
    },
    storage: 'MySQL Database'
  });
});

// Initialize database and start server
const startServer = async () => {
  try {
    // Test database connection
    let connected = false;
    try {
      const connection = await pool.getConnection();
      console.log('MySQL database connected successfully');
      connection.release();
      connected = true;
    } catch (error) {
      console.error('Database connection failed:', error.message);
      connected = false;
    }
    if (!connected) {
      console.error('Failed to connect to MySQL database. Please make sure XAMPP is running and MySQL service is started.');
      process.exit(1);
    }
    
    // Initialize database schema only if tables don't exist
    const [tables] = await pool.query('SHOW TABLES');
    if (tables.length === 0) {
      console.log('No tables found. Initializing database...');
      await initDatabase();
    } else {
      console.log('Database tables already exist. Skipping initialization.');
    }
    
    // Check if we need to migrate data from JSON
    const dataDir = path.join(__dirname, 'data');
    const jsonFilesExist = fs.existsSync(dataDir);
    
    if (jsonFilesExist) {
      const shouldMigrate = process.env.MIGRATE_JSON_TO_MYSQL === 'true';
      
      if (shouldMigrate) {
        console.log('Starting data migration from JSON to MySQL...');
        await migrateJsonToMysql();
      }
    }
    
    // Start Express server
    app.listen(PORT, HOST, () => {
      console.log(`Server is running on http://${HOST}:${PORT}`);
      console.log('Using MySQL database via XAMPP');
    });
  } catch (error) {
    console.error('Server initialization error:', error);
    process.exit(1);
  }
};

// Import and run database setup
const setupDatabase = require('./scripts/setup-database');

// Start the server with database setup
(async () => {
  try {
    await setupDatabase();
    await startServer();
  } catch (error) {
    console.error('Failed to start server:', error);
    process.exit(1);
  }
})();