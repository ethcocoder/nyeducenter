<?php 
class SupportRequest {
    private $table_name;
    private $conn;

    function __construct($db_conn) {
        $this->conn = $db_conn;
        $this->table_name = "support_requests";
    }

    function getAll($offset = 0, $limit = 10) {
        try {
            $sql = 'SELECT * FROM '. $this->table_name.' ORDER BY created_at DESC LIMIT :offset, :limit';
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("SupportRequest getAll error: " . $e->getMessage());
            return [];
        }
    }

    function count() {
        try {
            $sql = 'SELECT request_id FROM '. $this->table_name;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->rowCount();
        } catch(PDOException $e) {
            error_log("SupportRequest count error: " . $e->getMessage());
            return 0;
        }
    }

    function getById($id) {
        try {
            $sql = 'SELECT r.*, 
                    (SELECT GROUP_CONCAT(
                        CONCAT(
                            response_id, "|",
                            message, "|",
                            responder_name, "|",
                            created_at
                        )
                    ) FROM support_responses WHERE request_id = r.request_id) as responses
                    FROM '. $this->table_name.' r 
                    WHERE r.request_id = ?';
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id]);
            $request = $stmt->fetch();
            
            if ($request && $request['responses']) {
                $responses = [];
                foreach (explode(',', $request['responses']) as $response) {
                    list($id, $message, $name, $created_at) = explode('|', $response);
                    $responses[] = [
                        'response_id' => $id,
                        'message' => $message,
                        'responder_name' => $name,
                        'created_at' => $created_at
                    ];
                }
                $request['responses'] = $responses;
            } else {
                $request['responses'] = [];
            }
            
            return $request;
        } catch(PDOException $e) {
            error_log("SupportRequest getById error: " . $e->getMessage());
            return null;
        }
    }

    function insert($data) {
        try {
            $sql = 'INSERT INTO '. $this->table_name.'(user_id, user_type, subject, message, status) 
                    VALUES(?, ?, ?, ?, ?)';
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute($data);
        } catch(PDOException $e) {
            error_log("SupportRequest insert error: " . $e->getMessage());
            return false;
        }
    }

    function updateStatus($id, $status) {
        try {
            $sql = 'UPDATE '. $this->table_name.' SET status = ? WHERE request_id = ?';
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$status, $id]);
        } catch(PDOException $e) {
            error_log("SupportRequest updateStatus error: " . $e->getMessage());
            return false;
        }
    }

    function addResponse($request_id, $admin_id, $message) {
        try {
            $sql = 'INSERT INTO support_responses(request_id, admin_id, message) VALUES(?, ?, ?)';
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$request_id, $admin_id, $message]);
        } catch(PDOException $e) {
            error_log("SupportRequest addResponse error: " . $e->getMessage());
            return false;
        }
    }
}
?> 