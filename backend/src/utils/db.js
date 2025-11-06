/**
 * JSON Database Interface
 * 
 * A structured database interface that uses JSON files for storage
 * Provides schema validation, query capabilities, and migration support
 */
const fs = require('fs');
const path = require('path');
const bcrypt = require('bcrypt');
const crypto = require('crypto');

// Database configuration
const DB_CONFIG = {
  rootDir: path.join(__dirname, '../../database'),
  schemaDir: path.join(__dirname, '../../database/schemas'),
  migrationDir: path.join(__dirname, '../../database/migrations'),
  contentDirs: [
    'course', 'quiz', 'assignment', 'chat', 
    'announcement', 'media/image', 'media/video', 'profile'
  ]
};

// Ensure database structure exists
function initializeDatabase() {
  // Create root directory if it doesn't exist
  if (!fs.existsSync(DB_CONFIG.rootDir)) {
    fs.mkdirSync(DB_CONFIG.rootDir, { recursive: true });
  }

  // Create schema directory
  if (!fs.existsSync(DB_CONFIG.schemaDir)) {
    fs.mkdirSync(DB_CONFIG.schemaDir, { recursive: true });
  }

  // Create migration directory
  if (!fs.existsSync(DB_CONFIG.migrationDir)) {
    fs.mkdirSync(DB_CONFIG.migrationDir, { recursive: true });
  }

  // Create content directories
  DB_CONFIG.contentDirs.forEach(dir => {
    const fullPath = path.join(DB_CONFIG.rootDir, dir);
    if (!fs.existsSync(fullPath)) {
      fs.mkdirSync(fullPath, { recursive: true });
    }
  });

  // Initialize users table if it doesn't exist
  const usersFile = path.join(DB_CONFIG.rootDir, 'users.json');
  if (!fs.existsSync(usersFile)) {
    fs.writeFileSync(usersFile, JSON.stringify({ users: [] }, null, 2));
  }

  // Initialize tables registry
  const tablesRegistry = path.join(DB_CONFIG.rootDir, 'tables_registry.json');
  if (!fs.existsSync(tablesRegistry)) {
    fs.writeFileSync(tablesRegistry, JSON.stringify({ tables: [] }, null, 2));
  }

  console.log('Database structure initialized.');
}

// Schema Management
function createSchema(name, schema) {
  const schemaFile = path.join(DB_CONFIG.schemaDir, `${name}.schema.json`);
  fs.writeFileSync(schemaFile, JSON.stringify(schema, null, 2));
  console.log(`Schema '${name}' created.`);
  return schema;
}

function getSchema(name) {
  const schemaFile = path.join(DB_CONFIG.schemaDir, `${name}.schema.json`);
  if (!fs.existsSync(schemaFile)) {
    return null;
  }
  return JSON.parse(fs.readFileSync(schemaFile, 'utf8'));
}

function validateAgainstSchema(data, schema) {
  // Simple schema validation
  const errors = [];

  // Validate required fields
  if (schema.required && Array.isArray(schema.required)) {
    schema.required.forEach(field => {
      if (data[field] === undefined || data[field] === null) {
        errors.push(`Missing required field: ${field}`);
      }
    });
  }

  // Validate field types
  if (schema.properties) {
    Object.keys(schema.properties).forEach(field => {
      if (data[field] !== undefined) {
        const fieldSchema = schema.properties[field];
        const fieldType = fieldSchema.type;

        // Type validation
        if (fieldType === 'string' && typeof data[field] !== 'string') {
          errors.push(`Field '${field}' should be a string`);
        } else if (fieldType === 'number' && typeof data[field] !== 'number') {
          errors.push(`Field '${field}' should be a number`);
        } else if (fieldType === 'boolean' && typeof data[field] !== 'boolean') {
          errors.push(`Field '${field}' should be a boolean`);
        } else if (fieldType === 'array' && !Array.isArray(data[field])) {
          errors.push(`Field '${field}' should be an array`);
        } else if (fieldType === 'object' && (typeof data[field] !== 'object' || Array.isArray(data[field]))) {
          errors.push(`Field '${field}' should be an object`);
        }

        // Pattern validation for strings
        if (fieldType === 'string' && fieldSchema.pattern && typeof data[field] === 'string') {
          const regex = new RegExp(fieldSchema.pattern);
          if (!regex.test(data[field])) {
            errors.push(`Field '${field}' does not match pattern: ${fieldSchema.pattern}`);
          }
        }

        // Min/max validation for numbers
        if (fieldType === 'number') {
          if (fieldSchema.minimum !== undefined && data[field] < fieldSchema.minimum) {
            errors.push(`Field '${field}' should be at least ${fieldSchema.minimum}`);
          }
          if (fieldSchema.maximum !== undefined && data[field] > fieldSchema.maximum) {
            errors.push(`Field '${field}' should be at most ${fieldSchema.maximum}`);
          }
        }

        // Length validation for strings and arrays
        if ((fieldType === 'string' || fieldType === 'array') && typeof data[field] === 'string') {
          if (fieldSchema.minLength !== undefined && data[field].length < fieldSchema.minLength) {
            errors.push(`Field '${field}' should have at least ${fieldSchema.minLength} characters`);
          }
          if (fieldSchema.maxLength !== undefined && data[field].length > fieldSchema.maxLength) {
            errors.push(`Field '${field}' should have at most ${fieldSchema.maxLength} characters`);
          }
        }
      }
    });
  }

  return { isValid: errors.length === 0, errors };
}

// Table Operations
function registerTable(tableName, schema, directory) {
  const registry = getTableRegistry();
  const existingTable = registry.tables.find(t => t.name === tableName);
  
  if (existingTable) {
    return { success: false, message: `Table '${tableName}' already exists` };
  }

  // Create schema if it doesn't exist
  if (schema && !getSchema(tableName)) {
    createSchema(tableName, schema);
  }

  // Determine directory to store table data
  const dirPath = directory ? 
    path.join(DB_CONFIG.rootDir, directory) : 
    path.join(DB_CONFIG.rootDir, tableName);

  // Ensure directory exists
  if (!fs.existsSync(dirPath)) {
    fs.mkdirSync(dirPath, { recursive: true });
  }

  // Add table to registry
  registry.tables.push({
    name: tableName,
    directory: directory || tableName,
    schemaName: tableName,
    createdAt: new Date().toISOString()
  });

  // Save registry
  fs.writeFileSync(
    path.join(DB_CONFIG.rootDir, 'tables_registry.json'),
    JSON.stringify(registry, null, 2)
  );

  return { success: true, message: `Table '${tableName}' registered successfully` };
}

function getTableRegistry() {
  const registryPath = path.join(DB_CONFIG.rootDir, 'tables_registry.json');
  if (!fs.existsSync(registryPath)) {
    return { tables: [] };
  }
  return JSON.parse(fs.readFileSync(registryPath, 'utf8'));
}

function getTableInfo(tableName) {
  const registry = getTableRegistry();
  return registry.tables.find(t => t.name === tableName);
}

function getTablePath(tableName) {
  const tableInfo = getTableInfo(tableName);
  if (!tableInfo) {
    throw new Error(`Table '${tableName}' does not exist`);
  }
  return path.join(DB_CONFIG.rootDir, tableInfo.directory);
}

function listTables() {
  const registry = getTableRegistry();
  return registry.tables.map(t => ({
    name: t.name,
    directory: t.directory,
    schemaName: t.schemaName,
    createdAt: t.createdAt
  }));
}

function deleteTable(tableName) {
  const registry = getTableRegistry();
  const tableIndex = registry.tables.findIndex(t => t.name === tableName);
  
  if (tableIndex === -1) {
    return { success: false, message: `Table '${tableName}' does not exist` };
  }

  const tableInfo = registry.tables[tableIndex];
  
  // Remove table from registry
  registry.tables.splice(tableIndex, 1);
  fs.writeFileSync(
    path.join(DB_CONFIG.rootDir, 'tables_registry.json'),
    JSON.stringify(registry, null, 2)
  );

  // Delete schema
  const schemaPath = path.join(DB_CONFIG.schemaDir, `${tableInfo.schemaName}.schema.json`);
  if (fs.existsSync(schemaPath)) {
    fs.unlinkSync(schemaPath);
  }

  // Note: We don't delete the actual data directory to prevent accidental data loss
  // Instead, we prompt the user to manually delete it if needed
  
  return { 
    success: true, 
    message: `Table '${tableName}' removed from registry. Data files in ${tableInfo.directory} remain intact.`
  };
}

// Record Operations
function createRecord(tableName, record) {
  try {
    const tableInfo = getTableInfo(tableName);
    if (!tableInfo) {
      console.log(`Table '${tableName}' does not exist, attempting to create it`);
      // Auto-create table if it doesn't exist
      registerTable(tableName);
      return createRecord(tableName, record); // Retry after creating table
    }

    // Validate against schema if it exists
    const schema = getSchema(tableInfo.schemaName);
    if (schema) {
      const validation = validateAgainstSchema(record, schema);
      if (!validation.isValid) {
        throw new Error(`Schema validation failed: ${validation.errors.join(', ')}`);
      }
    }

    // Generate ID if not provided
    if (!record.id) {
      record.id = generateId();
    }
    
    // Add timestamps
    record.createdAt = record.createdAt || new Date().toISOString();
    record.updatedAt = new Date().toISOString();

    // Ensure record directory exists
    const recordDir = path.join(DB_CONFIG.rootDir, tableInfo.directory);
    if (!fs.existsSync(recordDir)) {
      fs.mkdirSync(recordDir, { recursive: true });
    }

    // Save to file
    const recordFile = path.join(recordDir, `${record.id}.json`);
    
    fs.writeFileSync(recordFile, JSON.stringify(record, null, 2));
    
    // Update index if possible, but don't fail if index doesn't exist
    try {
      updateIndexes(tableName, record);
    } catch(error) {
      console.log(`Index update skipped for ${tableName}: ${error.message}`);
    }
    
    return record;
  } catch (error) {
    console.error(`Error creating record in ${tableName}:`, error);
    throw error;
  }
}

function readRecord(tableName, id) {
  const tableInfo = getTableInfo(tableName);
  if (!tableInfo) {
    throw new Error(`Table '${tableName}' does not exist`);
  }

  const recordFile = path.join(DB_CONFIG.rootDir, tableInfo.directory, `${id}.json`);
  
  if (!fs.existsSync(recordFile)) {
    return null;
  }
  
  return JSON.parse(fs.readFileSync(recordFile, 'utf8'));
}

function updateRecord(tableName, id, updates) {
  const record = readRecord(tableName, id);
  
  if (!record) {
    return null;
  }
  
  // Apply updates
  const updatedRecord = {
    ...record,
    ...updates,
    id, // Ensure ID doesn't change
    updatedAt: new Date().toISOString()
  };
  
  // Validate against schema
  const tableInfo = getTableInfo(tableName);
  const schema = getSchema(tableInfo.schemaName);
  
  if (schema) {
    const validation = validateAgainstSchema(updatedRecord, schema);
    if (!validation.isValid) {
      throw new Error(`Schema validation failed: ${validation.errors.join(', ')}`);
    }
  }
  
  // Save updated record
  const recordFile = path.join(DB_CONFIG.rootDir, tableInfo.directory, `${id}.json`);
  fs.writeFileSync(recordFile, JSON.stringify(updatedRecord, null, 2));
  
  // Update indexes
  updateIndexes(tableName, updatedRecord);
  
  return updatedRecord;
}

function deleteRecord(tableName, id) {
  const tableInfo = getTableInfo(tableName);
  if (!tableInfo) {
    throw new Error(`Table '${tableName}' does not exist`);
  }

  const recordFile = path.join(DB_CONFIG.rootDir, tableInfo.directory, `${id}.json`);
  
  if (!fs.existsSync(recordFile)) {
    return false;
  }
  
  // Remove from indexes before deleting
  const record = JSON.parse(fs.readFileSync(recordFile, 'utf8'));
  removeFromIndexes(tableName, record);
  
  // Delete file
  fs.unlinkSync(recordFile);
  
  return true;
}

// Query Operations
function createIndex(tableName, fields) {
  const tableInfo = getTableInfo(tableName);
  if (!tableInfo) {
    throw new Error(`Table '${tableName}' does not exist`);
  }

  const indexDir = path.join(DB_CONFIG.rootDir, tableInfo.directory, 'indexes');
  if (!fs.existsSync(indexDir)) {
    fs.mkdirSync(indexDir, { recursive: true });
  }

  // Create index definition
  const indexName = fields.join('_');
  const indexDef = {
    name: indexName,
    fields,
    createdAt: new Date().toISOString()
  };

  fs.writeFileSync(
    path.join(indexDir, `${indexName}.def.json`),
    JSON.stringify(indexDef, null, 2)
  );

  // Initialize index data
  const indexData = {};
  fs.writeFileSync(
    path.join(indexDir, `${indexName}.idx.json`),
    JSON.stringify(indexData, null, 2)
  );

  // Build index with existing records
  const recordsDir = path.join(DB_CONFIG.rootDir, tableInfo.directory);
  const files = fs.readdirSync(recordsDir)
    .filter(file => file.endsWith('.json') && !file.includes('schema'));

  files.forEach(file => {
    const record = JSON.parse(fs.readFileSync(path.join(recordsDir, file)));
    addToIndex(tableName, indexName, record);
  });

  return { success: true, message: `Index '${indexName}' created for table '${tableName}'` };
}

function addToIndex(tableName, indexName, record) {
  const tableInfo = getTableInfo(tableName);
  const indexFile = path.join(
    DB_CONFIG.rootDir, 
    tableInfo.directory, 
    'indexes', 
    `${indexName}.idx.json`
  );
  
  if (!fs.existsSync(indexFile)) {
    return false;
  }
  
  const indexData = JSON.parse(fs.readFileSync(indexFile, 'utf8'));
  const indexDef = JSON.parse(fs.readFileSync(
    path.join(DB_CONFIG.rootDir, tableInfo.directory, 'indexes', `${indexName}.def.json`),
    'utf8'
  ));
  
  // Create index key
  const key = indexDef.fields.map(field => {
    const value = record[field];
    if (value === undefined) return 'undefined';
    if (value === null) return 'null';
    return value.toString();
  }).join('::');
  
  // Add to index
  if (!indexData[key]) {
    indexData[key] = [];
  }
  
  // Prevent duplicates
  if (!indexData[key].includes(record.id)) {
    indexData[key].push(record.id);
  }
  
  fs.writeFileSync(indexFile, JSON.stringify(indexData, null, 2));
  return true;
}

function removeFromIndexes(tableName, record) {
  const tableInfo = getTableInfo(tableName);
  const indexesDir = path.join(DB_CONFIG.rootDir, tableInfo.directory, 'indexes');
  
  if (!fs.existsSync(indexesDir)) {
    return;
  }
  
  const indexFiles = fs.readdirSync(indexesDir)
    .filter(file => file.endsWith('.idx.json'));
  
  indexFiles.forEach(file => {
    const indexName = file.replace('.idx.json', '');
    const indexFile = path.join(indexesDir, file);
    const indexDefFile = path.join(indexesDir, `${indexName}.def.json`);
    
    if (!fs.existsSync(indexDefFile)) {
      return;
    }
    
    const indexData = JSON.parse(fs.readFileSync(indexFile, 'utf8'));
    const indexDef = JSON.parse(fs.readFileSync(indexDefFile, 'utf8'));
    
    // Create index key
    const key = indexDef.fields.map(field => {
      const value = record[field];
      if (value === undefined) return 'undefined';
      if (value === null) return 'null';
      return value.toString();
    }).join('::');
    
    // Remove from index
    if (indexData[key] && indexData[key].includes(record.id)) {
      indexData[key] = indexData[key].filter(id => id !== record.id);
      
      // Remove empty keys
      if (indexData[key].length === 0) {
        delete indexData[key];
      }
      
      fs.writeFileSync(indexFile, JSON.stringify(indexData, null, 2));
    }
  });
}

function updateIndexes(tableName, record) {
  try {
    const tableInfo = getTableInfo(tableName);
    if (!tableInfo) return; // Skip if table doesn't exist
    
    const indexesDir = path.join(DB_CONFIG.rootDir, tableInfo.directory, 'indexes');
    
    // Skip if indexes directory doesn't exist
    if (!fs.existsSync(indexesDir)) return;
    
    const indexFiles = fs.readdirSync(indexesDir)
      .filter(file => file.endsWith('.def.json'));
    
    indexFiles.forEach(file => {
      const indexName = file.replace('.def.json', '');
      addToIndex(tableName, indexName, record);
    });
  } catch (error) {
    console.log(`Error updating indexes for ${tableName}:`, error.message);
    // Don't throw the error to prevent breaking the main flow
  }
}

function query(tableName, filter = {}) {
  const tableInfo = getTableInfo(tableName);
  if (!tableInfo) {
    throw new Error(`Table '${tableName}' does not exist`);
  }

  // Check if we can use an index for this query
  const indexResult = tryQueryWithIndex(tableName, filter);
  if (indexResult) {
    return indexResult;
  }

  // Full scan fallback
  const recordsDir = path.join(DB_CONFIG.rootDir, tableInfo.directory);
  const files = fs.readdirSync(recordsDir)
    .filter(file => file.endsWith('.json') && !file.includes('.schema.') && !file.includes('.idx.') && !file.includes('.def.'));

  const results = [];

  files.forEach(file => {
    const record = JSON.parse(fs.readFileSync(path.join(recordsDir, file), 'utf8'));
    
    // Check if record matches the filter
    const matches = Object.entries(filter).every(([key, value]) => {
      // Handle nested paths with dot notation (e.g., 'user.name')
      if (key.includes('.')) {
        const parts = key.split('.');
        let current = record;
        
        for (let i = 0; i < parts.length - 1; i++) {
          if (!current[parts[i]]) return false;
          current = current[parts[i]];
        }
        
        return current[parts[parts.length - 1]] === value;
      }
      
      return record[key] === value;
    });
    
    if (matches) {
      results.push(record);
    }
  });

  return results;
}

function tryQueryWithIndex(tableName, filter) {
  const tableInfo = getTableInfo(tableName);
  const indexesDir = path.join(DB_CONFIG.rootDir, tableInfo.directory, 'indexes');
  
  if (!fs.existsSync(indexesDir)) {
    return null;
  }
  
  // Get all index definitions
  const indexDefFiles = fs.readdirSync(indexesDir)
    .filter(file => file.endsWith('.def.json'));
  
  // Check if any index can be used for this query
  for (const file of indexDefFiles) {
    const indexName = file.replace('.def.json', '');
    const indexDef = JSON.parse(fs.readFileSync(path.join(indexesDir, file), 'utf8'));
    
    // Check if all filter fields are covered by this index
    const filterKeys = Object.keys(filter);
    
    // The fields must be in the same order as the index for a perfect match
    if (arraysEqual(filterKeys, indexDef.fields)) {
      const indexFile = path.join(indexesDir, `${indexName}.idx.json`);
      const indexData = JSON.parse(fs.readFileSync(indexFile, 'utf8'));
      
      // Create index key
      const key = indexDef.fields.map(field => {
        const value = filter[field];
        if (value === undefined) return 'undefined';
        if (value === null) return 'null';
        return value.toString();
      }).join('::');
      
      // If key exists in index, return matching records
      if (indexData[key]) {
        const ids = indexData[key];
        const records = [];
        
        for (const id of ids) {
          const record = readRecord(tableName, id);
          if (record) {
            records.push(record);
          }
        }
        
        return records;
      }
    }
  }
  
  return null;
}

// Utility Functions
function generateId() {
  return crypto.randomBytes(16).toString('hex');
}

function hashPassword(password) {
  return bcrypt.hashSync(password, 10);
}

function comparePassword(password, hash) {
  return bcrypt.compareSync(password, hash);
}

function arraysEqual(a, b) {
  if (a.length !== b.length) return false;
  for (let i = 0; i < a.length; i++) {
    if (a[i] !== b[i]) return false;
  }
  return true;
}

// User Authentication
function createUser(userData) {
  const usersFile = path.join(DB_CONFIG.rootDir, 'users.json');
  const usersData = JSON.parse(fs.readFileSync(usersFile, 'utf8'));
  
  // Check if username already exists
  if (usersData.users.find(user => user.username === userData.username)) {
    throw new Error('Username already exists');
  }
  
  const newUser = {
    id: generateId(),
    ...userData,
    password: hashPassword(userData.password),
    createdAt: new Date().toISOString(),
    updatedAt: new Date().toISOString()
  };
  
  usersData.users.push(newUser);
  fs.writeFileSync(usersFile, JSON.stringify(usersData, null, 2));
  
  // Don't return password
  const { password, ...userWithoutPassword } = newUser;
  return userWithoutPassword;
}

function findUserByUsername(username) {
  const usersFile = path.join(DB_CONFIG.rootDir, 'users.json');
  const usersData = JSON.parse(fs.readFileSync(usersFile, 'utf8'));
  return usersData.users.find(user => user.username === username) || null;
}

function validateUser(username, password) {
  const user = findUserByUsername(username);
  
  if (!user || !comparePassword(password, user.password)) {
    return null;
  }
  
  // Don't return password
  const { password: _, ...userWithoutPassword } = user;
  return userWithoutPassword;
}

// Migration Support
function createMigration(name, actions) {
  const timestamp = Date.now();
  const migrationName = `${timestamp}_${name}`;
  const migrationFile = path.join(DB_CONFIG.migrationDir, `${migrationName}.json`);
  
  const migration = {
    name: migrationName,
    createdAt: new Date().toISOString(),
    actions: actions || [],
    applied: false
  };
  
  fs.writeFileSync(migrationFile, JSON.stringify(migration, null, 2));
  return migration;
}

function runMigration(migrationName) {
  const migrationFile = path.join(DB_CONFIG.migrationDir, `${migrationName}.json`);
  
  if (!fs.existsSync(migrationFile)) {
    throw new Error(`Migration '${migrationName}' not found`);
  }
  
  const migration = JSON.parse(fs.readFileSync(migrationFile, 'utf8'));
  
  if (migration.applied) {
    return { success: false, message: 'Migration already applied' };
  }
  
  try {
    for (const action of migration.actions) {
      if (action.type === 'createTable') {
        registerTable(action.tableName, action.schema, action.directory);
      } else if (action.type === 'createIndex') {
        createIndex(action.tableName, action.fields);
      } else if (action.type === 'addField') {
        // Update schema
        const tableInfo = getTableInfo(action.tableName);
        const schema = getSchema(tableInfo.schemaName);
        
        if (schema) {
          schema.properties[action.field] = action.fieldDefinition;
          if (action.required && !schema.required.includes(action.field)) {
            schema.required.push(action.field);
          }
          
          createSchema(tableInfo.schemaName, schema);
        }
        
        // Update all records if default value provided
        if (action.defaultValue !== undefined) {
          const recordsDir = path.join(DB_CONFIG.rootDir, tableInfo.directory);
          const files = fs.readdirSync(recordsDir)
            .filter(file => file.endsWith('.json') && !file.includes('.schema.') && !file.includes('.idx.') && !file.includes('.def.'));
          
          files.forEach(file => {
            const record = JSON.parse(fs.readFileSync(path.join(recordsDir, file), 'utf8'));
            
            if (record[action.field] === undefined) {
              record[action.field] = action.defaultValue;
              fs.writeFileSync(path.join(recordsDir, file), JSON.stringify(record, null, 2));
            }
          });
        }
      } else if (action.type === 'removeField') {
        // Update schema
        const tableInfo = getTableInfo(action.tableName);
        const schema = getSchema(tableInfo.schemaName);
        
        if (schema) {
          delete schema.properties[action.field];
          if (schema.required) {
            schema.required = schema.required.filter(field => field !== action.field);
          }
          
          createSchema(tableInfo.schemaName, schema);
        }
        
        // Remove field from all records
        const recordsDir = path.join(DB_CONFIG.rootDir, tableInfo.directory);
        const files = fs.readdirSync(recordsDir)
          .filter(file => file.endsWith('.json') && !file.includes('.schema.') && !file.includes('.idx.') && !file.includes('.def.'));
        
        files.forEach(file => {
          const record = JSON.parse(fs.readFileSync(path.join(recordsDir, file), 'utf8'));
          
          if (record[action.field] !== undefined) {
            delete record[action.field];
            fs.writeFileSync(path.join(recordsDir, file), JSON.stringify(record, null, 2));
          }
        });
      }
    }
    
    // Mark migration as applied
    migration.applied = true;
    migration.appliedAt = new Date().toISOString();
    fs.writeFileSync(migrationFile, JSON.stringify(migration, null, 2));
    
    return { success: true, message: `Migration '${migrationName}' applied successfully` };
  } catch (error) {
    return { 
      success: false, 
      message: `Error applying migration: ${error.message}`,
      error
    };
  }
}

function listMigrations() {
  if (!fs.existsSync(DB_CONFIG.migrationDir)) {
    return [];
  }
  
  const files = fs.readdirSync(DB_CONFIG.migrationDir)
    .filter(file => file.endsWith('.json'));
  
  return files.map(file => {
    const migration = JSON.parse(fs.readFileSync(path.join(DB_CONFIG.migrationDir, file), 'utf8'));
    return {
      name: migration.name,
      createdAt: migration.createdAt,
      applied: migration.applied,
      appliedAt: migration.appliedAt
    };
  });
}

// Initialize database on module load
initializeDatabase();

// Export API
module.exports = {
  // Database Management
  initializeDatabase,
  
  // Schema Management
  createSchema,
  getSchema,
  validateAgainstSchema,
  
  // Table Operations
  registerTable,
  getTableInfo,
  listTables,
  deleteTable,
  
  // Record Operations
  createRecord,
  readRecord,
  updateRecord,
  deleteRecord,
  
  // Query Operations
  createIndex,
  query,
  
  // User Authentication
  createUser,
  findUserByUsername,
  validateUser,
  
  // Utility Functions
  generateId,
  hashPassword,
  comparePassword,
  
  // Migration Support
  createMigration,
  runMigration,
  listMigrations
};