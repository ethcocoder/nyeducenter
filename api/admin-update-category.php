<?php
require_once '../includes/functions.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

// Check if the user is an admin
if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Access denied.']);
    exit;
}

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);

$categoryId = $data['categoryId'] ?? null;
$categoryName = $data['categoryName'] ?? null;
$categoryDescription = $data['categoryDescription'] ?? null;

// Validate input
if (empty($categoryId) || empty($categoryName)) {
    echo json_encode(['success' => false, 'message' => 'Category ID and Name are required.']);
    exit;
}

try {
    $pdo = get_db_connection();

    // Check if category name already exists for another category
    $stmt = $pdo->prepare("SELECT id FROM grade_categories WHERE grade_name = :categoryName AND id != :categoryId");
    $stmt->execute(['categoryName' => $categoryName, 'categoryId' => $categoryId]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Category name already exists.']);
        exit;
    }

    // Update category data
    $stmt = $pdo->prepare("UPDATE grade_categories SET grade_name = :categoryName, description = :categoryDescription WHERE id = :categoryId");
    $stmt->execute([
        'categoryName' => $categoryName,
        'categoryDescription' => $categoryDescription,
        'categoryId' => $categoryId
    ]);

    if ($stmt->rowCount()) {
        echo json_encode(['success' => true, 'message' => 'Category updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made or category not found.']);
    }

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred.']);
}
?>