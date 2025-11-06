<?php

class Chapter {
    private $table_name = "chapter";
    private $conn;
    
    // Chapter properties
    private $chapter_id;
    private $course_id;
    private $title;
    private $description;
    private $week_number;
    private $order;
    private $created_at;
    private $updated_at;

    // Module objectives
    private $module_objectives = [];

    public function __construct($db_conn) {
        $this->conn = $db_conn;
    }

    // Create a new chapter
    public function create($data) {
        try {
            $sql = "INSERT INTO " . $this->table_name . " (
                course_id, title, description, week_number,
                `order`, created_at
            ) VALUES (?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                $data['course_id'],
                $data['title'],
                $data['description'],
                $data['week_number'],
                $data['order']
            ]);
            
            $chapter_id = $this->conn->lastInsertId();
            
            // Add module objectives if provided
            if (!empty($data['module_objectives'])) {
                $this->addModuleObjectives($chapter_id, $data['module_objectives']);
            }
            
            return $chapter_id;
        } catch(PDOException $e) {
            return false;
        }
    }

    // Add module objectives
    private function addModuleObjectives($chapter_id, $objectives) {
        try {
            $sql = "INSERT INTO module_objective (
                chapter_id, objective_number, description,
                bloom_level, created_at
            ) VALUES (?, ?, ?, ?, NOW())";
            
            $stmt = $this->conn->prepare($sql);
            
            foreach ($objectives as $objective) {
                $stmt->execute([
                    $chapter_id,
                    $objective['number'],
                    $objective['description'],
                    $objective['bloom_level']
                ]);
            }
            
            return true;
        } catch(PDOException $e) {
            return false;
        }
    }

    // Get chapter by ID
    public function getById($chapter_id) {
        try {
            // Get chapter details
            $sql = "SELECT * FROM " . $this->table_name . " WHERE chapter_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$chapter_id]);
            $chapter = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($chapter) {
                // Get module objectives
                $sql = "SELECT * FROM module_objective 
                        WHERE chapter_id = ? 
                        ORDER BY objective_number ASC";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$chapter_id]);
                $chapter['module_objectives'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Get topics
                $sql = "SELECT * FROM topic 
                        WHERE chapter_id = ? 
                        ORDER BY `order` ASC";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$chapter_id]);
                $chapter['topics'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            return $chapter;
        } catch(PDOException $e) {
            return false;
        }
    }

    // Get chapters by course ID
    public function getByCourseId($course_id) {
        try {
            $sql = "SELECT * FROM " . $this->table_name . " 
                    WHERE course_id = ? 
                    ORDER BY week_number ASC, `order` ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$course_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
    }

    // Update chapter
    public function update($chapter_id, $data) {
        try {
            $sql = "UPDATE " . $this->table_name . " SET 
                title = ?, description = ?, 
                week_number = ?, `order` = ?,
                updated_at = NOW()
                WHERE chapter_id = ?";
            
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                $data['title'],
                $data['description'],
                $data['week_number'],
                $data['order'],
                $chapter_id
            ]);
        } catch(PDOException $e) {
            return false;
        }
    }

    // Update module objectives
    public function updateModuleObjectives($chapter_id, $objectives) {
        try {
            $this->conn->beginTransaction();
            
            // Delete existing objectives
            $sql = "DELETE FROM module_objective WHERE chapter_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$chapter_id]);
            
            // Add new objectives
            $this->addModuleObjectives($chapter_id, $objectives);
            
            $this->conn->commit();
            return true;
        } catch(PDOException $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    // Delete chapter
    public function delete($chapter_id) {
        try {
            $this->conn->beginTransaction();
            
            // Delete module objectives
            $sql = "DELETE FROM module_objective WHERE chapter_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$chapter_id]);
            
            // Delete topics
            $sql = "DELETE FROM topic WHERE chapter_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$chapter_id]);
            
            // Delete chapter
            $sql = "DELETE FROM " . $this->table_name . " WHERE chapter_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$chapter_id]);
            
            $this->conn->commit();
            return true;
        } catch(PDOException $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    // Reorder chapters
    public function reorder($course_id, $chapter_orders) {
        try {
            $this->conn->beginTransaction();
            
            foreach ($chapter_orders as $chapter_id => $order) {
                $sql = "UPDATE " . $this->table_name . " 
                        SET `order` = ? 
                        WHERE chapter_id = ? AND course_id = ?";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$order, $chapter_id, $course_id]);
            }
            
            $this->conn->commit();
            return true;
        } catch(PDOException $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    // Get chapter statistics
    public function getStatistics($chapter_id) {
        try {
            $sql = "SELECT 
                (SELECT COUNT(*) FROM topic WHERE chapter_id = ?) as total_topics,
                (SELECT COUNT(*) FROM quiz WHERE chapter_id = ?) as total_quizzes,
                (SELECT AVG(score) FROM quiz_attempt qa 
                 JOIN quiz q ON qa.quiz_id = q.quiz_id 
                 WHERE q.chapter_id = ?) as average_quiz_score";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$chapter_id, $chapter_id, $chapter_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
    }
} 