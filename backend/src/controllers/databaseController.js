/**
 * Database Management Controller
 * Handles database administration requests
 */
const fs = require('fs').promises;
const path = require('path');
const { DB_DIR } = require('../config/database');
const { 
    listTables, 
    registerTable, 
    deleteTable, 
    findRecords, 
    createRecord, 
    updateRecord, 
    deleteRecord,
    getTablePath,
    getTableInfo
} = require('../utils/db');

const databaseController = {
    // Get all tables
    async getAllTables(req, res) {
        try {
            const tables = await listTables();
            res.json(tables);
        } catch (error) {
            console.error('Error getting tables:', error);
            res.status(500).json({ error: 'Failed to get tables' });
        }
    },
    
    // Create a new table
    async createTable(req, res) {
        try {
            const { name, fields } = req.body;
            
            if (!name || !fields || !Array.isArray(fields)) {
                return res.status(400).json({ error: 'Name and fields array are required' });
            }
            
            // Create schema from fields
            const schema = {
                type: 'object',
                required: fields.map(f => f.required ? f.name : null).filter(Boolean),
                properties: {}
            };
            
            // Add properties to schema
            fields.forEach(field => {
                schema.properties[field.name] = {
                    type: mapFieldTypeToSchemaType(field.type)
                };
            });
            
            // Register the table
            const result = registerTable(name, schema);
            
            if (!result.success) {
                return res.status(400).json({ error: result.message });
            }
            
            res.status(201).json({ message: result.message });
        } catch (error) {
            console.error('Error creating table:', error);
            res.status(500).json({ error: 'Failed to create table' });
        }
    },
    
    // Get data for a table
    async getTableData(req, res) {
        try {
            const { tableName } = req.params;
            const data = await findRecords(tableName);
            res.json(data);
        } catch (error) {
            console.error(`Error getting data for table ${req.params.tableName}:`, error);
            res.status(500).json({ error: `Failed to get data for table ${req.params.tableName}` });
        }
    },
    
    // Add data to a table
    async addTableData(req, res) {
        try {
            const { tableName } = req.params;
            const data = req.body;
            
            const result = await createRecord(tableName, data);
            res.status(201).json(result);
        } catch (error) {
            console.error(`Error adding data to table ${req.params.tableName}:`, error);
            res.status(500).json({ error: `Failed to add data to table ${req.params.tableName}` });
        }
    },
    
    // Update data in a table
    async updateTableData(req, res) {
        try {
            const { tableName, id } = req.params;
            const data = req.body;
            
            const result = await updateRecord(tableName, id, data);
            
            if (!result) {
                return res.status(404).json({ error: 'Record not found' });
            }
            
            res.json(result);
        } catch (error) {
            console.error(`Error updating data in table ${req.params.tableName}:`, error);
            res.status(500).json({ error: `Failed to update data in table ${req.params.tableName}` });
        }
    },
    
    // Delete data from a table
    async deleteTableData(req, res) {
        try {
            const { tableName, id } = req.params;
            
            const result = await deleteRecord(tableName, id);
            
            if (!result) {
                return res.status(404).json({ error: 'Record not found' });
            }
            
            res.json({ message: 'Record deleted successfully' });
        } catch (error) {
            console.error(`Error deleting data from table ${req.params.tableName}:`, error);
            res.status(500).json({ error: `Failed to delete data from table ${req.params.tableName}` });
        }
    },
    
    // Delete a table
    async deleteTable(req, res) {
        try {
            const { tableName } = req.params;
            
            const result = deleteTable(tableName);
            
            if (!result.success) {
                return res.status(400).json({ error: result.message });
            }
            
            res.json({ message: result.message });
        } catch (error) {
            console.error(`Error deleting table ${req.params.tableName}:`, error);
            res.status(500).json({ error: `Failed to delete table ${req.params.tableName}` });
        }
    },
    
    // Get all JSON files from the database directory
    async getAllFiles(req, res) {
        try {
            // Get all files and directories in the DB_DIR
            const files = await fs.readdir(DB_DIR);
            
            // Filter out non-JSON files
            const jsonFiles = files.filter(file => file.endsWith('.json'));
            
            // Get all directories
            const dirStats = await Promise.all(
                files.map(async file => {
                    const filePath = path.join(DB_DIR, file);
                    const stat = await fs.stat(filePath);
                    return { file, isDirectory: stat.isDirectory() };
                })
            );
            
            const directories = dirStats
                .filter(item => item.isDirectory)
                .map(item => item.file);
            
            // Read contents of each JSON file
            const fileContents = await Promise.all(
                jsonFiles.map(async file => {
                    const filePath = path.join(DB_DIR, file);
                    const content = await fs.readFile(filePath, 'utf8');
                    try {
                        return {
                            name: file,
                            content: JSON.parse(content)
                        };
                    } catch (e) {
                        return {
                            name: file,
                            content: "Invalid JSON"
                        };
                    }
                })
            );
            
            res.json({
                jsonFiles: fileContents,
                directories
            });
        } catch (error) {
            console.error('Error getting database files:', error);
            res.status(500).json({ error: 'Failed to get database files' });
        }
    },
    
    // Get files from a specific directory in the database
    async getDirectoryFiles(req, res) {
        try {
            const { directory } = req.params;
            const dirPath = path.join(DB_DIR, directory);
            
            // Check if directory exists
            try {
                await fs.access(dirPath);
            } catch (error) {
                return res.status(404).json({ error: `Directory ${directory} not found` });
            }
            
            // Get all files in the directory
            const files = await fs.readdir(dirPath);
            
            // Filter out non-JSON files
            const jsonFiles = files.filter(file => file.endsWith('.json'));
            
            // Read contents of each JSON file
            const fileContents = await Promise.all(
                jsonFiles.map(async file => {
                    const filePath = path.join(dirPath, file);
                    const content = await fs.readFile(filePath, 'utf8');
                    try {
                        return {
                            name: file,
                            content: JSON.parse(content)
                        };
                    } catch (e) {
                        return {
                            name: file,
                            content: "Invalid JSON"
                        };
                    }
                })
            );
            
            res.json(fileContents);
        } catch (error) {
            console.error(`Error getting files from directory ${req.params.directory}:`, error);
            res.status(500).json({ error: `Failed to get files from directory ${req.params.directory}` });
        }
    }
};

// Helper function to map field type to JSON schema type
function mapFieldTypeToSchemaType(fieldType) {
    switch(fieldType.toLowerCase()) {
        case 'number':
        case 'integer':
            return 'number';
        case 'boolean':
            return 'boolean';
        case 'array':
            return 'array';
        case 'object':
            return 'object';
        case 'text':
        case 'string':
        case 'date':
        case 'email':
        default:
            return 'string';
    }
}

module.exports = databaseController; 