const express = require('express');
const router = express.Router();
const path = require('path');
const fs = require('fs').promises;
const { 
  readTable, 
  writeTable,
  listTables,
  generateId,
  createRecord,
  updateRecord,
  deleteRecord,
  findRecords,
  findOneRecord
} = require('../utils/db');
const { authenticate } = require('../middlewares/auth');

// Apply authentication middleware to all data routes
router.use(authenticate);

/**
 * @route GET /api/data/:table
 * @desc Get all records from a table, with optional filtering
 * @access Authenticated
 */
router.get('/api/data/:table', async (req, res) => {
  try {
    const { table } = req.params;
    const query = req.query;
    
    // Check if table exists
    const tables = await listTables();
    if (!tables.includes(table)) {
      return res.status(404).json({ message: `Table '${table}' not found` });
    }
    
    // Read table data
    const data = await readTable(table);
    
    // If there are query parameters, filter the results
    if (Object.keys(query).length > 0) {
      // Use db utility to find matching records
      const results = findRecords(data, query);
      return res.json({ table, count: results.length, data: results });
    }
    
    // Return all data if no query parameters
    res.json({ table, count: data.length, data });
  } catch (error) {
    res.status(500).json({ message: 'Error fetching data', error: error.message });
  }
});

/**
 * @route GET /api/data/:table/:id
 * @desc Get a single record by ID
 * @access Authenticated
 */
router.get('/api/data/:table/:id', async (req, res) => {
  try {
    const { table, id } = req.params;
    
    // Check if table exists
    const tables = await listTables();
    if (!tables.includes(table)) {
      return res.status(404).json({ message: `Table '${table}' not found` });
    }
    
    // Read table data
    const data = await readTable(table);
    
    // Find record by ID
    const record = data.find(item => item.id === id);
    
    if (!record) {
      return res.status(404).json({ message: `Record with ID '${id}' not found in table '${table}'` });
    }
    
    res.json({ table, data: record });
  } catch (error) {
    res.status(500).json({ message: 'Error fetching record', error: error.message });
  }
});

/**
 * @route POST /api/data/:table
 * @desc Create a new record
 * @access Authenticated
 */
router.post('/api/data/:table', async (req, res) => {
  try {
    const { table } = req.params;
    const recordData = req.body;
    
    // Check if table exists
    const tables = await listTables();
    if (!tables.includes(table)) {
      return res.status(404).json({ message: `Table '${table}' not found` });
    }
    
    // Read table data
    const data = await readTable(table);
    
    // Get table schema
    const tablesSchemaPath = path.join(process.cwd(), 'database', 'tables.json');
    let tablesSchema = {};
    
    try {
      const tablesSchemaData = await fs.readFile(tablesSchemaPath, 'utf8');
      tablesSchema = JSON.parse(tablesSchemaData);
    } catch (error) {
      return res.status(500).json({ message: 'Error reading table schema', error: error.message });
    }
    
    // Validate data against schema
    const schema = tablesSchema[table]?.schema;
    if (!schema) {
      return res.status(500).json({ message: 'Schema not found for table' });
    }
    
    // Check required fields
    for (const [field, definition] of Object.entries(schema)) {
      if (definition.required && recordData[field] === undefined && field !== 'id') {
        return res.status(400).json({ message: `Field '${field}' is required` });
      }
    }
    
    // Generate ID if not provided
    if (!recordData.id) {
      recordData.id = generateId();
    }
    
    // Add timestamps
    recordData.createdAt = new Date().toISOString();
    recordData.updatedAt = new Date().toISOString();
    
    // Add user info if available
    if (req.user) {
      recordData.createdBy = req.user.username;
      recordData.updatedBy = req.user.username;
    }
    
    // Create the record
    const updatedData = [...data, recordData];
    await writeTable(table, updatedData);
    
    res.status(201).json({ 
      message: 'Record created successfully', 
      table, 
      data: recordData
    });
  } catch (error) {
    res.status(500).json({ message: 'Error creating record', error: error.message });
  }
});

/**
 * @route PUT /api/data/:table/:id
 * @desc Update an existing record
 * @access Authenticated
 */
router.put('/api/data/:table/:id', async (req, res) => {
  try {
    const { table, id } = req.params;
    const recordData = req.body;
    
    // Check if table exists
    const tables = await listTables();
    if (!tables.includes(table)) {
      return res.status(404).json({ message: `Table '${table}' not found` });
    }
    
    // Read table data
    const data = await readTable(table);
    
    // Find record by ID
    const recordIndex = data.findIndex(item => item.id === id);
    
    if (recordIndex === -1) {
      return res.status(404).json({ message: `Record with ID '${id}' not found in table '${table}'` });
    }
    
    // Don't allow ID to be changed
    if (recordData.id && recordData.id !== id) {
      return res.status(400).json({ message: 'Cannot change record ID' });
    }
    
    // Update the record
    const updatedRecord = {
      ...data[recordIndex],
      ...recordData,
      id, // Ensure ID stays the same
      updatedAt: new Date().toISOString(),
      updatedBy: req.user ? req.user.username : 'system'
    };
    
    data[recordIndex] = updatedRecord;
    await writeTable(table, data);
    
    res.json({ 
      message: 'Record updated successfully', 
      table, 
      data: updatedRecord
    });
  } catch (error) {
    res.status(500).json({ message: 'Error updating record', error: error.message });
  }
});

/**
 * @route DELETE /api/data/:table/:id
 * @desc Delete a record
 * @access Authenticated
 */
router.delete('/api/data/:table/:id', async (req, res) => {
  try {
    const { table, id } = req.params;
    
    // Check if table exists
    const tables = await listTables();
    if (!tables.includes(table)) {
      return res.status(404).json({ message: `Table '${table}' not found` });
    }
    
    // Read table data
    const data = await readTable(table);
    
    // Find record by ID
    const recordIndex = data.findIndex(item => item.id === id);
    
    if (recordIndex === -1) {
      return res.status(404).json({ message: `Record with ID '${id}' not found in table '${table}'` });
    }
    
    // Remove the record
    data.splice(recordIndex, 1);
    await writeTable(table, data);
    
    res.json({ 
      message: 'Record deleted successfully', 
      table, 
      id
    });
  } catch (error) {
    res.status(500).json({ message: 'Error deleting record', error: error.message });
  }
});

module.exports = router; 