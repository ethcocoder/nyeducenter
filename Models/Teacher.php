<?php
class Teacher {
    protected $conn;
    protected $table_name = "teachers";
    protected $teacher_id;
    protected $username;
    protected $password;
    protected $email;
    protected $first_name;
    protected $last_name;
    protected $created_at;

    public function __construct($db_conn) {
        $this->conn = $db_conn;
    }

    // Getters
    public function getTeacherId() {
        return $this->teacher_id;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getFirstName() {
        return $this->first_name;
    }

    public function getLastName() {
        return $this->last_name;
    }

    public function getCreatedAt() {
        return $this->created_at;
    }

    // Setters
    public function setTeacherId($id) {
        $this->teacher_id = $id;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function setPassword($password) {
        $this->password = password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]);
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setFirstName($first_name) {
        $this->first_name = $first_name;
    }

    public function setLastName($last_name) {
        $this->last_name = $last_name;
    }

    public function getTeacherByUsername($username) {
        try {
            $sql = 'SELECT * FROM ' . $this->table_name . ' WHERE username = ?';
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$username]);
            return $stmt->fetch();
        } catch(PDOException $e) {
            error_log("Teacher getTeacherByUsername error: " . $e->getMessage());
            throw new Exception("Failed to retrieve teacher: " . $e->getMessage());
        }
    }

    public function getTeacherById($id) {
        try {
            $sql = 'SELECT * FROM ' . $this->table_name . ' WHERE teacher_id = ?';
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id]);
            $result = $stmt->fetch();
            if (!$result) {
                throw new Exception("Teacher not found");
            }
            return $result;
        } catch(PDOException $e) {
            error_log("Teacher getTeacherById error: " . $e->getMessage());
            throw new Exception("Failed to retrieve teacher: " . $e->getMessage());
        }
    }

    public function updateProfile($teacher_id, $data) {
        try {
            $sql = 'UPDATE ' . $this->table_name . ' 
                    SET first_name = ?, last_name = ?, email = ? 
                    WHERE teacher_id = ?';
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $teacher_id
            ]);
        } catch(PDOException $e) {
            error_log("Teacher updateProfile error: " . $e->getMessage());
            throw new Exception("Failed to update teacher profile: " . $e->getMessage());
        }
    }

    public function changePassword($teacher_id, $current_password, $new_password) {
        try {
            // Get current password hash
            $sql = 'SELECT password FROM ' . $this->table_name . ' WHERE teacher_id = ?';
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$teacher_id]);
            $result = $stmt->fetch();

            if (!$result || !password_verify($current_password, $result['password'])) {
                throw new Exception("Current password is incorrect");
            }

            // Update password
            $sql = 'UPDATE ' . $this->table_name . ' 
                    SET password = ? 
                    WHERE teacher_id = ?';
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                password_hash($new_password, PASSWORD_ARGON2ID, [
                    'memory_cost' => 65536,
                    'time_cost' => 4,
                    'threads' => 3
                ]),
                $teacher_id
            ]);
        } catch(PDOException $e) {
            error_log("Teacher changePassword error: " . $e->getMessage());
            throw new Exception("Failed to change password: " . $e->getMessage());
        }
    }
} 