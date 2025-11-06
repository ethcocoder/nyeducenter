<?php
class LearningModule {
    private $conn;
    private $table = 'learning_modules';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getByPathId($path_id, $offset = 0, $limit = 10) {
        $query = "SELECT * FROM " . $this->table . "
                 WHERE path_id = :path_id
                 ORDER BY order_number ASC
                 LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':path_id', $path_id);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE module_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insert($data) {
        $query = "INSERT INTO " . $this->table . "
                 (path_id, title, description, duration, order_number, status)
                 VALUES
                 (:path_id, :title, :description, :duration, :order_number, 'Active')";
        
        $stmt = $this->conn->prepare($query);
        
        // Get the next order number
        $order_number = $this->getNextOrderNumber($data['path_id']);
        
        // Sanitize and bind data
        $stmt->bindParam(':path_id', $data['path_id']);
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':duration', $data['duration']);
        $stmt->bindParam(':order_number', $order_number);
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function update($id, $data) {
        $query = "UPDATE " . $this->table . "
                 SET title = :title,
                     description = :description,
                     duration = :duration,
                     status = :status
                 WHERE module_id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize and bind data
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':duration', $data['duration']);
        $stmt->bindParam(':status', $data['status']);
        
        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE module_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function updateOrder($id, $new_order) {
        $query = "UPDATE " . $this->table . "
                 SET order_number = :order_number
                 WHERE module_id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':order_number', $new_order);
        return $stmt->execute();
    }

    private function getNextOrderNumber($path_id) {
        $query = "SELECT MAX(order_number) as max_order FROM " . $this->table . " WHERE path_id = :path_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':path_id', $path_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($row['max_order'] ?? 0) + 1;
    }

    public function getResources($module_id) {
        $query = "SELECT * FROM module_resources 
                 WHERE module_id = :module_id 
                 ORDER BY order_number ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':module_id', $module_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStudentProgress($student_id, $module_id) {
        $query = "SELECT * FROM student_module_progress 
                 WHERE student_id = :student_id AND module_id = :module_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->bindParam(':module_id', $module_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateStudentProgress($student_id, $module_id, $progress_data) {
        $query = "INSERT INTO student_module_progress 
                 (student_id, module_id, status, progress_percentage, last_accessed)
                 VALUES 
                 (:student_id, :module_id, :status, :progress_percentage, NOW())
                 ON DUPLICATE KEY UPDATE
                 status = :status,
                 progress_percentage = :progress_percentage,
                 last_accessed = NOW()";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':student_id', $student_id);
        $stmt->bindParam(':module_id', $module_id);
        $stmt->bindParam(':status', $progress_data['status']);
        $stmt->bindParam(':progress_percentage', $progress_data['progress_percentage']);
        
        return $stmt->execute();
    }
}
?> 