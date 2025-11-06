<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Access denied.', 'categories' => []]);
    exit;
}

try {
    $pdo = get_db_connection(); // Assuming this function exists in db.php or functions.php
    
    // Get pagination parameters
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = ($page - 1) * $limit;

    // Get total number of categories
    $totalStmt = $pdo->prepare("SELECT COUNT(*) FROM grade_categories");
    $totalStmt->execute();
    $totalCategories = $totalStmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT id, grade_name, description, (SELECT COUNT(*) FROM system_books WHERE grade_category_id = gc.id) as book_count FROM grade_categories gc ORDER BY grade_name ASC LIMIT :limit OFFSET :offset");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'categories' => $categories,
        'total' => $totalCategories,
        'page' => $page,
        'limit' => $limit
    ]);
} catch (PDOException $e) {
    error_log("Error fetching categories: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error.', 'categories' => []]);
}
?>