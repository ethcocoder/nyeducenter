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
$category_id = $input['category_id'] ?? null;

if (!$category_id) {
    echo json_encode(['error' => 'Category ID is required']);
    exit;
}

try {
    $db = Database::getInstance();
    
    // Start transaction
    $db->beginTransaction();
    
    // Get all books in this category
    $stmt = $db->prepare("SELECT file_path FROM system_books WHERE grade_category_id = ?");
    $stmt->execute([$category_id]);
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Delete book files
    foreach ($books as $book) {
        if (file_exists($book['file_path'])) {
            unlink($book['file_path']);
        }
    }
    
    // Delete books
    $stmt = $db->prepare("DELETE FROM system_books WHERE grade_category_id = ?");
    $stmt->execute([$category_id]);
    
    // Delete category
    $stmt = $db->prepare("DELETE FROM grade_categories WHERE id = ?");
    $stmt->execute([$category_id]);
    
    $db->commit();
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode(['error' => 'Failed to delete category']);
}