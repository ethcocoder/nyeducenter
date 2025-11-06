<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/functions.php';

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

    // Get total number of activities
    $totalStmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM (
            SELECT ra.id
            FROM recent_activity ra
            JOIN users u ON ra.user_id = u.id
            JOIN system_books sb ON ra.book_id = sb.id AND ra.book_type = 'system'
            
            UNION ALL
            
            SELECT ra.id
            FROM recent_activity ra
            JOIN users u ON ra.user_id = u.id
            JOIN user_books ub ON ra.book_id = ub.id AND ra.book_type = 'user'
        ) as total_activities
    ");
    $totalStmt->execute();
    $totalActivities = $totalStmt->fetchColumn();
    
    $stmt = $pdo->prepare("
        SELECT ra.*, u.username, sb.title as system_book_title, sb.file_path as system_book_path, 'system' as book_type
        FROM recent_activity ra
        JOIN users u ON ra.user_id = u.id
        JOIN system_books sb ON ra.book_id = sb.id AND ra.book_type = 'system'
        
        UNION ALL
        
        SELECT ra.*, u.username, ub.title as user_book_title, ub.file_path as user_book_path, 'user' as book_type
        FROM recent_activity ra
        JOIN users u ON ra.user_id = u.id
        JOIN user_books ub ON ra.book_id = ub.id AND ra.book_type = 'user'
        
        ORDER BY opened_at DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'activities' => $activities,
        'total' => $totalActivities,
        'page' => $page,
        'limit' => $limit
    ]);
    
} catch (Exception $e) {
    error_log("Error fetching activity: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch activity']);
}