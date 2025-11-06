<?php
session_start();
require_once '../../Database.php';
require_once '../../Models/Student.php';
require_once '../../Models/Instructor.php';
require_once '../../Models/Admin.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Debug POST data
        error_log("POST data received: " . print_r($_POST, true));
        
        $db = new Database();
        $conn = $db->getConnection();
        
        // Get form data
        $type = strtolower($_POST['type'] ?? '');
        $id = $_POST['id'] ?? '';
        $admin_password = $_POST['admin_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        error_log("Processing password reset for type: " . $type . ", ID: " . $id);

        // Validate input
        if (empty($type) || empty($id) || empty($admin_password) || empty($new_password) || empty($confirm_password)) {
            throw new Exception("All fields are required");
        }

        if ($new_password !== $confirm_password) {
            throw new Exception("Passwords do not match");
        }

        if (strlen($new_password) < 6) {
            throw new Exception("Password must be at least 6 characters long");
        }

        // Verify admin password
        $admin = new Admin($conn);
        if (!$admin->authenticate($_SESSION['username'], $admin_password)) {
            throw new Exception("Invalid admin password");
        }

        // Hash new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        error_log("Generated password hash: " . $hashed_password);
        
        // Reset password based on type
        if ($type === 'student') {
            $query = "UPDATE student SET password = ? WHERE student_id = ?";
        } else if ($type === 'instructor') {
            $query = "UPDATE instructor SET password = ? WHERE instructor_id = ?";
        } else {
            throw new Exception("Invalid user type: " . $type);
        }
        
        error_log("Executing query: " . $query . " with ID: " . $id);
        
        $stmt = $conn->prepare($query);
        $result = $stmt->execute([$hashed_password, $id]);
        
        if (!$result) {
            error_log("Database error: " . print_r($stmt->errorInfo(), true));
            throw new Exception("Failed to update password");
        }
        
        error_log("Password reset successful for " . $type . " ID: " . $id);
        
        $_SESSION['success'] = "Password reset successfully";
        header("Location: ../index.php");
        exit();

    } catch (Exception $e) {
        error_log("Password reset error: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        
        $_SESSION['error'] = "Password reset failed: " . $e->getMessage();
        
        // Redirect back with correct parameters based on type
        if ($type === 'student') {
            header("Location: ../Reset-Password.php?for=student&student_id=" . urlencode($id));
        } else if ($type === 'instructor') {
            header("Location: ../Reset-Password.php?for=instructor&instructor_id=" . urlencode($id));
        } else {
            header("Location: ../index.php");
        }
        exit();
    }
} else {
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    header("Location: ../index.php");
    exit();
}
?> 