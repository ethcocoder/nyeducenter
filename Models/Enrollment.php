<?php
class Enrollment {
    private $table_name = "enrollments";
    private $conn;

    function __construct($db_conn) {
        $this->conn = $db_conn;
    }

    function count() {
        try {
            $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['total'];
        } catch(PDOException $e) {
            error_log("Enrollment count error: " . $e->getMessage());
            return 0;
        }
    }

    function getCompletionRate() {
        try {
            $query = "SELECT (COUNT(CASE WHEN progress = 100 THEN 1 END) * 100.0 / COUNT(*)) as completion_rate FROM " . $this->table_name;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return round($row['completion_rate'], 1);
        } catch(PDOException $e) {
            error_log("Enrollment completion rate error: " . $e->getMessage());
            return 0;
        }
    }

    function getAverageProgress() {
        try {
            $query = "SELECT AVG(progress) as avg_progress FROM " . $this->table_name;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return round($row['avg_progress'], 1);
        } catch(PDOException $e) {
            error_log("Enrollment average progress error: " . $e->getMessage());
            return 0;
        }
    }

    function getEnrollmentTrends() {
        try {
            $query = "SELECT DATE(created_at) as date, COUNT(*) as count FROM " . $this->table_name . " GROUP BY DATE(created_at) ORDER BY date DESC LIMIT 30";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Enrollment trends error: " . $e->getMessage());
            return [];
        }
    }
}
?> 