<?php
require_once '../includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$book_id = $input['book_id'] ?? 0;
$book_type = $input['book_type'] ?? '';

if (!$book_id || !$book_type) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Add to recent activity
try {
    $db = Database::getInstance();
    
    // Remove existing entry for this book to avoid duplicates
    $stmt = $db->prepare("DELETE FROM recent_activity WHERE user_id = ? AND book_id = ? AND book_type = ?");
    $stmt->execute([$user_id, $book_id, $book_type]);
    
    // Add new entry
    $stmt = $db->prepare("INSERT INTO recent_activity (user_id, book_id, book_type, opened_at) VALUES (?, ?, ?, NOW())");
    $result = $stmt->execute([$user_id, $book_id, $book_type]);
    
    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Failed to add recent activity');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error', 'message' => $e->getMessage()]);
}