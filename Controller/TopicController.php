<?php
require_once 'Models/Course.php';
require_once 'Models/Chapter.php';
require_once 'Models/Topic.php';

class TopicController {
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

    // Get topic details
    public function show($topic_id) {
        $topic = $this->topic->getById($topic_id);
        
        if (!$topic) {
            return [
                'status' => 'error',
                'message' => 'Topic not found'
            ];
        }
        
        // Get chapter and course to check permissions
        $chapter = $this->chapter->getById($topic['chapter_id']);
        $course = $this->course->getById($chapter['course_id']);
        
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
        
        // Get student progress if applicable
        if ($this->user['role'] === 'student') {
            $progress = $this->topic->getProgress($topic_id, $this->user['id']);
            $topic['student_progress'] = $progress;
        }
        
        return [
            'status' => 'success',
            'data' => $topic
        ];
    }

    // Create new topic (Admin and Instructor only)
    public function create($data) {
        if (!in_array($this->user['role'], ['admin', 'instructor'])) {
            return [
                'status' => 'error',
                'message' => 'Access denied'
            ];
        }
        
        // Validate required fields
        $required = ['chapter_id', 'title', 'description', 'content_type', 'content'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return [
                    'status' => 'error',
                    'message' => "Missing required field: $field"
                ];
            }
        }
        
        // Check course ownership
        $chapter = $this->chapter->getById($data['chapter_id']);
        $course = $this->course->getById($chapter['course_id']);
        
        if ($this->user['role'] === 'instructor' && 
            $course['created_by'] !== $this->user['id']) {
            return [
                'status' => 'error',
                'message' => 'Access denied'
            ];
        }
        
        $topic_id = $this->topic->create($data);
        
        if (!$topic_id) {
            return [
                'status' => 'error',
                'message' => 'Failed to create topic'
            ];
        }
        
        return [
            'status' => 'success',
            'message' => 'Topic created successfully',
            'data' => ['topic_id' => $topic_id]
        ];
    }

    // Update topic (Admin and Instructor only)
    public function update($topic_id, $data) {
        if (!in_array($this->user['role'], ['admin', 'instructor'])) {
            return [
                'status' => 'error',
                'message' => 'Access denied'
            ];
        }
        
        $topic = $this->topic->getById($topic_id);
        
        if (!$topic) {
            return [
                'status' => 'error',
                'message' => 'Topic not found'
            ];
        }
        
        // Check course ownership
        $chapter = $this->chapter->getById($topic['chapter_id']);
        $course = $this->course->getById($chapter['course_id']);
        
        if ($this->user['role'] === 'instructor' && 
            $course['created_by'] !== $this->user['id']) {
            return [
                'status' => 'error',
                'message' => 'Access denied'
            ];
        }
        
        $success = $this->topic->update($topic_id, $data);
        
        if (!$success) {
            return [
                'status' => 'error',
                'message' => 'Failed to update topic'
            ];
        }
        
        return [
            'status' => 'success',
            'message' => 'Topic updated successfully'
        ];
    }

    // Delete topic (Admin and Instructor only)
    public function delete($topic_id) {
        if (!in_array($this->user['role'], ['admin', 'instructor'])) {
            return [
                'status' => 'error',
                'message' => 'Access denied'
            ];
        }
        
        $topic = $this->topic->getById($topic_id);
        
        if (!$topic) {
            return [
                'status' => 'error',
                'message' => 'Topic not found'
            ];
        }
        
        // Check course ownership
        $chapter = $this->chapter->getById($topic['chapter_id']);
        $course = $this->course->getById($chapter['course_id']);
        
        if ($this->user['role'] === 'instructor' && 
            $course['created_by'] !== $this->user['id']) {
            return [
                'status' => 'error',
                'message' => 'Access denied'
            ];
        }
        
        $success = $this->topic->delete($topic_id);
        
        if (!$success) {
            return [
                'status' => 'error',
                'message' => 'Failed to delete topic'
            ];
        }
        
        return [
            'status' => 'success',
            'message' => 'Topic deleted successfully'
        ];
    }

    // Reorder topics (Admin and Instructor only)
    public function reorder($chapter_id, $topic_orders) {
        if (!in_array($this->user['role'], ['admin', 'instructor'])) {
            return [
                'status' => 'error',
                'message' => 'Access denied'
            ];
        }
        
        // Check course ownership
        $chapter = $this->chapter->getById($chapter_id);
        $course = $this->course->getById($chapter['course_id']);
        
        if ($this->user['role'] === 'instructor' && 
            $course['created_by'] !== $this->user['id']) {
            return [
                'status' => 'error',
                'message' => 'Access denied'
            ];
        }
        
        $success = $this->topic->reorder($chapter_id, $topic_orders);
        
        if (!$success) {
            return [
                'status' => 'error',
                'message' => 'Failed to reorder topics'
            ];
        }
        
        return [
            'status' => 'success',
            'message' => 'Topics reordered successfully'
        ];
    }

    // Update student progress (Students only)
    public function updateProgress($topic_id, $status) {
        if ($this->user['role'] !== 'student') {
            return [
                'status' => 'error',
                'message' => 'Access denied'
            ];
        }
        
        $topic = $this->topic->getById($topic_id);
        
        if (!$topic) {
            return [
                'status' => 'error',
                'message' => 'Topic not found'
            ];
        }
        
        // Check if course is public
        $chapter = $this->chapter->getById($topic['chapter_id']);
        $course = $this->course->getById($chapter['course_id']);
        
        if ($course['status'] !== 'public') {
            return [
                'status' => 'error',
                'message' => 'Access denied'
            ];
        }
        
        $success = $this->topic->updateProgress($topic_id, $this->user['id'], $status);
        
        if (!$success) {
            return [
                'status' => 'error',
                'message' => 'Failed to update progress'
            ];
        }
        
        return [
            'status' => 'success',
            'message' => 'Progress updated successfully'
        ];
    }

    // Get topic statistics
    public function getStatistics($topic_id) {
        $topic = $this->topic->getById($topic_id);
        
        if (!$topic) {
            return [
                'status' => 'error',
                'message' => 'Topic not found'
            ];
        }
        
        // Check course ownership
        $chapter = $this->chapter->getById($topic['chapter_id']);
        $course = $this->course->getById($chapter['course_id']);
        
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
        
        $stats = $this->topic->getStatistics($topic_id);
        
        return [
            'status' => 'success',
            'data' => $stats
        ];
    }
} 