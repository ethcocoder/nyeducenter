const fs = require('fs').promises;
const fsSync = require('fs');
const path = require('path');

const DB_DIR = path.join(__dirname, '../../database');
const USER_DB_DIR = path.join(__dirname, '../../database/user');

async function createDirectoryIfNotExists(dirPath) {
    console.log(`Checking directory: ${dirPath}`);
    try {
        if (!fsSync.existsSync(dirPath)) {
            console.log(`Creating directory: ${dirPath}`);
            await fs.mkdir(dirPath, { recursive: true });
            console.log(`Created directory: ${dirPath}`);
        } else {
            console.log(`Directory already exists: ${dirPath}`);
        }
    } catch (error) {
        console.error(`Error creating directory ${dirPath}:`, error);
        throw error;
    }
}

async function createFileIfNotExists(filePath, initialContent) {
    console.log(`Checking file: ${filePath}`);
    try {
        if (!fsSync.existsSync(filePath)) {
            console.log(`Creating file: ${filePath}`);
            await fs.writeFile(filePath, initialContent);
            console.log(`Created file: ${filePath}`);
        } else {
            console.log(`File already exists: ${filePath}`);
        }
    } catch (error) {
        console.error(`Error creating file ${filePath}:`, error);
        throw error;
    }
}

async function ensureDatabaseDir() {
    // Create main database directory
    await createDirectoryIfNotExists(DB_DIR);
    
    // Create user directory
    await createDirectoryIfNotExists(USER_DB_DIR);
    
    // Create role directories
    const roleDirs = ['admin', 'teacher', 'student'];
    for (const role of roleDirs) {
        const roleDir = path.join(USER_DB_DIR, role);
        await createDirectoryIfNotExists(roleDir);
        
        // If admin, create grades directory directly
        if (role === 'admin') {
            const gradesDir = path.join(roleDir, 'grades');
            await createDirectoryIfNotExists(gradesDir);
            
            // Create grades.json file for admin
            const gradesFile = path.join(gradesDir, 'grades.json');
            await createFileIfNotExists(gradesFile, JSON.stringify([], null, 2));
        }
        
        // If student or teacher, create grade directories
        if (role === 'student' || role === 'teacher') {
            for (const grade of ['9', '10', '11', '12']) {
                const gradeDir = path.join(roleDir, `grade${grade}`);
                await createDirectoryIfNotExists(gradeDir);
                
                // Create grades directory inside each grade
                const gradesDir = path.join(gradeDir, 'grades');
                await createDirectoryIfNotExists(gradesDir);
                
                // Create grades.json file for academic data
                const gradesFile = path.join(gradesDir, 'grades.json');
                await createFileIfNotExists(gradesFile, JSON.stringify([], null, 2));
            }
        }
    }
}

async function initializeDatabase() {
    console.log('Initializing database structure...');
    await ensureDatabaseDir();
    
    // Initialize tables.json file if it doesn't exist
    await createFileIfNotExists(path.join(DB_DIR, 'tables.json'), JSON.stringify([], null, 2));
    
    console.log('Database initialization complete.');
}

module.exports = {
    DB_DIR,
    USER_DB_DIR,
    ensureDatabaseDir,
    initializeDatabase
}; 