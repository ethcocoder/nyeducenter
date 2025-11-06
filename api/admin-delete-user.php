<?php
require_once '../includes/functions.php';

// Check if user is admin
if (!is_admin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$user_id = $input['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['error' => 'User ID is required']);
    exit;
}

// Prevent deleting yourself
if ($user_id == $_SESSION['user_id']) {
    echo json_encode(['error' => 'Cannot delete your own account']);
    exit;
}

try {
    $db = Database::getInstance();
    
    // Start transaction
    $db->beginTransaction();
    
    // Delete user's books and folders
    $stmt = $db->prepare("SELECT id FROM user_folders WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $folders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($folders as $folder) {
        // Delete books in folder
        $stmt = $db->prepare("SELECT file_path FROM user_books WHERE folder_id = ?");
        $stmt->execute([$folder['id']]);
        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($books as $book) {
            if (file_exists($book['file_path'])) {
                unlink($book['file_path']);
            }
        }
        
        $stmt = $db->prepare("DELETE FROM user_books WHERE folder_id = ?");
        $stmt->execute([$folder['id']]);
    }
    
    // Delete folders
    $stmt = $db->prepare("DELETE FROM user_folders WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    // Delete recent activity
    $stmt = $db->prepare("DELETE FROM recent_activity WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    // Delete user
    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    
    $db->commit();
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode(['error' => 'Failed to delete user']);
}