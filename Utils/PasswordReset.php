<?php
if (!class_exists('PasswordReset')) {
class PasswordReset {
    private const TOKEN_EXPIRY = 3600; // 1 hour
    private $conn;
    
    public function __construct($db_conn) {
        $this->conn = $db_conn;
    }
    
    public function generateResetToken($email, $role) {
        try {
            // Generate a secure token
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', time() + self::TOKEN_EXPIRY);
            
            // Store the token in the database
            $sql = "INSERT INTO password_resets (email, role, token, expiry) 
                    VALUES (?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$email, $role, $token, $expiry]);
            
            return $token;
        } catch (PDOException $e) {
            error_log("Error generating reset token: " . $e->getMessage());
            return false;
        }
    }
    
    public function validateToken($token) {
        try {
            $sql = "SELECT * FROM password_resets 
                    WHERE token = ? AND expiry > NOW() 
                    AND used = 0";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$token]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error validating token: " . $e->getMessage());
            return false;
        }
    }
    
    public function markTokenAsUsed($token) {
        try {
            $sql = "UPDATE password_resets SET used = 1 WHERE token = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$token]);
        } catch (PDOException $e) {
            error_log("Error marking token as used: " . $e->getMessage());
            return false;
        }
    }
    
    public function updatePassword($email, $role, $new_password) {
        try {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            switch ($role) {
                case 'student':
                    $table = 'student';
                    $id_field = 'student_id';
                    break;
                case 'instructor':
                    $table = 'instructor';
                    $id_field = 'instructor_id';
                    break;
                case 'admin':
                    $table = 'admin';
                    $id_field = 'admin_id';
                    break;
                default:
                    throw new Exception("Invalid role");
            }
            
            $sql = "UPDATE $table SET password = ? WHERE email = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$hashed_password, $email]);
        } catch (PDOException $e) {
            error_log("Error updating password: " . $e->getMessage());
            return false;
        }
    }
    
    public function cleanupExpiredTokens() {
        try {
            $sql = "DELETE FROM password_resets WHERE expiry < NOW() OR used = 1";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error cleaning up tokens: " . $e->getMessage());
            return false;
        }
    }
}
} 