<?php

class Mentor {
    private $table_name = "mentor";
    private $conn;
    
    // Mentor properties
    private $mentor_id;
    private $instructor_id;
    private $bio;
    private $expertise;
    private $availability;
    private $max_students;
    
    public function __construct($db_conn) {
        $this->conn = $db_conn;
    }
    
    // Create a new mentor
    public function create($data) {
        try {
            $sql = "INSERT INTO " . $this->table_name . " (
                instructor_id, bio, expertise, availability, max_students
            ) VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute($data);
        } catch(PDOException $e) {
            return false;
        }
    }
    
    // Get mentor by ID
    public function getById($mentor_id) {
        try {
            $sql = "SELECT m.*, i.first_name, i.last_name, i.email, i.profile_img
                    FROM " . $this->table_name . " m
                    JOIN instructor i ON m.instructor_id = i.instructor_id
                    WHERE m.mentor_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$mentor_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
    }
    
    // Get mentor by instructor ID
    public function getByInstructorId($instructor_id) {
        try {
            $sql = "SELECT * FROM " . $this->table_name . " 
                    WHERE instructor_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$instructor_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
    }
    
    // Get all mentors
    public function getAll() {
        try {
            $sql = "SELECT m.*, i.first_name, i.last_name, i.email, i.profile_img
                    FROM " . $this->table_name . " m
                    JOIN instructor i ON m.instructor_id = i.instructor_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
    }
    
    // Update mentor
    public function update($mentor_id, $data) {
        try {
            $sql = "UPDATE " . $this->table_name . " 
                    SET bio = ?, expertise = ?, availability = ?, max_students = ?
                    WHERE mentor_id = ?";
            
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([...$data, $mentor_id]);
        } catch(PDOException $e) {
            return false;
        }
    }
    
    // Get mentor's students
    public function getStudents($mentor_id) {
        try {
            $sql = "SELECT ms.*, s.first_name, s.last_name, s.email, s.profile_img,
                           np.title as program_title
                    FROM mentor_student ms
                    JOIN student s ON ms.student_id = s.student_id
                    JOIN nanodegree_program np ON ms.program_id = np.program_id
                    WHERE ms.mentor_id = ? AND ms.status = 'Active'
                    ORDER BY ms.start_date DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$mentor_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
    }
    
    // Assign student to mentor
    public function assignStudent($mentor_id, $student_id, $program_id) {
        try {
            $sql = "INSERT INTO mentor_student (mentor_id, student_id, program_id)
                    VALUES (?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$mentor_id, $student_id, $program_id]);
        } catch(PDOException $e) {
            return false;
        }
    }
    
    // Remove student from mentor
    public function removeStudent($mentor_id, $student_id, $program_id) {
        try {
            $sql = "UPDATE mentor_student 
                    SET status = 'Terminated', end_date = CURRENT_TIMESTAMP
                    WHERE mentor_id = ? AND student_id = ? AND program_id = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$mentor_id, $student_id, $program_id]);
        } catch(PDOException $e) {
            return false;
        }
    }
    
    // Get student's mentor
    public function getStudentMentor($student_id, $program_id) {
        try {
            $sql = "SELECT m.*, i.first_name, i.last_name, i.email, i.profile_img
                    FROM mentor_student ms
                    JOIN mentor m ON ms.mentor_id = m.mentor_id
                    JOIN instructor i ON m.instructor_id = i.instructor_id
                    WHERE ms.student_id = ? AND ms.program_id = ? AND ms.status = 'Active'";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$student_id, $program_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
    }
} 