<?php
require_once '../includes/functions.php';

// Ensure user is admin
if (!is_admin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

// Decode JSON input
$input = json_decode(file_get_contents('php://input'), true);
$book_id = $input['book_id'] ?? null;

if (!$book_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Book ID is required']);
    exit;
}

try {
    $db = Database::getInstance();

    // Get book info
    $stmt = $db->prepare("SELECT file_path FROM system_books WHERE id = ?");
    $stmt->execute([$book_id]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$book) {
        http_response_code(404);
        echo json_encode(['error' => 'Book not found']);
        exit;
    }

    // Safely resolve file path inside allowed directory
    $filePath = __DIR__ . '/../' . ltrim($book['file_path'], '/');

    // Check if file exists and delete
    if (file_exists($filePath)) {
        if (!unlink($filePath)) {
            throw new Exception('Failed to delete file from server.');
        }
    }

    // Start transaction
    $db->beginTransaction();

    // Delete related activity
    $stmt = $db->prepare("DELETE FROM recent_activity WHERE book_id = ? AND book_type = 'system'");
    $stmt->execute([$book_id]);

    // Delete book entry
    $stmt = $db->prepare("DELETE FROM system_books WHERE id = ?");
    $stmt->execute([$book_id]);

    // Commit transaction
    $db->commit();

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if (isset($db)) {
        $db->rollBack();
    }

    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to delete book',
        'details' => $e->getMessage()
    ]);
}
