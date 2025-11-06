const fs = require('fs');
const path = require('path');
const { v4: uuidv4 } = require('uuid');

const dataDir = path.join(__dirname, '..', 'data');

// Ensure data directory exists
if (!fs.existsSync(dataDir)) {
  fs.mkdirSync(dataDir, { recursive: true });
}

/**
 * JSON Database utility for CRUD operations on JSON files
 */
class JsonDB {
  constructor(collection) {
    this.collection = collection;
    this.filePath = path.join(dataDir, `${collection}.json`);
    
    // Create file if it doesn't exist
    if (!fs.existsSync(this.filePath)) {
      fs.writeFileSync(this.filePath, JSON.stringify([]), 'utf8');
    }
  }

  /**
   * Read all data from the JSON file
   * @returns {Array} All data from the collection
   */
  getData() {
    try {
      const data = fs.readFileSync(this.filePath, 'utf8');
      return JSON.parse(data);
    } catch (error) {
      console.error(`Error reading ${this.collection} data:`, error);
      return [];
    }
  }

  /**
   * Write data to the JSON file
   * @param {Array} data - The data to write
   * @returns {Boolean} Success status
   */
  saveData(data) {
    try {
      fs.writeFileSync(this.filePath, JSON.stringify(data, null, 2), 'utf8');
      return true;
    } catch (error) {
      console.error(`Error writing ${this.collection} data:`, error);
      return false;
    }
  }

  /**
   * Find all items in the collection
   * @returns {Array} All items
   */
  findAll() {
    return this.getData();
  }

  /**
   * Find items matching a filter
   * @param {Function} filterFn - Filter function
   * @returns {Array} Matching items
   */
  find(filterFn) {
    const data = this.getData();
    return data.filter(filterFn);
  }

  /**
   * Find one item by ID
   * @param {String} id - The ID to find
   * @returns {Object|null} The found item or null
   */
  findById(id) {
    const data = this.getData();
    return data.find(item => item.id === id) || null;
  }

  /**
   * Find one item matching a filter
   * @param {Function} filterFn - Filter function
   * @returns {Object|null} The found item or null
   */
  findOne(filterFn) {
    const data = this.getData();
    return data.find(filterFn) || null;
  }

  /**
   * Insert a new item
   * @param {Object} item - The item to insert
   * @returns {Object} The inserted item with ID
   */
  create(item) {
    const data = this.getData();
    const newItem = { 
      ...item, 
      id: item.id || uuidv4(), 
      createdAt: item.createdAt || new Date().toISOString(),
      updatedAt: new Date().toISOString()
    };
    
    data.push(newItem);
    this.saveData(data);
    return newItem;
  }

  /**
   * Update an item by ID
   * @param {String} id - The ID of the item to update
   * @param {Object} updates - The updates to apply
   * @returns {Object|null} The updated item or null
   */
  updateById(id, updates) {
    const data = this.getData();
    const index = data.findIndex(item => item.id === id);
    
    if (index === -1) return null;
    
    const updatedItem = {
      ...data[index],
      ...updates,
      id, // Ensure ID remains the same
      updatedAt: new Date().toISOString()
    };
    
    data[index] = updatedItem;
    this.saveData(data);
    return updatedItem;
  }

  /**
   * Delete an item by ID
   * @param {String} id - The ID of the item to delete
   * @returns {Boolean} Success status
   */
  deleteById(id) {
    const data = this.getData();
    const filtered = data.filter(item => item.id !== id);
    
    if (filtered.length === data.length) return false;
    
    return this.saveData(filtered);
  }

  /**
   * Delete all items matching a filter
   * @param {Function} filterFn - Filter function
   * @returns {Number} Number of items deleted
   */
  deleteMany(filterFn) {
    const data = this.getData();
    const filtered = data.filter(item => !filterFn(item));
    
    const deletedCount = data.length - filtered.length;
    this.saveData(filtered);
    return deletedCount;
  }
}

module.exports = JsonDB; 
 