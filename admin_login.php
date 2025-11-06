<?php
session_start();
header('Content-Type: application/json');

// Database connection (replace with your actual credentials)
$host = "sql100.infinityfree.com";
$db = "if0_40118513_youth_center_db";
$user = "if0_40118513";
$pass = "changed1221";
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die(json_encode(['success' => false, 'message' => 'An unexpected error occurred. Please try again later.']));
}

// Function to sanitize input data
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = [];

    // Validate and sanitize username
    $input_username = isset($_POST['username']) ? sanitize_input($_POST['username']) : '';
    if (empty($input_username)) {
        $errors[] = 'Username is required.';
    }

    // Validate and sanitize password
    $input_password = isset($_POST['password']) ? sanitize_input($_POST['password']) : '';
    if (empty($input_password)) {
        $errors[] = 'Password is required.';
    }

    if (count($errors) > 0) {
        echo json_encode(['success' => false, 'message' => 'Validation errors', 'errors' => $errors]);
    } else {
        // Prepare a select statement to retrieve user data
        $stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM users WHERE username = :username");
        $stmt->bindParam(':username', $input_username, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Verify password
            if (password_verify($input_password, $user['password_hash'])) {
                // Password is correct, start a new session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                echo json_encode(['success' => true, 'message' => 'Login successful!', 'redirect' => 'admin_dashboard.html']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

// No need to close PDO connection explicitly, it closes automatically when script ends
?>