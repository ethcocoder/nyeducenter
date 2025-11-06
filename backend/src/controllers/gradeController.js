const fs = require('fs').promises;
const fsSync = require('fs');
const path = require('path');

// Define the path to the user database directory
const DB_DIR = path.join(__dirname, '../../database/user');

// Helper function to ensure directory exists
const ensureDirectoryExists = async (dirPath) => {
  try {
    if (!fsSync.existsSync(dirPath)) {
      console.log(`Creating directory: ${dirPath}`);
      await fs.mkdir(dirPath, { recursive: true });
      console.log(`Created directory: ${dirPath}`);
    }
  } catch (error) {
    console.error(`Error creating directory ${dirPath}:`, error);
    throw error;
  }
};

// Helper function to get the grade file path based on role and grade
const getGradeFilePath = (role, grade) => {
  if (role === 'admin') {
    return path.join(DB_DIR, role, 'grades', 'grades.json');
  }
  return path.join(DB_DIR, role, `grade${grade}`, 'grades', 'grades.json');
};

// Helper function to read grade data
const readGradeData = async (role, grade) => {
  try {
    const filePath = getGradeFilePath(role, grade);
    console.log(`Reading grades from: ${filePath}`);
    
    // Ensure the directory exists
    const dirPath = path.dirname(filePath);
    await ensureDirectoryExists(dirPath);
    
    if (!fsSync.existsSync(filePath)) {
      console.log(`Grade file does not exist, creating empty file: ${filePath}`);
      await fs.writeFile(filePath, JSON.stringify([], null, 2));
      return [];
    }
    
    const data = await fs.readFile(filePath, 'utf8');
    const parsedData = JSON.parse(data);
    
    if (!Array.isArray(parsedData)) {
      console.warn(`Grade data in ${filePath} is not an array, initializing empty array`);
      await fs.writeFile(filePath, JSON.stringify([], null, 2));
      return [];
    }
    
    console.log(`Successfully read ${parsedData.length} grades from ${filePath}`);
    return parsedData;
  } catch (error) {
    console.error(`Error reading grade data for ${role}, grade ${grade}:`, error);
    if (error.code === 'ENOENT') {
      // If file doesn't exist, create directory and empty file
      try {
        const filePath = getGradeFilePath(role, grade);
        const dirPath = path.dirname(filePath);
        await ensureDirectoryExists(dirPath);
        await fs.writeFile(filePath, JSON.stringify([], null, 2));
        console.log(`Created new grades file at ${filePath}`);
        return [];
      } catch (err) {
        console.error('Error creating new grades file:', err);
        throw err;
      }
    }
    throw error;
  }
};

// Helper function to write grade data
const writeGradeData = async (role, grade, data) => {
  try {
    if (!Array.isArray(data)) {
      console.error('Invalid grade data: expected an array');
      throw new Error('Invalid grade data: expected an array');
    }
    
    const filePath = getGradeFilePath(role, grade);
    console.log(`Writing ${data.length} grades to: ${filePath}`);
    
    // Ensure directory exists
    const dirPath = path.dirname(filePath);
    await ensureDirectoryExists(dirPath);
    
    await fs.writeFile(filePath, JSON.stringify(data, null, 2));
    console.log(`Successfully wrote grades to ${filePath}`);
    return data;
  } catch (error) {
    console.error(`Error writing grade data for ${role}, grade ${grade}:`, error);
    throw error;
  }
};

const gradeController = {
  // Get all grades for a specific role and grade
  async getGrades(req, res) {
    try {
      const { role, grade } = req.params;
      console.log(`Getting grades for role: ${role}, grade: ${grade}`);
      
      // Validate role
      if (!['student', 'teacher', 'admin'].includes(role)) {
        return res.status(400).json({ 
          success: false, 
          message: 'Invalid role. Must be student, teacher, or admin' 
        });
      }
      
      // Validate grade (only applicable for student and teacher)
      if ((role === 'student' || role === 'teacher') && !['9', '10', '11', '12'].includes(grade)) {
        return res.status(400).json({ 
          success: false, 
          message: 'Invalid grade. Must be 9, 10, 11, or 12 for students and teachers' 
        });
      }
      
      // For admin, we don't check grade
      const gradeToUse = role === 'admin' ? '' : grade;
      
      const grades = await readGradeData(role, gradeToUse);
      
      res.status(200).json({
        success: true,
        count: grades.length,
        data: grades
      });
    } catch (error) {
      console.error('Error getting grades:', error);
      res.status(500).json({ 
        success: false, 
        message: 'Failed to retrieve grades',
        error: error.message
      });
    }
  },
  
  // Get a specific grade by ID
  async getGradeById(req, res) {
    try {
      const { role, grade, id } = req.params;
      
      // Validate role
      if (!['student', 'teacher', 'admin'].includes(role)) {
        return res.status(400).json({ 
          success: false, 
          message: 'Invalid role. Must be student, teacher, or admin' 
        });
      }
      
      // Validate grade (only applicable for student and teacher)
      if ((role === 'student' || role === 'teacher') && !['9', '10', '11', '12'].includes(grade)) {
        return res.status(400).json({ 
          success: false, 
          message: 'Invalid grade. Must be 9, 10, 11, or 12 for students and teachers' 
        });
      }
      
      // For admin, we don't check grade
      const gradeToUse = role === 'admin' ? '' : grade;
      
      const grades = await readGradeData(role, gradeToUse);
      const gradeItem = grades.find(g => g.id === id);
      
      if (!gradeItem) {
        return res.status(404).json({ 
          success: false, 
          message: 'Grade not found' 
        });
      }
      
      res.status(200).json({
        success: true,
        data: gradeItem
      });
    } catch (error) {
      console.error('Error getting grade:', error);
      res.status(500).json({ 
        success: false, 
        message: 'Failed to retrieve grade',
        error: error.message
      });
    }
  },
  
  // Create a new grade
  async createGrade(req, res) {
    try {
      const { role, grade } = req.params;
      const gradeData = req.body;
      
      // Validate role
      if (!['student', 'teacher', 'admin'].includes(role)) {
        return res.status(400).json({ 
          success: false, 
          message: 'Invalid role. Must be student, teacher, or admin' 
        });
      }
      
      // Validate grade (only applicable for student and teacher)
      if ((role === 'student' || role === 'teacher') && !['9', '10', '11', '12'].includes(grade)) {
        return res.status(400).json({ 
          success: false, 
          message: 'Invalid grade. Must be 9, 10, 11, or 12 for students and teachers' 
        });
      }
      
      // For admin, we don't check grade
      const gradeToUse = role === 'admin' ? '' : grade;
      
      // Validate required fields in grade data
      if (!gradeData.studentId || !gradeData.courseId || !gradeData.value) {
        return res.status(400).json({ 
          success: false, 
          message: 'Missing required fields. studentId, courseId, and value are required' 
        });
      }
      
      // Read existing grades
      const grades = await readGradeData(role, gradeToUse);
      
      // Create new grade with ID and timestamps
      const newGrade = {
        id: Date.now().toString(),
        ...gradeData,
        createdAt: new Date().toISOString(),
        updatedAt: new Date().toISOString()
      };
      
      // Add new grade to the array
      grades.push(newGrade);
      
      // Write updated grades array
      await writeGradeData(role, gradeToUse, grades);
      
      res.status(201).json({
        success: true,
        message: 'Grade created successfully',
        data: newGrade
      });
    } catch (error) {
      console.error('Error creating grade:', error);
      res.status(500).json({ 
        success: false, 
        message: 'Failed to create grade',
        error: error.message
      });
    }
  },
  
  // Update an existing grade
  async updateGrade(req, res) {
    try {
      const { role, grade, id } = req.params;
      const updateData = req.body;
      
      // Validate role
      if (!['student', 'teacher', 'admin'].includes(role)) {
        return res.status(400).json({ 
          success: false, 
          message: 'Invalid role. Must be student, teacher, or admin' 
        });
      }
      
      // Validate grade (only applicable for student and teacher)
      if ((role === 'student' || role === 'teacher') && !['9', '10', '11', '12'].includes(grade)) {
        return res.status(400).json({ 
          success: false, 
          message: 'Invalid grade. Must be 9, 10, 11, or 12 for students and teachers' 
        });
      }
      
      // For admin, we don't check grade
      const gradeToUse = role === 'admin' ? '' : grade;
      
      // Read existing grades
      const grades = await readGradeData(role, gradeToUse);
      
      // Find the grade to update
      const gradeIndex = grades.findIndex(g => g.id === id);
      
      if (gradeIndex === -1) {
        return res.status(404).json({ 
          success: false, 
          message: 'Grade not found' 
        });
      }
      
      // Update the grade
      const updatedGrade = {
        ...grades[gradeIndex],
        ...updateData,
        updatedAt: new Date().toISOString()
      };
      
      // Replace the old grade with the updated one
      grades[gradeIndex] = updatedGrade;
      
      // Write updated grades array
      await writeGradeData(role, gradeToUse, grades);
      
      res.status(200).json({
        success: true,
        message: 'Grade updated successfully',
        data: updatedGrade
      });
    } catch (error) {
      console.error('Error updating grade:', error);
      res.status(500).json({ 
        success: false, 
        message: 'Failed to update grade',
        error: error.message
      });
    }
  },
  
  // Delete a grade
  async deleteGrade(req, res) {
    try {
      const { role, grade, id } = req.params;
      
      // Validate role
      if (!['student', 'teacher', 'admin'].includes(role)) {
        return res.status(400).json({ 
          success: false, 
          message: 'Invalid role. Must be student, teacher, or admin' 
        });
      }
      
      // Validate grade (only applicable for student and teacher)
      if ((role === 'student' || role === 'teacher') && !['9', '10', '11', '12'].includes(grade)) {
        return res.status(400).json({ 
          success: false, 
          message: 'Invalid grade. Must be 9, 10, 11, or 12 for students and teachers' 
        });
      }
      
      // For admin, we don't check grade
      const gradeToUse = role === 'admin' ? '' : grade;
      
      // Read existing grades
      const grades = await readGradeData(role, gradeToUse);
      
      // Find the grade to delete
      const gradeIndex = grades.findIndex(g => g.id === id);
      
      if (gradeIndex === -1) {
        return res.status(404).json({ 
          success: false, 
          message: 'Grade not found' 
        });
      }
      
      // Remove the grade
      const deletedGrade = grades.splice(gradeIndex, 1)[0];
      
      // Write updated grades array
      await writeGradeData(role, gradeToUse, grades);
      
      res.status(200).json({
        success: true,
        message: 'Grade deleted successfully',
        data: deletedGrade
      });
    } catch (error) {
      console.error('Error deleting grade:', error);
      res.status(500).json({ 
        success: false, 
        message: 'Failed to delete grade',
        error: error.message
      });
    }
  }
};

module.exports = gradeController; 