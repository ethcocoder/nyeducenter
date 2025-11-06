const express = require('express');
const router = express.Router();
const fs = require('fs').promises;
const path = require('path');
const { 
  ensureDbDir, 
  writeTable, 
  readTable, 
  listTables
} = require('../utils/db');
const { authenticate, requireAdmin } = require('../middlewares/auth');

// Use authentication for all table routes
router.use(authenticate);

/**
 * @route GET /api/tables
 * @desc List all available tables
 * @access Admin only
 */
router.get('/api/tables', requireAdmin, async (req, res) => {
  try {
    const tables = await listTables();
    res.json({ tables });
  } catch (error) {
    res.status(500).json({ message: 'Error listing tables', error: error.message });
  }
});

/**
 * @route POST /api/tables
 * @desc Create a new table
 * @access Admin only
 */
router.post('/api/tables', requireAdmin, async (req, res) => {
  try {
    const { name, schema } = req.body;
    
    // Validate input
    if (!name || typeof name !== 'string') {
      return res.status(400).json({ message: 'Table name is required and must be a string' });
    }
    
    if (!schema || typeof schema !== 'object') {
      return res.status(400).json({ message: 'Table schema is required and must be an object' });
    }
    
    // Check for required field 'id'
    if (!schema.id) {
      schema.id = { type: 'string', required: true };
    }
    
    // Check if table already exists
    const tables = await listTables();
    if (tables.includes(name)) {
      return res.status(400).json({ message: `Table '${name}' already exists` });
    }
    
    // Create table data file with empty array
    await writeTable(name, []);
    
    // Save table schema
    await ensureDbDir();
    const tablesSchemaPath = path.join(process.cwd(), 'database', 'tables.json');
    let tablesSchema = {};
    
    try {
      const tablesSchemaData = await fs.readFile(tablesSchemaPath, 'utf8');
      tablesSchema = JSON.parse(tablesSchemaData);
    } catch (error) {
      // If file doesn't exist, create it
      if (error.code === 'ENOENT') {
        tablesSchema = {};
      } else {
        throw error;
      }
    }
    
    // Add new table schema
    tablesSchema[name] = {
      schema,
      createdAt: new Date().toISOString(),
      createdBy: req.user.username,
      updatedAt: new Date().toISOString(),
      updatedBy: req.user.username
    };
    
    await fs.writeFile(tablesSchemaPath, JSON.stringify(tablesSchema, null, 2), 'utf8');
    
    res.status(201).json({ 
      message: `Table '${name}' created successfully`,
      table: { name, schema }
    });
  } catch (error) {
    res.status(500).json({ message: 'Error creating table', error: error.message });
  }
});

/**
 * @route GET /api/tables/:name
 * @desc Get table data
 * @access Admin only
 */
router.get('/api/tables/:name', requireAdmin, async (req, res) => {
  try {
    const { name } = req.params;
    
    // Check if table exists
    const tables = await listTables();
    if (!tables.includes(name)) {
      return res.status(404).json({ message: `Table '${name}' not found` });
    }
    
    const data = await readTable(name);
    res.json({ table: name, data });
  } catch (error) {
    res.status(500).json({ message: 'Error reading table', error: error.message });
  }
});

/**
 * @route DELETE /api/tables/:name
 * @desc Delete a table
 * @access Admin only
 */
router.delete('/api/tables/:name', requireAdmin, async (req, res) => {
  try {
    const { name } = req.params;
    
    // Check if table exists
    const tables = await listTables();
    if (!tables.includes(name)) {
      return res.status(404).json({ message: `Table '${name}' not found` });
    }
    
    // Don't allow users table to be deleted
    if (name === 'users') {
      return res.status(400).json({ message: "The 'users' table cannot be deleted" });
    }
    
    // Delete table data file
    const tablePath = path.join(process.cwd(), 'database', `${name}.json`);
    await fs.unlink(tablePath);
    
    // Remove table from schema
    const tablesSchemaPath = path.join(process.cwd(), 'database', 'tables.json');
    let tablesSchema = {};
    
    try {
      const tablesSchemaData = await fs.readFile(tablesSchemaPath, 'utf8');
      tablesSchema = JSON.parse(tablesSchemaData);
      
      // Delete table from schema
      delete tablesSchema[name];
      
      await fs.writeFile(tablesSchemaPath, JSON.stringify(tablesSchema, null, 2), 'utf8');
    } catch (error) {
      console.error('Error updating tables schema:', error);
    }
    
    res.json({ message: `Table '${name}' deleted successfully` });
  } catch (error) {
    res.status(500).json({ message: 'Error deleting table', error: error.message });
  }
});

module.exports = router; 