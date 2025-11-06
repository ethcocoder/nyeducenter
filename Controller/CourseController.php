<?php
require_once 'Models/Course.php';
require_once 'Models/Chapter.php';
require_once 'Models/Topic.php';

class CourseController {
    private $course;
    private $chapter;
    private $topic;
    private $db;
    private $user;

    public function __construct($db, $user) {
        $this->db = $db;
        $this->user = $user;
        $this->course = new Course($db);
        $this->chapter = new Chapter($db);
        $this->topic = new Topic($db);
    }

    // List all courses (filtered by role)
    public function index() {
        $filters = [];
        
        // Instructors can only see their courses
        if ($this->user['role'] === 'instructor') {
            $filters['created_by'] = $this->user['id'];
            $filters['created_by_role'] = 'instructor';
        }
        
        // Students can only see public courses
        if ($this->user['role'] === 'student') {
            $filters['status'] = 'public';
        }
        
        $courses = $this->course->getAll($filters);
        return [
            'status' => 'success',
            'data' => $courses
        ];
    }

    // Get course details
    public function show($course_id) {
        $course = $this->course->getById($course_id);
        
        if (!$course) {
            return [
                'status' => 'error',
                'message' => 'Course not found'
            ];
        }
        
        // Check access permissions
        if ($this->user['role'] === 'instructor' && 
            $course['created_by'] !== $this->user['id']) {
            return [
                'status' => 'error',
                'message' => 'Access denied'
            ];
        }
        
        if ($this->user['role'] === 'student' && 
            $course['status'] !== 'public') {
            return [
                'status' => 'error',
                'message' => 'Access denied'
            ];
        }
        
        // Get chapters and topics
        $chapters = $this->chapter->getByCourseId($course_id);
        foreach ($chapters as &$chapter) {
            $chapter['topics'] = $this->topic->getByChapterId($chapter['chapter_id']);
        }
        
        $course['chapters'] = $chapters;
        
        return [
            'status' => 'success',
            'data' => $course
        ];
    }

    // Create new course (Admin and Instructor only)
    public function create($data) {
        if (!in_array($this->user['role'], ['admin', 'instructor'])) {
            return [
                'status' => 'error',
                'message' => 'Access denied'
            ];
        }
        
        // Validate required fields
        $required = ['title', 'description', 'status'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return [
                    'status' => 'error',
                    'message' => "Missing required field: $field"
                ];
            }
        }
        
        // Add creator info
        $data['created_by'] = $this->user['id'];
        $data['created_by_role'] = $this->user['role'];
        
        $course_id = $this->course->create($data);
        
        if (!$course_id) {
            return [
                'status' => 'error',
                'message' => 'Failed to create course'
            ];
        }
        
        return [
            'status' => 'success',
            'message' => 'Course created successfully',
            'data' => ['course_id' => $course_id]
        ];
    }

    // Update course (Admin and Instructor only)
    public function update($course_id, $data) {
        if (!in_array($this->user['role'], ['admin', 'instructor'])) {
            return [
                'status' => 'error',
                'message' => 'Access denied'
            ];
        }
        
        $course = $this->course->getById($course_id);
        
        if (!$course) {
            return [
                'status' => 'error',
                'message' => 'Course not found'
            ];
        }
        
        // Check if instructor owns the course
        if ($this->user['role'] === 'instructor' && 
            $course['created_by'] !== $this->user['id']) {
            return [
                'status' => 'error',
                'message' => 'Access denied'
            ];
        }
        
        $success = $this->course->update($course_id, $data);
        
        if (!$success) {
            return [
                'status' => 'error',
                'message' => 'Failed to update course'
            ];
        }
        
        return [
            'status' => 'success',
            'message' => 'Course updated successfully'
        ];
    }

    // Delete course (Admin and Instructor only)
    public function delete($course_id) {
        if (!in_array($this->user['role'], ['admin', 'instructor'])) {
            return [
                'status' => 'error',
                'message' => 'Access denied'
            ];
        }
        
        $course = $this->course->getById($course_id);
        
        if (!$course) {
            return [
                'status' => 'error',
                'message' => 'Course not found'
            ];
        }
        
        // Check if instructor owns the course
        if ($this->user['role'] === 'instructor' && 
            $course['created_by'] !== $this->user['id']) {
            return [
                'status' => 'error',
                'message' => 'Access denied'
            ];
        }
        
        $success = $this->course->delete($course_id);
        
        if (!$success) {
            return [
                'status' => 'error',
                'message' => 'Failed to delete course'
            ];
        }
        
        return [
            'status' => 'success',
            'message' => 'Course deleted successfully'
        ];
    }

    // Toggle course status (Admin and Instructor only)
    public function toggleStatus($course_id) {
        if (!in_array($this->user['role'], ['admin', 'instructor'])) {
            return [
                'status' => 'error',
                'message' => 'Access denied'
            ];
        }
        
        $course = $this->course->getById($course_id);
        
        if (!$course) {
            return [
                'status' => 'error',
                'message' => 'Course not found'
            ];
        }
        
        // Check if instructor owns the course
        if ($this->user['role'] === 'instructor' && 
            $course['created_by'] !== $this->user['id']) {
            return [
                'status' => 'error',
                'message' => 'Access denied'
            ];
        }
        
        $success = $this->course->toggleStatus($course_id);
        
        if (!$success) {
            return [
                'status' => 'error',
                'message' => 'Failed to toggle course status'
            ];
        }
        
        return [
            'status' => 'success',
            'message' => 'Course status updated successfully'
        ];
    }

    // Get course statistics
    public function getStatistics($course_id) {
        $course = $this->course->getById($course_id);
        
        if (!$course) {
            return [
                'status' => 'error',
                'message' => 'Course not found'
            ];
        }
        
        // Check access permissions
        if ($this->user['role'] === 'instructor' && 
            $course['created_by'] !== $this->user['id']) {
            return [
                'status' => 'error',
                'message' => 'Access denied'
            ];
        }
        
        if ($this->user['role'] === 'student' && 
            $course['status'] !== 'public') {
            return [
                'status' => 'error',
                'message' => 'Access denied'
            ];
        }
        
        $stats = $this->course->getStatistics($course_id);
        
        return [
            'status' => 'success',
            'data' => $stats
        ];
    }
} 