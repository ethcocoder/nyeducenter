<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $conn = get_db_connection();
    // Get reading progress data
    $stmt = $conn->prepare("
        SELECT 
            rp.book_id,
            rp.book_type,
            rp.current_page,
            rp.total_pages,
            rp.last_read,
            CASE 
                WHEN rp.book_type = 'system' THEN sb.title
                WHEN rp.book_type = 'user' THEN ub.title
                ELSE 'Unknown Book'
            END as book_title,
            CASE 
                WHEN rp.book_type = 'system' THEN gc.grade_name
                ELSE 'User Upload'
            END as category_name
        FROM reading_progress rp
        LEFT JOIN system_books sb ON rp.book_id = sb.id AND rp.book_type = 'system'
        LEFT JOIN user_books ub ON rp.book_id = ub.id AND rp.book_type = 'user'
        LEFT JOIN grade_categories gc ON sb.grade_category_id = gc.id
        WHERE rp.user_id = ?
        ORDER BY rp.last_read DESC
    ");
    $stmt->execute([$user_id]);
    $reading_progress = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get bookmarks data
    $stmt = $conn->prepare("
        SELECT 
            b.book_id,
            b.book_type,
            b.page_number,
            b.note,
            b.created_at,
            CASE 
                WHEN b.book_type = 'system' THEN sb.title
                WHEN b.book_type = 'user' THEN ub.title
                ELSE 'Unknown Book'
            END as book_title
        FROM bookmarks b
        LEFT JOIN system_books sb ON b.book_id = sb.id AND b.book_type = 'system'
        LEFT JOIN user_books ub ON b.book_id = ub.id AND b.book_type = 'user'
        WHERE b.user_id = ?
        ORDER BY b.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $bookmarks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get recent activity data
    $stmt = $conn->prepare("
        SELECT 
            ra.book_id,
            ra.book_type,
            ra.last_opened,
            CASE 
                WHEN ra.book_type = 'system' THEN sb.title
                WHEN ra.book_type = 'user' THEN ub.title
                ELSE 'Unknown Book'
            END as book_title
        FROM recent_activity ra
        LEFT JOIN system_books sb ON ra.book_id = sb.id AND ra.book_type = 'system'
        LEFT JOIN user_books ub ON ra.book_id = ub.id AND ra.book_type = 'user'
        WHERE ra.user_id = ?
        ORDER BY ra.last_opened DESC
        LIMIT 50
    ");
    $stmt->execute([$user_id]);
    $recent_activity = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Compile the data
    $reading_data = [
        'export_date' => date('Y-m-d H:i:s'),
        'user_id' => $user_id,
        'username' => $_SESSION['username'],
        'reading_progress' => $reading_progress,
        'bookmarks' => $bookmarks,
        'recent_activity' => $recent_activity,
        'summary' => [
            'total_books_read' => count($reading_progress),
            'total_pages_read' => array_sum(array_column($reading_progress, 'current_page')),
            'total_bookmarks' => count($bookmarks),
            'total_activities' => count($recent_activity)
        ]
    ];

    echo json_encode([
        'success' => true,
        'reading_data' => $reading_data
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>