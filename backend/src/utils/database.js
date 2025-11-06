const fs = require('fs').promises;
const path = require('path');
const bcrypt = require('bcrypt');

const DB_DIR = path.join(__dirname, '../../database');

// Ensure database directory exists
const ensureDbDir = async () => {
    try {
        await fs.access(DB_DIR);
    } catch {
        await fs.mkdir(DB_DIR, { recursive: true });
    }
};

// Read a table from JSON file
const readTable = async (tableName) => {
    await ensureDbDir();
    const filePath = path.join(DB_DIR, `${tableName}.json`);
    
    try {
        const data = await fs.readFile(filePath, 'utf8');
        return JSON.parse(data);
    } catch (error) {
        if (error.code === 'ENOENT') {
            return [];
        }
        throw error;
    }
};

// Write a table to JSON file
const writeTable = async (tableName, data) => {
    await ensureDbDir();
    const filePath = path.join(DB_DIR, `${tableName}.json`);
    await fs.writeFile(filePath, JSON.stringify(data, null, 2));
};

// Generate a new ID
const generateId = () => {
    return Date.now().toString(36) + Math.random().toString(36).substr(2);
};

// Hash password
const hashPassword = async (password) => {
    const salt = await bcrypt.genSalt(10);
    return bcrypt.hash(password, salt);
};

// Compare password
const comparePassword = async (password, hash) => {
    return bcrypt.compare(password, hash);
};

// Create a new record
const createRecord = async (tableName, data) => {
    const records = await readTable(tableName);
    const newRecord = {
        id: generateId(),
        createdAt: new Date().toISOString(),
        updatedAt: new Date().toISOString(),
        ...data
    };
    records.push(newRecord);
    await writeTable(tableName, records);
    return newRecord;
};

// Update a record
const updateRecord = async (tableName, id, data) => {
    const records = await readTable(tableName);
    const index = records.findIndex(record => record.id === id);
    
    if (index === -1) {
        throw new Error('Record not found');
    }
    
    records[index] = {
        ...records[index],
        ...data,
        updatedAt: new Date().toISOString()
    };
    
    await writeTable(tableName, records);
    return records[index];
};

// Delete a record
const deleteRecord = async (tableName, id) => {
    const records = await readTable(tableName);
    const filteredRecords = records.filter(record => record.id !== id);
    
    if (filteredRecords.length === records.length) {
        throw new Error('Record not found');
    }
    
    await writeTable(tableName, filteredRecords);
    return true;
};

// Find records by criteria
const findRecords = async (tableName, criteria) => {
    const records = await readTable(tableName);
    return records.filter(record => {
        return Object.entries(criteria).every(([key, value]) => {
            // Handle special operators
            if (value && typeof value === 'object') {
                // $in operator - check if record[key] is in the array
                if (value.$in && Array.isArray(value.$in)) {
                    return value.$in.includes(record[key]);
                }
            }
            // Default case: exact match
            return record[key] === value;
        });
    });
};

// Find one record by criteria
const findOneRecord = async (tableName, criteria) => {
    const records = await findRecords(tableName, criteria);
    return records[0] || null;
};

module.exports = {
    readTable,
    writeTable,
    generateId,
    hashPassword,
    comparePassword,
    createRecord,
    updateRecord,
    deleteRecord,
    findRecords,
    findOneRecord
}; 