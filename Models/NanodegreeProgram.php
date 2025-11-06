<?php

class NanodegreeProgram {
    private $table_name = "nanodegree_program";
    private $conn;
    
    // Program properties
    private $program_id;
    private $title;
    private $description;
    private $duration_weeks;
    private $level;
    private $price;
    private $status;
    private $partner_id;
    
    public function __construct($db_conn) {
        $this->conn = $db_conn;
    }
    
    // Create a new program
    public function create($data) {
        try {
            $sql = "INSERT INTO " . $this->table_name . " (
                title, description, duration_weeks, level, 
                price, partner_id
            ) VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute($data);
        } catch(PDOException $e) {
            return false;
        }
    }
    
    // Get program by ID
    public function getById($program_id) {
        try {
            $sql = "SELECT np.*, ip.name as partner_name 
                    FROM " . $this->table_name . " np
                    LEFT JOIN industry_partner ip ON np.partner_id = ip.partner_id
                    WHERE np.program_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$program_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
    }
    
    // Get all programs
    public function getAll() {
        try {
            $sql = "SELECT np.*, ip.name as partner_name 
                    FROM " . $this->table_name . " np
                    LEFT JOIN industry_partner ip ON np.partner_id = ip.partner_id
                    ORDER BY np.created_at DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
    }
    
    // Update program
    public function update($program_id, $data) {
        try {
            $sql = "UPDATE " . $this->table_name . " 
                    SET title = ?, description = ?, duration_weeks = ?,
                        level = ?, price = ?, partner_id = ?, status = ?
                    WHERE program_id = ?";
            
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([...$data, $program_id]);
        } catch(PDOException $e) {
            return false;
        }
    }
    
    // Get program courses
    public function getProgramCourses($program_id) {
        try {
            $sql = "SELECT c.*, pc.sequence_order 
                    FROM program_course pc
                    JOIN course c ON pc.course_id = c.course_id
                    WHERE pc.program_id = ?
                    ORDER BY pc.sequence_order ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$program_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
    }
    
    // Add course to program
    public function addCourse($program_id, $course_id, $sequence_order) {
        try {
            $sql = "INSERT INTO program_course (program_id, course_id, sequence_order)
                    VALUES (?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$program_id, $course_id, $sequence_order]);
        } catch(PDOException $e) {
            return false;
        }
    }
    
    // Remove course from program
    public function removeCourse($program_id, $course_id) {
        try {
            $sql = "DELETE FROM program_course 
                    WHERE program_id = ? AND course_id = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$program_id, $course_id]);
        } catch(PDOException $e) {
            return false;
        }
    }
} 