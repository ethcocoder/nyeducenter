 /**
 * Course Routes
 * 
 * API endpoints for course management
 */
const express = require('express');
const router = express.Router();
const courseModel = require('../models/course.model');
const authMiddleware = require('../middlewares/auth');

// Apply auth middleware to all course routes
router.use(authMiddleware);

/**
 * @route   GET /api/courses
 * @desc    Get all courses with optional filters
 * @access  Private
 */
router.get('/', async (req, res) => {
  try {
    // Extract query parameters
    const { teacherId, grade, subject, status, search } = req.query;
    
    let courses;
    
    // Handle search separately since it requires specialized filtering
    if (search) {
      courses = courseModel.searchCourses(search);
    } else if (teacherId) {
      courses = courseModel.getCoursesByTeacher(teacherId);
    } else if (grade) {
      courses = courseModel.getCoursesByGrade(grade);
    } else if (subject) {
      courses = courseModel.getCoursesBySubject(subject);
    } else if (status === 'published') {
      courses = courseModel.getPublishedCourses();
    } else if (status === 'draft') {
      courses = courseModel.getDraftCourses();
    } else {
      // Apply role-based filtering
      if (req.user.role === 'teacher') {
        // Teachers can only see their own courses
        courses = courseModel.getCoursesByTeacher(req.user.id);
      } else if (req.user.role === 'admin') {
        // Admins can see all courses
        courses = courseModel.listCourses();
      } else {
        // Students can only see published courses
        courses = courseModel.getPublishedCourses();
      }
    }
    
    res.json({
      success: true,
      count: courses.length,
      data: courses
    });
  } catch (error) {
    console.error('Error fetching courses:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to fetch courses',
      error: error.message
    });
  }
});

/**
 * @route   GET /api/courses/:id
 * @desc    Get course by ID
 * @access  Private
 */
router.get('/:id', async (req, res) => {
  try {
    const course = courseModel.getCourseById(req.params.id);
    
    if (!course) {
      return res.status(404).json({
        success: false,
        message: 'Course not found'
      });
    }
    
    // Permission check
    if (req.user.role === 'teacher' && course.teacherId !== req.user.id) {
      return res.status(403).json({
        success: false,
        message: 'Not authorized to access this course'
      });
    }
    
    // Students can only view published courses
    if (req.user.role === 'student' && course.status !== 'published') {
      return res.status(403).json({
        success: false,
        message: 'Course is not published yet'
      });
    }
    
    res.json({
      success: true,
      data: course
    });
  } catch (error) {
    console.error('Error fetching course:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to fetch course',
      error: error.message
    });
  }
});

/**
 * @route   POST /api/courses
 * @desc    Create a new course
 * @access  Private (Teachers only)
 */
router.post('/', async (req, res) => {
  try {
    // Check if user is a teacher
    if (req.user.role !== 'teacher' && req.user.role !== 'admin') {
      return res.status(403).json({
        success: false,
        message: 'Only teachers can create courses'
      });
    }
    
    // Set teacher ID to the current user
    const courseData = {
      ...req.body,
      teacherId: req.user.id
    };
    
    const course = courseModel.createCourse(courseData);
    
    res.status(201).json({
      success: true,
      message: 'Course created successfully',
      data: course
    });
  } catch (error) {
    console.error('Error creating course:', error);
    res.status(400).json({
      success: false,
      message: 'Failed to create course',
      error: error.message
    });
  }
});

/**
 * @route   PUT /api/courses/:id
 * @desc    Update course
 * @access  Private (Course teacher only)
 */
router.put('/:id', async (req, res) => {
  try {
    const course = courseModel.getCourseById(req.params.id);
    
    if (!course) {
      return res.status(404).json({
        success: false,
        message: 'Course not found'
      });
    }
    
    // Check if user is the course teacher or admin
    if (req.user.role !== 'admin' && course.teacherId !== req.user.id) {
      return res.status(403).json({
        success: false,
        message: 'Not authorized to update this course'
      });
    }
    
    const updatedCourse = courseModel.updateCourse(req.params.id, req.body);
    
    res.json({
      success: true,
      message: 'Course updated successfully',
      data: updatedCourse
    });
  } catch (error) {
    console.error('Error updating course:', error);
    res.status(400).json({
      success: false,
      message: 'Failed to update course',
      error: error.message
    });
  }
});

/**
 * @route   DELETE /api/courses/:id
 * @desc    Delete course
 * @access  Private (Course teacher or admin)
 */
router.delete('/:id', async (req, res) => {
  try {
    const course = courseModel.getCourseById(req.params.id);
    
    if (!course) {
      return res.status(404).json({
        success: false,
        message: 'Course not found'
      });
    }
    
    // Check if user is the course teacher or admin
    if (req.user.role !== 'admin' && course.teacherId !== req.user.id) {
      return res.status(403).json({
        success: false,
        message: 'Not authorized to delete this course'
      });
    }
    
    const deleted = courseModel.deleteCourse(req.params.id);
    
    if (deleted) {
      res.json({
        success: true,
        message: 'Course deleted successfully'
      });
    } else {
      res.status(400).json({
        success: false,
        message: 'Failed to delete course'
      });
    }
  } catch (error) {
    console.error('Error deleting course:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to delete course',
      error: error.message
    });
  }
});

/**
 * @route   POST /api/courses/:id/materials
 * @desc    Add material to course
 * @access  Private (Course teacher only)
 */
router.post('/:id/materials', async (req, res) => {
  try {
    const course = courseModel.getCourseById(req.params.id);
    
    if (!course) {
      return res.status(404).json({
        success: false,
        message: 'Course not found'
      });
    }
    
    // Check if user is the course teacher
    if (course.teacherId !== req.user.id) {
      return res.status(403).json({
        success: false,
        message: 'Not authorized to add materials to this course'
      });
    }
    
    const updatedCourse = courseModel.addCourseMaterial(req.params.id, req.body);
    
    res.status(201).json({
      success: true,
      message: 'Material added successfully',
      data: updatedCourse
    });
  } catch (error) {
    console.error('Error adding course material:', error);
    res.status(400).json({
      success: false,
      message: 'Failed to add course material',
      error: error.message
    });
  }
});

/**
 * @route   PUT /api/courses/:id/materials/:materialId
 * @desc    Update course material
 * @access  Private (Course teacher only)
 */
router.put('/:id/materials/:materialId', async (req, res) => {
  try {
    const course = courseModel.getCourseById(req.params.id);
    
    if (!course) {
      return res.status(404).json({
        success: false,
        message: 'Course not found'
      });
    }
    
    // Check if user is the course teacher
    if (course.teacherId !== req.user.id) {
      return res.status(403).json({
        success: false,
        message: 'Not authorized to update materials for this course'
      });
    }
    
    const updatedCourse = courseModel.updateCourseMaterial(
      req.params.id, 
      req.params.materialId, 
      req.body
    );
    
    res.json({
      success: true,
      message: 'Material updated successfully',
      data: updatedCourse
    });
  } catch (error) {
    console.error('Error updating course material:', error);
    res.status(400).json({
      success: false,
      message: 'Failed to update course material',
      error: error.message
    });
  }
});

/**
 * @route   DELETE /api/courses/:id/materials/:materialId
 * @desc    Delete course material
 * @access  Private (Course teacher only)
 */
router.delete('/:id/materials/:materialId', async (req, res) => {
  try {
    const course = courseModel.getCourseById(req.params.id);
    
    if (!course) {
      return res.status(404).json({
        success: false,
        message: 'Course not found'
      });
    }
    
    // Check if user is the course teacher
    if (course.teacherId !== req.user.id) {
      return res.status(403).json({
        success: false,
        message: 'Not authorized to delete materials from this course'
      });
    }
    
    const updatedCourse = courseModel.deleteCourseMaterial(
      req.params.id, 
      req.params.materialId
    );
    
    res.json({
      success: true,
      message: 'Material deleted successfully',
      data: updatedCourse
    });
  } catch (error) {
    console.error('Error deleting course material:', error);
    res.status(400).json({
      success: false,
      message: 'Failed to delete course material',
      error: error.message
    });
  }
});

/**
 * @route   PUT /api/courses/:id/status
 * @desc    Change course status (draft/published)
 * @access  Private (Course teacher only)
 */
router.put('/:id/status', async (req, res) => {
  try {
    const { status } = req.body;
    
    if (!status || (status !== 'draft' && status !== 'published')) {
      return res.status(400).json({
        success: false,
        message: 'Invalid status. Must be "draft" or "published"'
      });
    }
    
    const course = courseModel.getCourseById(req.params.id);
    
    if (!course) {
      return res.status(404).json({
        success: false,
        message: 'Course not found'
      });
    }
    
    // Check if user is the course teacher
    if (course.teacherId !== req.user.id) {
      return res.status(403).json({
        success: false,
        message: 'Not authorized to change status of this course'
      });
    }
    
    const updatedCourse = courseModel.changeStatus(req.params.id, status);
    
    res.json({
      success: true,
      message: `Course status changed to ${status}`,
      data: updatedCourse
    });
  } catch (error) {
    console.error('Error changing course status:', error);
    res.status(400).json({
      success: false,
      message: 'Failed to change course status',
      error: error.message
    });
  }
});

module.exports = router;