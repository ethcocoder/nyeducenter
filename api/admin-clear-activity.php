<?php
require_once '../includes/functions.php';

// Check if user is admin
if (!is_admin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

try {
    $db = Database::getInstance();
    
    $stmt = $db->prepare("DELETE FROM recent_activity");
    $stmt->execute();
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to clear recent activity']);
}