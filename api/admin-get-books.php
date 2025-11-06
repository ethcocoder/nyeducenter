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
    $pdo = $db->getConnection();

    // Get pagination parameters
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = ($page - 1) * $limit;

    // Get total number of books
    $totalStmt = $pdo->prepare("SELECT COUNT(*) FROM system_books");
    $totalStmt->execute();
    $totalBooks = $totalStmt->fetchColumn();
    
    $stmt = $pdo->prepare("
        SELECT sb.*, gc.grade_name as category_name
        FROM system_books sb
        JOIN grade_categories gc ON sb.grade_category_id = gc.id
        ORDER BY sb.title ASC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'books' => $books,
        'total' => $totalBooks,
        'page' => $page,
        'limit' => $limit
    ]);
    
} catch (Exception $e) {
    error_log("Error fetching books: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to fetch books']);
}