/**
 * Database Management Routes
 * Routes for database administration
 */
const express = require('express');
const router = express.Router();
const databaseController = require('../controllers/databaseController');
const { authenticateToken, requireAdmin } = require('../middleware/auth');

// Apply admin authentication to all routes
router.use(authenticateToken, requireAdmin);

// Get all tables
router.get('/tables', databaseController.getAllTables);

// Create a new table
router.post('/tables', databaseController.createTable);

// Get data for a table
router.get('/tables/:tableName/data', databaseController.getTableData);

// Add data to a table
router.post('/tables/:tableName/data', databaseController.addTableData);

// Update data in a table
router.put('/tables/:tableName/data/:id', databaseController.updateTableData);

// Delete data from a table
router.delete('/tables/:tableName/data/:id', databaseController.deleteTableData);

// Delete a table
router.delete('/tables/:tableName', databaseController.deleteTable);

// Get all database files
router.get('/files', databaseController.getAllFiles);

// Get files from a specific directory
router.get('/files/:directory', databaseController.getDirectoryFiles);

module.exports = router; 