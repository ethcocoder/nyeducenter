<?php
require_once '../includes/functions.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

// Check if the user is an admin
if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Access denied.']);
    exit;
}

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);

$userId = $data['userId'] ?? null;
$username = $data['username'] ?? null;
$email = $data['email'] ?? null;
$role = $data['role'] ?? null;

// Validate input
if (empty($userId) || empty($username) || empty($email) || empty($role)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
    exit;
}

if (!in_array($role, ['user', 'admin'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid role.']);
    exit;
}

try {
    $pdo = get_db_connection();

    // Check if username or email already exists for another user
    $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = :username OR email = :email) AND id != :userId");
    $stmt->execute(['username' => $username, 'email' => $email, 'userId' => $userId]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Username or email already exists.']);
        exit;
    }

    // Update user data
    $stmt = $pdo->prepare("UPDATE users SET username = :username, email = :email, role = :role WHERE id = :userId");
    $stmt->execute([
        'username' => $username,
        'email' => $email,
        'role' => $role,
        'userId' => $userId
    ]);

    if ($stmt->rowCount()) {
        echo json_encode(['success' => true, 'message' => 'User updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made or user not found.']);
    }

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred.']);
}
?>