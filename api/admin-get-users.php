<?php
header('Content-Type: application/json');
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Check if user is admin
if (!require_admin(true)) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit();
}

try {
    $db = Database::getInstance();
    
    // Get pagination parameters
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = ($page - 1) * $limit;

    // Get total number of users
    $totalStmt = $db->prepare("SELECT COUNT(*) FROM users");
    $totalStmt->execute();
    $totalUsers = $totalStmt->fetchColumn();
    
    $stmt = $db->prepare("
        SELECT id, username, email, role, created_at 
        FROM users 
        ORDER BY created_at DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'users' => $users,
        'total' => $totalUsers,
        'page' => $page,
        'limit' => $limit
    ]);
    
} catch (Exception $e) {
    error_log("Error fetching users: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch users']);
}