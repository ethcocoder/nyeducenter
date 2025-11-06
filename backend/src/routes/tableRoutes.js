const express = require('express');
const router = express.Router();
const tableController = require('../controllers/tableController');
const { authenticateToken } = require('../middleware/auth');

// Table operations
router.get('/', authenticateToken, tableController.getAllTables);
router.post('/', authenticateToken, tableController.createTable);
router.delete('/:name', authenticateToken, tableController.deleteTable);

// Table data operations
router.get('/:name/data', authenticateToken, tableController.getTableData);
router.post('/:name/data', authenticateToken, tableController.addTableData);
router.put('/:name/data/:id', authenticateToken, tableController.updateTableData);
router.delete('/:name/data/:id', authenticateToken, tableController.deleteTableData);

module.exports = router; 