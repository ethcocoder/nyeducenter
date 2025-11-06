<?php
// Set session settings before starting the session
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.gc_maxlifetime', 3600); // 1 hour

// Start session
session_start();

require_once "../Utils/Session.php";
require_once "../Utils/Validation.php";
require_once "../Utils/Util.php";
require_once "../Database.php";
require_once "../Models/Admin.php";
require_once "../Models/Teacher.php";
require_once "../Models/Student.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || !Session::validateCSRFToken($_POST['csrf_token'])) {
            throw new Exception("Invalid request");
        }

        $username = Validation::clean($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = Validation::clean($_POST['role'] ?? '');

        if (empty($username) || empty($password) || empty($role)) {
            throw new Exception("All fields are required");
        }

        $db = new Database();
        $conn = $db->connect();

        switch ($role) {
            case 'admin':
                $admin = new Admin($conn);
                $admin_data = $admin->getAdminByUsername($username);
                
                if ($admin_data && password_verify($password, $admin_data['password'])) {
                    // Regenerate session ID for security
                    session_regenerate_id(true);
                    
                    $_SESSION['role'] = 'admin';
                    $_SESSION['username'] = $admin_data['username'];
                    $_SESSION['admin_id'] = $admin_data['admin_id'];
                    $_SESSION['last_activity'] = time();
                    
                    // Set secure session cookie
                    setcookie(session_name(), session_id(), [
                        'expires' => time() + 3600,
                        'path' => '/',
                        'domain' => '',
                        'secure' => true,
                        'httponly' => true,
                        'samesite' => 'Strict'
                    ]);
                    
                    header("Location: ../Admin/Dashboard.php");
                    exit();
                }
                break;

            case 'teacher':
                $teacher = new Teacher($conn);
                $teacher_data = $teacher->getTeacherByUsername($username);
                
                if ($teacher_data && password_verify($password, $teacher_data['password'])) {
                    session_regenerate_id(true);
                    
                    $_SESSION['role'] = 'teacher';
                    $_SESSION['username'] = $teacher_data['username'];
                    $_SESSION['teacher_id'] = $teacher_data['teacher_id'];
                    $_SESSION['last_activity'] = time();
                    
                    setcookie(session_name(), session_id(), [
                        'expires' => time() + 3600,
                        'path' => '/',
                        'domain' => '',
                        'secure' => true,
                        'httponly' => true,
                        'samesite' => 'Strict'
                    ]);
                    
                    header("Location: ../Teacher/Dashboard.php");
                    exit();
                }
                break;

            case 'student':
                $student = new Student($conn);
                $student_data = $student->getStudentByUsername($username);
                
                if ($student_data && password_verify($password, $student_data['password'])) {
                    session_regenerate_id(true);
                    
                    $_SESSION['role'] = 'student';
                    $_SESSION['username'] = $student_data['username'];
                    $_SESSION['student_id'] = $student_data['student_id'];
                    $_SESSION['last_activity'] = time();
                    
                    setcookie(session_name(), session_id(), [
                        'expires' => time() + 3600,
                        'path' => '/',
                        'domain' => '',
                        'secure' => true,
                        'httponly' => true,
                        'samesite' => 'Strict'
                    ]);
                    
                    header("Location: ../Student/Dashboard.php");
                    exit();
                }
                break;

            default:
                throw new Exception("Invalid role selected");
        }

        throw new Exception("Invalid username or password");

    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        $_SESSION['error'] = $e->getMessage();
        header("Location: ../login.php");
        exit();
    }
} else {
    header("Location: ../login.php");
    exit();
}