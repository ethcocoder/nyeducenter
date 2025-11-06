<?php

class Question {
    private $table_name = "question";
    private $conn;
    
    // Question properties
    private $question_id;
    private $quiz_id;
    private $question_text_en;
    private $question_text_am;
    private $question_text_ti;
    private $question_text_om;
    private $question_type;
    private $points;
    private $order;

    public function __construct($db_conn) {
        $this->conn = $db_conn;
    }

    // Create a new question
    public function create($data) {
        try {
            $sql = "INSERT INTO " . $this->table_name . " (
                quiz_id, question_text_en, question_text_am, 
                question_text_ti, question_text_om, question_type, 
                points, `order`
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute($data);
        } catch(PDOException $e) {
            return false;
        }
    }

    // Get question by ID
    public function getById($question_id) {
        try {
            $sql = "SELECT * FROM " . $this->table_name . " WHERE question_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$question_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
    }

    // Get questions by quiz ID
    public function getByQuizId($quiz_id) {
        try {
            $sql = "SELECT * FROM " . $this->table_name . " 
                    WHERE quiz_id = ? 
                    ORDER BY `order` ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$quiz_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
    }

    // Update question
    public function update($question_id, $data) {
        try {
            $sql = "UPDATE " . $this->table_name . " SET 
                question_text_en = ?, question_text_am = ?, 
                question_text_ti = ?, question_text_om = ?,
                question_type = ?, points = ?, `order` = ?
                WHERE question_id = ?";
            
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute($data);
        } catch(PDOException $e) {
            return false;
        }
    }

    // Delete question
    public function delete($question_id) {
        try {
            $sql = "DELETE FROM " . $this->table_name . " WHERE question_id = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$question_id]);
        } catch(PDOException $e) {
            return false;
        }
    }

    // Reorder questions
    public function reorder($quiz_id, $question_orders) {
        try {
            $this->conn->beginTransaction();
            
            foreach ($question_orders as $question_id => $order) {
                $sql = "UPDATE " . $this->table_name . " 
                        SET `order` = ? 
                        WHERE question_id = ? AND quiz_id = ?";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$order, $question_id, $quiz_id]);
            }
            
            $this->conn->commit();
            return true;
        } catch(PDOException $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    // Get question statistics
    public function getStatistics($question_id) {
        try {
            $sql = "SELECT 
                COUNT(qa.answer_id) as total_attempts,
                SUM(qa.is_correct) as correct_answers,
                AVG(qa.points_earned) as average_points
                FROM quiz_answer qa
                WHERE qa.question_id = ?";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$question_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
    }
} 