<?php
require_once '../includes/functions.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!is_admin()) {
        echo json_encode(['success' => false, 'message' => 'Access denied.']);
        exit;
    }

    $categoryId = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

    if (!$categoryId) {
        echo json_encode(['success' => false, 'message' => 'Category ID is required.']);
        exit;
    }

    $conn = get_db_connection();
    $stmt = $conn->prepare("SELECT id, grade_name, description FROM grade_categories WHERE id = ?");
    $stmt->execute([$categoryId]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($category) {
        echo json_encode(['success' => true, 'category' => $category]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Category not found.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>