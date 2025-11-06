<?php
require_once '../../Utils/Session.php';
require_once '../../Utils/Validation.php';
require_once '../../Utils/Util.php';
require_once '../../Database.php';
require_once '../../Models/Student.php';

// Initialize session
Session::init();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || !Session::validateCSRFToken($_POST['csrf_token'])) {
            throw new Exception("Invalid request");
        }

        $db = new Database();
        $conn = $db->getConnection();
        
        // Debug: Check database connection
        if (!$conn) {
            throw new Exception("Database connection failed");
        }

        // Get form data
        $username = Validation::clean($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $first_name = Validation::clean($_POST['fname'] ?? '');
        $last_name = Validation::clean($_POST['lname'] ?? '');
        $email = Validation::clean($_POST['email'] ?? '');
        $date_of_birth = Validation::clean($_POST['date_of_birth'] ?? '');

        // Debug: Log the received data
        error_log("Received registration data: " . print_r($_POST, true));

        // Validate input
        if (empty($username) || empty($password) || empty($first_name) || empty($last_name) || empty($email)) {
            throw new Exception("All fields are required");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        // Enhanced password validation
        if (strlen($password) < 8) {
            throw new Exception("Password must be at least 8 characters long");
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            throw new Exception("Password must contain at least one uppercase letter");
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            throw new Exception("Password must contain at least one lowercase letter");
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            throw new Exception("Password must contain at least one number");
        }
        
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            throw new Exception("Password must contain at least one special character");
        }

        // Create Student model instance
        $student = new Student($conn);

        // Check if username is unique
        if (!$student->is_username_unique($username)) {
            throw new Exception("Username already exists");
        }

        // Hash password with strong algorithm
        $hashed_password = password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]);

        // Prepare data for insertion
        $data = [
            $username,
            $first_name,
            $last_name,
            $email,
            $date_of_birth,
            $hashed_password
        ];

        // Insert student record
        if ($student->insert($data)) {
            $_SESSION['success'] = "Student registered successfully";
            header("Location: ../student-list.php");
            exit();
        } else {
            throw new Exception("Failed to register student");
        }

    } catch (Exception $e) {
        error_log("Student registration error: " . $e->getMessage());
        $_SESSION['error'] = $e->getMessage();
        header("Location: ../student-register.php");
        exit();
    }
} else {
    header("Location: ../student-register.php");
    exit();
}
?> 