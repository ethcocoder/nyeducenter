<?php
error_log("admin-update-book.php script started.");
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Access denied.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $bookId = $input['bookId'] ?? null;
    $bookTitle = $input['bookTitle'] ?? null;
    $bookAuthor = $input['bookAuthor'] ?? null;
    $bookCategory = $input['bookCategory'] ?? null;
    $bookDescription = $input['bookDescription'] ?? null;

    error_log("admin-update-book.php received: bookId=" . $bookId . ", bookTitle=" . $bookTitle . ", bookCategory=" . $bookCategory);

    if (!$bookId || !$bookTitle || !$bookAuthor || !$bookCategory || !$bookDescription) {
        echo json_encode(['success' => false, 'message' => 'Book ID, Title, Author, Category, and Description are required.']);
        exit;
    }

    $conn = get_db_connection();
    $stmt = $conn->prepare("UPDATE system_books SET title = ?, author = ?, grade_category_id = ?, description = ? WHERE id = ?");
    $stmt->execute([$bookTitle, $bookAuthor, $bookCategory, $bookDescription, $bookId]);

    error_log("admin-update-book.php rowCount: " . $stmt->rowCount());

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Book updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update book or no changes made.']);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>