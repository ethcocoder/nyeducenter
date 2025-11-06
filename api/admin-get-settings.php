<?php
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Access denied.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $conn = get_db_connection();

    // Fetch settings from the database
    $settings = [];
    $stmt = $conn->query("SELECT setting_name, setting_value FROM settings");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['setting_name']] = $row['setting_value'];
    }

    // Provide default values if settings are not found
    $responseSettings = [
        'site_name' => $settings['site_name'] ?? 'Library System',
        'site_description' => $settings['site_description'] ?? 'A simple library management system.',
        'admin_email' => $settings['admin_email'] ?? 'admin@example.com',
        'items_per_page' => $settings['items_per_page'] ?? 10,
    ];

    echo json_encode(['success' => true, 'settings' => $responseSettings]);

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>