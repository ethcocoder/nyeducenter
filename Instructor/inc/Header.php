<?php
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'edupulsedb');

// Create database connection
try {
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS,
        array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'")
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Function to get database connection
function getDBConnection() {
    global $conn;
    return $conn;
}

// Function to check user role
function requireRole($allowedRoles) {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        header('Location: /login.php');
        exit();
    }
    
    if (!in_array($_SESSION['role'], $allowedRoles)) {
        header('Location: /unauthorized.php');
        exit();
    }
}

// Function to get user data
function getUserData($userId) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM instructor WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Function to set flash message
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

// Function to get and clear flash message
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

// Function to sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to format date
function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

// Function to format datetime
function formatDateTime($datetime) {
    return date('F j, Y g:i A', strtotime($datetime));
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to get current user role
function getCurrentUserRole() {
    return $_SESSION['role'] ?? null;
}

// Function to get current user ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Function to redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Function to check if request is AJAX
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

// Function to send JSON response
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

// Function to handle errors
function handleError($message, $statusCode = 400) {
    if (isAjaxRequest()) {
        sendJsonResponse(['error' => $message], $statusCode);
    } else {
        setFlashMessage('error', $message);
        redirect($_SERVER['HTTP_REFERER'] ?? '/');
    }
}

// Function to handle success
function handleSuccess($message, $redirectUrl = null) {
    if (isAjaxRequest()) {
        sendJsonResponse(['success' => $message]);
    } else {
        setFlashMessage('success', $message);
        if ($redirectUrl) {
            redirect($redirectUrl);
        }
    }
}

// Check if user is logged in and has instructor role
requireRole(['instructor']);
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Instructor Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../assets/css/bootstrap.min.css" >
    <link rel="stylesheet" type="text/css" href="../assets/css/style.css">
    <link rel="stylesheet" type="text/css" href="../assets/css/richtext.min.css">
    <link rel="icon" type="image/x-icon" href="../favicon.ico">

    <script src="../assets/js/jquery-3.5.1.min.js"></script>
    <script src="../assets/js/jquery.richtext.min.js"></script>
  </head>
  <body style="display: flex; flex-direction: column; min-height: 100vh;">
<style>
    .main-content {
        flex-grow: 1;
    }
</style>
    <div class="container-fluid">
        <?php
        $flash = getFlashMessage();
        if ($flash) {
            echo '<div class="alert alert-' . $flash['type'] . ' alert-dismissible fade show" role="alert">';
            echo $flash['message'];
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            echo '</div>';
        }
        ?>
    </div>
  </body>
</html>