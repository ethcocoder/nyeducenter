<?php

class QuizAttempt {
    private $table_name = "quiz_attempt";
    private $answer_table = "quiz_answer";
    private $conn;
    
    // Quiz attempt properties
    private $attempt_id;
    private $quiz_id;
    private $student_id;
    private $start_time;
    private $end_time;
    private $score;
    private $status;

    public function __construct($db_conn) {
        $this->conn = $db_conn;
    }

    // Start a new quiz attempt
    public function start($quiz_id, $student_id) {
        try {
            $sql = "INSERT INTO " . $this->table_name . " (
                quiz_id, student_id, start_time, status
            ) VALUES (?, ?, NOW(), 'in_progress')";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$quiz_id, $student_id]);
            return $this->conn->lastInsertId();
        } catch(PDOException $e) {
            return false;
        }
    }

    // Submit a quiz attempt
    public function submit($attempt_id, $answers) {
        try {
            $this->conn->beginTransaction();

            // Calculate score
            $score = $this->calculateScore($answers);
            
            // Update attempt
            $sql = "UPDATE " . $this->table_name . " 
                    SET end_time = NOW(), score = ?, status = 'completed'
                    WHERE attempt_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$score, $attempt_id]);

            // Save answers
            foreach ($answers as $answer) {
                $sql = "INSERT INTO " . $this->answer_table . " (
                    attempt_id, question_id, selected_option_id,
                    text_answer, is_correct, points_earned
                ) VALUES (?, ?, ?, ?, ?, ?)";
                
                $stmt = $this->conn->prepare($sql);
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
            return false;
        }
    }

    // Get attempt by ID
    public function getById($attempt_id) {
        try {
            $sql = "SELECT * FROM " . $this->table_name . " WHERE attempt_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$attempt_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
    }

    // Get attempts by quiz ID
    public function getByQuizId($quiz_id) {
        try {
            $sql = "SELECT * FROM " . $this->table_name . " 
                    WHERE quiz_id = ? 
                    ORDER BY start_time DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$quiz_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
    }

    // Get attempts by student ID
    public function getByStudentId($student_id) {
        try {
            $sql = "SELECT * FROM " . $this->table_name . " 
                    WHERE student_id = ? 
                    ORDER BY start_time DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$student_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
    }

    // Get attempt answers
    public function getAnswers($attempt_id) {
        try {
            $sql = "SELECT * FROM " . $this->answer_table . " 
                    WHERE attempt_id = ? 
                    ORDER BY question_id ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$attempt_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
    }

    // Calculate score for answers
    private function calculateScore($answers) {
        $total_points = 0;
        foreach ($answers as $answer) {
            $total_points += $answer['points_earned'];
        }
        return $total_points;
    }

    // Get attempt statistics
    public function getStatistics($quiz_id) {
        try {
            $sql = "SELECT 
                COUNT(*) as total_attempts,
                AVG(score) as average_score,
                MIN(score) as lowest_score,
                MAX(score) as highest_score,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_attempts
                FROM " . $this->table_name . "
                WHERE quiz_id = ?";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$quiz_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
    }

    // Check if student has active attempt
    public function hasActiveAttempt($quiz_id, $student_id) {
        try {
            $sql = "SELECT attempt_id FROM " . $this->table_name . " 
                    WHERE quiz_id = ? AND student_id = ? 
                    AND status = 'in_progress'";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$quiz_id, $student_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
    }
} 