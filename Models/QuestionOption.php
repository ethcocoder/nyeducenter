<?php

class QuestionOption {
    private $table_name = "question_option";
    private $conn;
    
    // Question option properties
    private $option_id;
    private $question_id;
    private $option_text_en;
    private $option_text_am;
    private $option_text_ti;
    private $option_text_om;
    private $is_correct;
    private $order;

    public function __construct($db_conn) {
        $this->conn = $db_conn;
    }

    // Create a new question option
    public function create($data) {
        try {
            $sql = "INSERT INTO " . $this->table_name . " (
                question_id, option_text_en, option_text_am, 
                option_text_ti, option_text_om, is_correct, 
                `order`
            ) VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute($data);
        } catch(PDOException $e) {
            return false;
        }
    }

    // Get option by ID
    public function getById($option_id) {
        try {
            $sql = "SELECT * FROM " . $this->table_name . " WHERE option_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$option_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
    }

    // Get options by question ID
    public function getByQuestionId($question_id) {
        try {
            $sql = "SELECT * FROM " . $this->table_name . " 
                    WHERE question_id = ? 
                    ORDER BY `order` ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$question_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
    }

    // Update option
    public function update($option_id, $data) {
        try {
            $sql = "UPDATE " . $this->table_name . " SET 
                option_text_en = ?, option_text_am = ?, 
                option_text_ti = ?, option_text_om = ?,
                is_correct = ?, `order` = ?
                WHERE option_id = ?";
            
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute($data);
        } catch(PDOException $e) {
            return false;
        }
    }

    // Delete option
    public function delete($option_id) {
        try {
            $sql = "DELETE FROM " . $this->table_name . " WHERE option_id = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$option_id]);
        } catch(PDOException $e) {
            return false;
        }
    }

    // Delete all options for a question
    public function deleteByQuestionId($question_id) {
        try {
            $sql = "DELETE FROM " . $this->table_name . " WHERE question_id = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$question_id]);
        } catch(PDOException $e) {
            return false;
        }
    }

    // Reorder options
    public function reorder($question_id, $option_orders) {
        try {
            $this->conn->beginTransaction();
            
            foreach ($option_orders as $option_id => $order) {
                $sql = "UPDATE " . $this->table_name . " 
                        SET `order` = ? 
                        WHERE option_id = ? AND question_id = ?";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$order, $option_id, $question_id]);
            }
            
            $this->conn->commit();
            return true;
        } catch(PDOException $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    // Get option statistics
    public function getStatistics($option_id) {
        try {
            $sql = "SELECT 
                COUNT(qa.answer_id) as total_selections,
                (SELECT COUNT(*) FROM quiz_answer 
                 WHERE question_id = qo.question_id) as total_answers,
                qo.is_correct
                FROM question_option qo
                LEFT JOIN quiz_answer qa ON qa.selected_option_id = qo.option_id
                WHERE qo.option_id = ?
                GROUP BY qo.option_id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$option_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
    }
} 