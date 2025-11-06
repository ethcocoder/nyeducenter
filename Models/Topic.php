<?php

class Topic {
    private $table_name = "topic";
    private $conn;
    
    // Topic properties
    private $topic_id;
    private $chapter_id;
    private $title;
    private $description;
    private $content_type;
    private $content;
    private $order;
    private $created_at;
    private $updated_at;

    public function __construct($db_conn) {
        $this->conn = $db_conn;
    }

    // Create a new topic
    public function create($data) {
        try {
            $sql = "INSERT INTO " . $this->table_name . " (
                chapter_id, title, description, content_type,
                content, `order`, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                $data['chapter_id'],
                $data['title'],
                $data['description'],
                $data['content_type'],
                $data['content'],
                $data['order']
            ]);
            
            return $this->conn->lastInsertId();
        } catch(PDOException $e) {
            return false;
        }
    }

    // Get topic by ID
    public function getById($topic_id) {
        try {
            $sql = "SELECT * FROM " . $this->table_name . " WHERE topic_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$topic_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
    }

    // Get topics by chapter ID
    public function getByChapterId($chapter_id) {
        try {
            $sql = "SELECT * FROM " . $this->table_name . " 
                    WHERE chapter_id = ? 
                    ORDER BY `order` ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$chapter_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
    }

    // Update topic
    public function update($topic_id, $data) {
        try {
            $sql = "UPDATE " . $this->table_name . " SET 
                title = ?, description = ?, 
                content_type = ?, content = ?,
                `order` = ?, updated_at = NOW()
                WHERE topic_id = ?";
            
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                $data['title'],
                $data['description'],
                $data['content_type'],
                $data['content'],
                $data['order'],
                $topic_id
            ]);
        } catch(PDOException $e) {
            return false;
        }
    }

    // Delete topic
    public function delete($topic_id) {
        try {
            $sql = "DELETE FROM " . $this->table_name . " WHERE topic_id = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$topic_id]);
        } catch(PDOException $e) {
            return false;
        }
    }

    // Reorder topics
    public function reorder($chapter_id, $topic_orders) {
        try {
            $this->conn->beginTransaction();
            
            foreach ($topic_orders as $topic_id => $order) {
                $sql = "UPDATE " . $this->table_name . " 
                        SET `order` = ? 
                        WHERE topic_id = ? AND chapter_id = ?";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$order, $topic_id, $chapter_id]);
            }
            
            $this->conn->commit();
            return true;
        } catch(PDOException $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    // Get topic statistics
    public function getStatistics($topic_id) {
        try {
            $sql = "SELECT 
                (SELECT COUNT(*) FROM quiz WHERE topic_id = ?) as total_quizzes,
                (SELECT AVG(score) FROM quiz_attempt qa 
                 JOIN quiz q ON qa.quiz_id = q.quiz_id 
                 WHERE q.topic_id = ?) as average_quiz_score,
                (SELECT COUNT(*) FROM student_progress 
                 WHERE topic_id = ? AND status = 'completed') as completed_by";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$topic_id, $topic_id, $topic_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
    }

    // Update student progress
    public function updateProgress($topic_id, $student_id, $status) {
        try {
            $sql = "INSERT INTO student_progress (
                topic_id, student_id, status, updated_at
            ) VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE 
                status = ?, updated_at = NOW()";
            
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                $topic_id, $student_id, $status, $status
            ]);
        } catch(PDOException $e) {
            return false;
        }
    }

    // Get student progress
    public function getProgress($topic_id, $student_id) {
        try {
            $sql = "SELECT * FROM student_progress 
                    WHERE topic_id = ? AND student_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$topic_id, $student_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
    }
} 