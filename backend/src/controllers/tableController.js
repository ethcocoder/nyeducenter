const fs = require('fs').promises;
const path = require('path');
const { DB_DIR } = require('../config/database');

const tableController = {
    async getAllTables(req, res) {
        try {
            const tables = JSON.parse(await fs.readFile(path.join(DB_DIR, 'tables.json'), 'utf8'));
            res.json(tables);
        } catch (error) {
            res.status(500).json({ error: 'Server error' });
        }
    },

    async createTable(req, res) {
        const { name, fields } = req.body;
        
        try {
            const tables = JSON.parse(await fs.readFile(path.join(DB_DIR, 'tables.json'), 'utf8'));
            
            if (tables.some(t => t.name === name)) {
                return res.status(400).json({ error: 'Table already exists' });
            }

            tables.push({ name, fields });
            await fs.writeFile(path.join(DB_DIR, 'tables.json'), JSON.stringify(tables, null, 2));
            
            // Create empty data file for the table
            await fs.writeFile(path.join(DB_DIR, `${name}.json`), JSON.stringify([], null, 2));
            
            res.status(201).json({ message: 'Table created successfully' });
        } catch (error) {
            res.status(500).json({ error: 'Server error' });
        }
    },

    async deleteTable(req, res) {
        const { name } = req.params;
        
        try {
            const tables = JSON.parse(await fs.readFile(path.join(DB_DIR, 'tables.json'), 'utf8'));
            const filteredTables = tables.filter(t => t.name !== name);
            
            await fs.writeFile(path.join(DB_DIR, 'tables.json'), JSON.stringify(filteredTables, null, 2));
            await fs.unlink(path.join(DB_DIR, `${name}.json`));
            
            res.json({ message: 'Table deleted successfully' });
        } catch (error) {
            res.status(500).json({ error: 'Server error' });
        }
    },

    async getTableData(req, res) {
        const { name } = req.params;
        
        try {
            const data = JSON.parse(await fs.readFile(path.join(DB_DIR, `${name}.json`), 'utf8'));
            res.json(data);
        } catch (error) {
            res.status(500).json({ error: 'Server error' });
        }
    },

    async addTableData(req, res) {
        const { name } = req.params;
        const newData = req.body;
        
        try {
            const data = JSON.parse(await fs.readFile(path.join(DB_DIR, `${name}.json`), 'utf8'));
            newData.id = Date.now().toString();
            data.push(newData);
            
            await fs.writeFile(path.join(DB_DIR, `${name}.json`), JSON.stringify(data, null, 2));
            res.status(201).json(newData);
        } catch (error) {
            res.status(500).json({ error: 'Server error' });
        }
    },

    async updateTableData(req, res) {
        const { name, id } = req.params;
        const updatedData = req.body;
        
        try {
            const data = JSON.parse(await fs.readFile(path.join(DB_DIR, `${name}.json`), 'utf8'));
            const index = data.findIndex(item => item.id === id);
            
            if (index === -1) {
                return res.status(404).json({ error: 'Data not found' });
            }
            
            data[index] = { ...data[index], ...updatedData, id };
            await fs.writeFile(path.join(DB_DIR, `${name}.json`), JSON.stringify(data, null, 2));
            
            res.json(data[index]);
        } catch (error) {
            res.status(500).json({ error: 'Server error' });
        }
    },

    async deleteTableData(req, res) {
        const { name, id } = req.params;
        
        try {
            const data = JSON.parse(await fs.readFile(path.join(DB_DIR, `${name}.json`), 'utf8'));
            const filteredData = data.filter(item => item.id !== id);
            
            await fs.writeFile(path.join(DB_DIR, `${name}.json`), JSON.stringify(filteredData, null, 2));
            res.json({ message: 'Data deleted successfully' });
        } catch (error) {
            res.status(500).json({ error: 'Server error' });
        }
    }
};

module.exports = tableController; 