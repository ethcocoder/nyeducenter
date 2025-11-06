/**
 * Course Model
 * 
 * Handles CRUD operations for courses using the JSON database interface
 */
const db = require('../utils/db');
const path = require('path');

// Initialize course table if not exists
function initCourseTable() {
  // Define course schema
  const courseSchema = {
    type: 'object',
    required: ['title', 'subject', 'description', 'teacherId', 'grade'],
    properties: {
      id: { type: 'string' },
      title: { 
        type: 'string',
        minLength: 5,
        maxLength: 100
      },
      subject: { 
        type: 'string',
        enum: ['mathematics', 'physics', 'chemistry', 'biology', 'history', 
               'geography', 'civics', 'english', 'amharic', 'ict']
      },
      description: {
        type: 'string',
        minLength: 20
      },
      teacherId: { type: 'string' },
      grade: { type: 'string' },
      status: {
        type: 'string',
        enum: ['draft', 'published']
      },
      imageUrl: { type: 'string' },
      materials: {
        type: 'array',
        items: {
          type: 'object',
          properties: {
            id: { type: 'string' },
            title: { type: 'string' },
            content: { type: 'string' },
            type: { 
              type: 'string',
              enum: ['text', 'pdf', 'video']
            },
            url: { type: 'string' }
          }
        }
      },
      videoUrl: { type: 'string' },
      createdAt: { type: 'string' },
      updatedAt: { type: 'string' }
    }
  };

  // Register course table
  const tableResult = db.registerTable('course', courseSchema, 'course');
  
  // Create indexes for faster querying
  if (tableResult.success) {
    db.createIndex('course', ['teacherId']);
    db.createIndex('course', ['grade']);
    db.createIndex('course', ['subject']);
    db.createIndex('course', ['status']);
  }
}

// Create a new course
function createCourse(courseData) {
  // Ensure table exists
  initCourseTable();
  
  // Set default status if not provided
  if (!courseData.status) {
    courseData.status = 'draft';
  }
  
  // Set empty materials array if not provided
  if (!courseData.materials) {
    courseData.materials = [];
  }
  
  try {
    // Create course record
    return db.createRecord('course', courseData);
  } catch (error) {
    console.error('Error creating course:', error);
    throw new Error(`Failed to create course: ${error.message}`);
  }
}

// Get course by ID
function getCourseById(id) {
  try {
    return db.readRecord('course', id);
  } catch (error) {
    console.error('Error finding course:', error);
    return null;
  }
}

// Update course
function updateCourse(id, updates) {
  try {
    const course = getCourseById(id);
    if (!course) {
      throw new Error('Course not found');
    }
    
    return db.updateRecord('course', id, updates);
  } catch (error) {
    console.error('Error updating course:', error);
    throw new Error(`Failed to update course: ${error.message}`);
  }
}

// Delete course
function deleteCourse(id) {
  try {
    const course = getCourseById(id);
    if (!course) {
      throw new Error('Course not found');
    }
    
    return db.deleteRecord('course', id);
  } catch (error) {
    console.error('Error deleting course:', error);
    throw new Error(`Failed to delete course: ${error.message}`);
  }
}

// List all courses with optional filters
function listCourses(filters = {}) {
  try {
    return db.query('course', filters);
  } catch (error) {
    console.error('Error listing courses:', error);
    return [];
  }
}

// Get courses by teacher
function getCoursesByTeacher(teacherId) {
  return listCourses({ teacherId });
}

// Get courses by grade
function getCoursesByGrade(grade) {
  return listCourses({ grade });
}

// Get courses by subject
function getCoursesBySubject(subject) {
  return listCourses({ subject });
}

// Get published courses
function getPublishedCourses() {
  return listCourses({ status: 'published' });
}

// Get draft courses
function getDraftCourses() {
  return listCourses({ status: 'draft' });
}

// Add material to course
function addCourseMaterial(courseId, material) {
  try {
    const course = getCourseById(courseId);
    if (!course) {
      throw new Error('Course not found');
    }
    
    // Generate ID for material if not provided
    if (!material.id) {
      material.id = db.generateId();
    }
    
    // Add material to course
    const materials = [...(course.materials || []), material];
    return db.updateRecord('course', courseId, { materials });
  } catch (error) {
    console.error('Error adding course material:', error);
    throw new Error(`Failed to add course material: ${error.message}`);
  }
}

// Update course material
function updateCourseMaterial(courseId, materialId, updates) {
  try {
    const course = getCourseById(courseId);
    if (!course) {
      throw new Error('Course not found');
    }
    
    // Find material
    const materials = course.materials || [];
    const materialIndex = materials.findIndex(m => m.id === materialId);
    
    if (materialIndex === -1) {
      throw new Error('Material not found');
    }
    
    // Update material
    materials[materialIndex] = {
      ...materials[materialIndex],
      ...updates
    };
    
    return db.updateRecord('course', courseId, { materials });
  } catch (error) {
    console.error('Error updating course material:', error);
    throw new Error(`Failed to update course material: ${error.message}`);
  }
}

// Delete course material
function deleteCourseMaterial(courseId, materialId) {
  try {
    const course = getCourseById(courseId);
    if (!course) {
      throw new Error('Course not found');
    }
    
    // Filter out material
    const materials = (course.materials || []).filter(m => m.id !== materialId);
    return db.updateRecord('course', courseId, { materials });
  } catch (error) {
    console.error('Error deleting course material:', error);
    throw new Error(`Failed to delete course material: ${error.message}`);
  }
}

// Course search with text matching
function searchCourses(searchTerm) {
  try {
    // Get all courses
    const allCourses = listCourses();
    
    // Perform client-side filtering
    return allCourses.filter(course => {
      const term = searchTerm.toLowerCase();
      const title = (course.title || '').toLowerCase();
      const description = (course.description || '').toLowerCase();
      const subject = (course.subject || '').toLowerCase();
      
      return title.includes(term) || 
             description.includes(term) || 
             subject.includes(term);
    });
  } catch (error) {
    console.error('Error searching courses:', error);
    return [];
  }
}

// Change course status
function changeStatus(courseId, status) {
  try {
    if (status !== 'draft' && status !== 'published') {
      throw new Error('Invalid status. Must be "draft" or "published"');
    }
    
    return updateCourse(courseId, { status });
  } catch (error) {
    console.error('Error changing course status:', error);
    throw new Error(`Failed to change course status: ${error.message}`);
  }
}

// Export model functions
module.exports = {
  createCourse,
  getCourseById,
  updateCourse,
  deleteCourse,
  listCourses,
  getCoursesByTeacher,
  getCoursesByGrade,
  getCoursesBySubject,
  getPublishedCourses,
  getDraftCourses,
  addCourseMaterial,
  updateCourseMaterial,
  deleteCourseMaterial,
  searchCourses,
  changeStatus
};