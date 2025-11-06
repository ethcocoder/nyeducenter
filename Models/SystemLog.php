<?php 
class SystemLog {
    private $table_name;
    private $conn;

    function __construct($db_conn) {
        $this->conn = $db_conn;
        $this->table_name = "system_log";
    }

    function getRecent($limit = 50) {
        try {
            $sql = 'SELECT * FROM '. $this->table_name.' ORDER BY created_at DESC LIMIT :limit';
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("SystemLog getRecent error: " . $e->getMessage());
            return [];
        }
    }

    function getFiltered($action = '', $user_type = '', $start_date = '', $end_date = '') {
        try {
            $sql = 'SELECT * FROM '. $this->table_name.' WHERE 1=1';
            $params = [];
            
            if ($action) {
                $sql .= ' AND action = ?';
                $params[] = $action;
            }
            
            if ($user_type) {
                $sql .= ' AND user_type = ?';
                $params[] = $user_type;
            }
            
            if ($start_date) {
                $sql .= ' AND created_at >= ?';
                $params[] = $start_date . ' 00:00:00';
            }
            
            if ($end_date) {
                $sql .= ' AND created_at <= ?';
                $params[] = $end_date . ' 23:59:59';
            }
            
            $sql .= ' ORDER BY created_at DESC';
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("SystemLog getFiltered error: " . $e->getMessage());
            return [];
        }
    }

    function add($action, $description, $user_id = null, $user_type = null) {
        try {
            $sql = 'INSERT INTO '. $this->table_name.'(action, description, user_id, user_type, created_at) 
                    VALUES(?, ?, ?, ?, NOW())';
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$action, $description, $user_id, $user_type]);
        } catch(PDOException $e) {
            error_log("SystemLog add error: " . $e->getMessage());
            return false;
        }
    }

    function clearOldLogs($days = 30) {
        try {
            $sql = 'DELETE FROM '. $this->table_name.' WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)';
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$days]);
        } catch(PDOException $e) {
            error_log("SystemLog clearOldLogs error: " . $e->getMessage());
            return false;
        }
    }

    function getActiveUsers($days = 30) {
        try {
            $sql = "SELECT COUNT(DISTINCT user_id) as active_users FROM $this->table_name WHERE action = 'Login' AND user_id IS NOT NULL AND created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':days', $days, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            return $row ? $row['active_users'] : 0;
        } catch(PDOException $e) {
            error_log('SystemLog getActiveUsers error: ' . $e->getMessage());
            return 0;
        }
    }
}
?> 