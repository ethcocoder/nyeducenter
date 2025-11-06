<?php

class Project {
    private $table_name = "project";
    private $conn;
    
    // Project properties
    private $project_id;
    private $course_id;
    private $title;
    private $description;
    private $rubric;
    private $due_date;
    private $created_at;
    private $status;
    
    public function __construct($db_conn) {
        $this->conn = $db_conn;
    }
    
    // Create a new project
    public function create($data) {
        try {
            $sql = "INSERT INTO " . $this->table_name . " (
                course_id, title, description, rubric, due_date,
                created_at, status
            ) VALUES (?, ?, ?, ?, ?, NOW(), 'active')";
            
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                $data['course_id'],
                $data['title'],
                $data['description'],
                $data['rubric'],
                $data['due_date']
            ]);
        } catch(PDOException $e) {
            error_log("Project creation error: " . $e->getMessage());
            return false;
        }
    }
    
    // Get project by ID
    public function getById($project_id) {
        try {
            $sql = "SELECT p.*, c.title as course_title 
                    FROM " . $this->table_name . " p
                    JOIN course c ON p.course_id = c.course_id
                    WHERE p.project_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$project_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Project getById error: " . $e->getMessage());
            return false;
        }
    }
    
    // Get projects by course ID
    public function getByCourseId($course_id) {
        try {
            $sql = "SELECT * FROM " . $this->table_name . " 
                    WHERE course_id = ? 
                    ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$course_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Project getByCourseId error: " . $e->getMessage());
            return false;
        }
    }
    
    // Get project submissions
    public function getSubmissions($project_id) {
        try {
            $sql = "SELECT ps.*, s.first_name, s.last_name, s.email,
                           i.first_name as reviewer_first_name, i.last_name as reviewer_last_name
                    FROM project_submission ps
                    JOIN student s ON ps.student_id = s.student_id
                    LEFT JOIN instructor i ON ps.reviewer_id = i.instructor_id
                    WHERE ps.project_id = ?
                    ORDER BY ps.submission_date DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$project_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
    }
    
    // Get student's submission
    public function getStudentSubmission($project_id, $student_id) {
        try {
            $sql = "SELECT ps.*, i.first_name as reviewer_first_name, i.last_name as reviewer_last_name
                    FROM project_submission ps
                    LEFT JOIN instructor i ON ps.reviewer_id = i.instructor_id
                    WHERE ps.project_id = ? AND ps.student_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$project_id, $student_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Project getStudentSubmission error: " . $e->getMessage());
            return false;
        }
    }
    
    // Submit project
    public function submitProject($project_id, $student_id, $submission_url) {
        try {
            $sql = "INSERT INTO project_submission (
                project_id, student_id, submission_url, 
                submitted_at, status
            ) VALUES (?, ?, ?, NOW(), 'Under Review')
            ON DUPLICATE KEY UPDATE 
                submission_url = ?,
                submitted_at = NOW(),
                status = 'Under Review'";
            
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                $project_id, 
                $student_id, 
                $submission_url,
                $submission_url
            ]);
        } catch(PDOException $e) {
            error_log("Project submission error: " . $e->getMessage());
            return false;
        }
    }
    
    // Update project
    public function update($project_id, $data) {
        try {
            $sql = "UPDATE " . $this->table_name . " 
                    SET title = ?, description = ?, rubric = ?, 
                        due_date = ?, status = ?
                    WHERE project_id = ?";
            
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                $data['title'],
                $data['description'],
                $data['rubric'],
                $data['due_date'],
                $data['status'],
                $project_id
            ]);
        } catch(PDOException $e) {
            error_log("Project update error: " . $e->getMessage());
            return false;
        }
    }
    
    // Delete project
    public function delete($project_id) {
        try {
            $sql = "DELETE FROM " . $this->table_name . " WHERE project_id = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$project_id]);
        } catch(PDOException $e) {
            error_log("Project delete error: " . $e->getMessage());
            return false;
        }
    }
} 