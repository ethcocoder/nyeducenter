<?php
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Access denied.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $bookId = $_GET['id'] ?? null;

    if (!$bookId) {
        echo json_encode(['success' => false, 'message' => 'Book ID is required.']);
        exit;
    }

    $conn = get_db_connection();
    $stmt = $conn->prepare("SELECT id, title, grade_category_id FROM system_books WHERE id = ?");
    $stmt->execute([$bookId]);

    if ($book = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode(['success' => true, 'book' => $book]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Book not found.']);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>