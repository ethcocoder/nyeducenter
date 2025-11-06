<?php

class Quiz {
    private $table_name = "quiz";
    private $conn;
    
    // Quiz properties
    private $quiz_id;
    private $course_id;
    private $title;
    private $description;
    private $passing_score;
    private $time_limit;
    private $status;
    private $created_at;
    
    public function __construct($db_conn) {
        $this->conn = $db_conn;
    }
    
    // Create a new quiz
    public function create($data) {
        try {
            $sql = "INSERT INTO " . $this->table_name . " (
                course_id, title, description, passing_score,
                time_limit, status, created_at
            ) VALUES (?, ?, ?, ?, ?, 'active', NOW())";
            
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                $data['course_id'],
                $data['title'],
                $data['description'],
                $data['passing_score'],
                $data['time_limit']
            ]);
        } catch(PDOException $e) {
            error_log("Quiz creation error: " . $e->getMessage());
            return false;
        }
    }
    
    // Get quiz by ID
    public function getById($quiz_id) {
        try {
            $sql = "SELECT q.*, c.title as course_title 
                    FROM " . $this->table_name . " q
                    JOIN course c ON q.course_id = c.course_id
                    WHERE q.quiz_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$quiz_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Quiz getById error: " . $e->getMessage());
            return false;
        }
    }
    
    // Get quizzes by course ID
    public function getByCourseId($course_id) {
        try {
            $sql = "SELECT * FROM " . $this->table_name . " 
                    WHERE course_id = ? 
                    ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$course_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Quiz getByCourseId error: " . $e->getMessage());
            return false;
        }
    }
    
    // Get quiz attempt
    public function getAttempt($attempt_id, $student_id) {
        try {
            $sql = "SELECT a.*, q.title as quiz_title, q.passing_score,
                    c.title as course_title, c.course_id
                    FROM quiz_attempt a
                    JOIN quiz q ON a.quiz_id = q.quiz_id
                    JOIN course c ON q.course_id = c.course_id
                    WHERE a.attempt_id = ? AND a.student_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$attempt_id, $student_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Quiz getAttempt error: " . $e->getMessage());
            return false;
        }
    }
    
    // Get quiz questions and answers
    public function getQuestionsAndAnswers($attempt_id, $quiz_id) {
        try {
            $sql = "SELECT q.*, a.selected_option_id, a.text_answer, 
                           a.is_correct, a.points_earned,
                           o.option_text as selected_option_text
                    FROM question q
                    LEFT JOIN quiz_answer a ON q.question_id = a.question_id 
                        AND a.attempt_id = ?
                    LEFT JOIN question_option o ON a.selected_option_id = o.option_id
                    WHERE q.quiz_id = ?
                    ORDER BY q.`order`";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$attempt_id, $quiz_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Quiz getQuestionsAndAnswers error: " . $e->getMessage());
            return false;
        }
    }
    
    // Start quiz attempt
    public function startAttempt($quiz_id, $student_id) {
        try {
            $sql = "INSERT INTO quiz_attempt (
                quiz_id, student_id, start_time, status
            ) VALUES (?, ?, NOW(), 'in_progress')";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$quiz_id, $student_id]);
            return $this->conn->lastInsertId();
        } catch(PDOException $e) {
            error_log("Quiz startAttempt error: " . $e->getMessage());
            return false;
        }
    }
    
    // Submit quiz attempt
    public function submitAttempt($attempt_id, $answers) {
        try {
            $this->conn->beginTransaction();
            
            // Calculate score
            $score = $this->calculateScore($answers);
            
            // Update attempt
            $sql = "UPDATE quiz_attempt 
                    SET end_time = NOW(), score = ?, status = 'completed'
                    WHERE attempt_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$score, $attempt_id]);
            
            // Save answers
            $sql = "INSERT INTO quiz_answer (
                attempt_id, question_id, selected_option_id,
                text_answer, is_correct, points_earned
            ) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            
            foreach ($answers as $answer) {
                $stmt->execute([
                    $attempt_id,
                    $answer['question_id'],
                    $answer['selected_option_id'] ?? null,
                    $answer['text_answer'] ?? null,
                    $answer['is_correct'],
                    $answer['points_earned']
                ]);
            }
            
            $this->conn->commit();
            return true;
        } catch(PDOException $e) {
            $this->conn->rollBack();
            error_log("Quiz submitAttempt error: " . $e->getMessage());
            return false;
        }
    }
    
    // Calculate quiz score
    private function calculateScore($answers) {
        $total_points = 0;
        foreach ($answers as $answer) {
            $total_points += $answer['points_earned'];
        }
        return $total_points;
    }
    
    // Update quiz
    public function update($quiz_id, $data) {
        try {
            $sql = "UPDATE " . $this->table_name . " 
                    SET title = ?, description = ?, passing_score = ?,
                        time_limit = ?, status = ?
                    WHERE quiz_id = ?";
            
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                $data['title'],
                $data['description'],
                $data['passing_score'],
                $data['time_limit'],
                $data['status'],
                $quiz_id
            ]);
        } catch(PDOException $e) {
            error_log("Quiz update error: " . $e->getMessage());
            return false;
        }
    }
    
    // Delete quiz
    public function delete($quiz_id) {
        try {
            $sql = "DELETE FROM " . $this->table_name . " WHERE quiz_id = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$quiz_id]);
        } catch(PDOException $e) {
            error_log("Quiz delete error: " . $e->getMessage());
            return false;
        }
    }
} 