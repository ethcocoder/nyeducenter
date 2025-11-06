<?php
class LearningPath {
    private $table_name;
    private $conn;

    function __construct($db_conn) {
        $this->conn = $db_conn;
        $this->table_name = "learning_paths";
    }

    function count() {
        try {
            $sql = 'SELECT path_id FROM '. $this->table_name;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->rowCount();
        } catch(PDOException $e) {
            error_log("LearningPath count error: " . $e->getMessage());
            return 0;
        }
    }

    function countByStatus($status) {
        try {
            $sql = 'SELECT path_id FROM '. $this->table_name.' WHERE status = ?';
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$status]);
            return $stmt->rowCount();
        } catch(PDOException $e) {
            error_log("LearningPath countByStatus error: " . $e->getMessage());
            return 0;
        }
    }

    function getAll($offset = 0, $limit = 10) {
        try {
            $sql = 'SELECT lp.*, i.first_name, i.last_name, 
                    (SELECT COUNT(*) FROM learning_path_enrollments WHERE path_id = lp.path_id) as enrolled_students 
                    FROM '. $this->table_name.' lp 
                    LEFT JOIN instructor i ON lp.instructor_id = i.instructor_id 
                    ORDER BY lp.created_at DESC 
                    LIMIT :offset, :limit';
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("LearningPath getAll error: " . $e->getMessage());
            return [];
        }
    }

    function getById($id) {
        try {
            $sql = 'SELECT lp.*, i.first_name, i.last_name, 
                    (SELECT COUNT(*) FROM learning_path_enrollments WHERE path_id = lp.path_id) as enrolled_students 
                    FROM '. $this->table_name.' lp 
                    LEFT JOIN instructor i ON lp.instructor_id = i.instructor_id 
                    WHERE lp.path_id = ?';
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch(PDOException $e) {
            error_log("LearningPath getById error: " . $e->getMessage());
            return null;
        }
    }

    function insert($data) {
        try {
            $sql = 'INSERT INTO '. $this->table_name.'(title, description, instructor_id, subject, level, duration, status) 
                    VALUES(?, ?, ?, ?, ?, ?, ?)';
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute($data);
        } catch(PDOException $e) {
            error_log("LearningPath insert error: " . $e->getMessage());
            return false;
        }
    }

    function update($id, $data) {
        try {
            $sql = 'UPDATE '. $this->table_name.' 
                    SET title = ?, description = ?, subject = ?, level = ?, duration = ?, status = ? 
                    WHERE path_id = ?';
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([...$data, $id]);
        } catch(PDOException $e) {
            error_log("LearningPath update error: " . $e->getMessage());
            return false;
        }
    }

    function delete($id) {
        try {
            $sql = 'DELETE FROM '. $this->table_name.' WHERE path_id = ?';
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$id]);
        } catch(PDOException $e) {
            error_log("LearningPath delete error: " . $e->getMessage());
            return false;
        }
    }

    function getStudents($path_id, $offset = 0, $limit = 10) {
        try {
            $sql = 'SELECT s.*, lpe.enrolled_at, lpe.status, lpe.progress 
                    FROM learning_path_enrollments lpe 
                    JOIN student s ON lpe.student_id = s.student_id 
                    WHERE lpe.path_id = ? 
                    ORDER BY lpe.enrolled_at DESC 
                    LIMIT :offset, :limit';
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute([$path_id]);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("LearningPath getStudents error: " . $e->getMessage());
            return [];
        }
    }

    function getStudentCount($path_id) {
        try {
            $sql = 'SELECT COUNT(*) as count FROM learning_path_enrollments WHERE path_id = ?';
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$path_id]);
            $result = $stmt->fetch();
            return $result['count'];
        } catch(PDOException $e) {
            error_log("LearningPath getStudentCount error: " . $e->getMessage());
            return 0;
        }
    }

    function getTotalEnrollments() {
        try {
            $sql = 'SELECT COUNT(*) as count FROM learning_path_enrollments';
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result['count'];
        } catch(PDOException $e) {
            error_log("LearningPath getTotalEnrollments error: " . $e->getMessage());
            return 0;
        }
    }

    function getStudentPaths($student_id, $offset = 0, $limit = 10) {
        try {
            $sql = 'SELECT lp.*, i.first_name, i.last_name, lpe.enrolled_at, lpe.status as enrollment_status, lpe.progress 
                    FROM learning_path_enrollments lpe 
                    JOIN '. $this->table_name.' lp ON lpe.path_id = lp.path_id 
                    LEFT JOIN instructor i ON lp.instructor_id = i.instructor_id 
                    WHERE lpe.student_id = ? 
                    ORDER BY lpe.enrolled_at DESC 
                    LIMIT :offset, :limit';
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute([$student_id]);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("LearningPath getStudentPaths error: " . $e->getMessage());
            return [];
        }
    }

    function getStudentPathCount($student_id) {
        try {
            $sql = 'SELECT COUNT(*) as count 
                    FROM learning_path_enrollments 
                    WHERE student_id = ?';
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$student_id]);
            $result = $stmt->fetch();
            return $result['count'];
        } catch(PDOException $e) {
            error_log("LearningPath getStudentPathCount error: " . $e->getMessage());
            return 0;
        }
    }
}
?> 