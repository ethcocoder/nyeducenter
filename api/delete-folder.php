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

$folder_id = $input['folder_id'] ?? 0;

if (!$folder_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing folder ID']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Verify folder ownership and delete
try {
    $db = Database::getInstance();
    
    // Start transaction
    $db->beginTransaction();
    
    // Get folder details
    $stmt = $db->prepare("SELECT * FROM user_folders WHERE id = ? AND user_id = ?");
    $stmt->execute([$folder_id, $user_id]);
    
    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Folder not found or access denied']);
        exit;
    }
    
    // Get all books in the folder to delete their files
    $stmt = $db->prepare("SELECT file_path FROM user_books WHERE folder_id = ?");
    $stmt->execute([$folder_id]);
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Delete book files
    foreach ($books as $book) {
        $file_path = '../' . $book['file_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    
    // Delete books from database
    $stmt = $db->prepare("DELETE FROM user_books WHERE folder_id = ?");
    $stmt->execute([$folder_id]);
    
    // Delete folder
    $stmt = $db->prepare("DELETE FROM user_folders WHERE id = ? AND user_id = ?");
    $result = $stmt->execute([$folder_id, $user_id]);
    
    if ($result) {
        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Folder and all books deleted successfully']);
    } else {
        $db->rollBack();
        throw new Exception('Failed to delete folder');
    }
    
} catch (Exception $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode(['error' => 'Database error', 'message' => $e->getMessage()]);
}