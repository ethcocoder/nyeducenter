<?php 
class SchoolDepartment {
    private $table_name;
    private $conn;

    function __construct($db_conn) {
        $this->conn = $db_conn;
        $this->table_name = "school_departments";
    }

    function getAll() {
        try {
            $sql = 'SELECT * FROM '. $this->table_name.' ORDER BY department_name ASC';
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("SchoolDepartment getAll error: " . $e->getMessage());
            return [];
        }
    }

    function getById($id) {
        try {
            $sql = 'SELECT * FROM '. $this->table_name.' WHERE department_id = ?';
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch(PDOException $e) {
            error_log("SchoolDepartment getById error: " . $e->getMessage());
            return null;
        }
    }

    function insert($data) {
        try {
            $sql = 'INSERT INTO '. $this->table_name.'(department_name, description) VALUES(?, ?)';
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute($data);
        } catch(PDOException $e) {
            error_log("SchoolDepartment insert error: " . $e->getMessage());
            return false;
        }
    }

    function update($id, $data) {
        try {
            $sql = 'UPDATE '. $this->table_name.' SET department_name = ?, description = ? WHERE department_id = ?';
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([...$data, $id]);
        } catch(PDOException $e) {
            error_log("SchoolDepartment update error: " . $e->getMessage());
            return false;
        }
    }

    function delete($id) {
        try {
            $sql = 'DELETE FROM '. $this->table_name.' WHERE department_id = ?';
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$id]);
        } catch(PDOException $e) {
            error_log("SchoolDepartment delete error: " . $e->getMessage());
            return false;
        }
    }
}
?> 