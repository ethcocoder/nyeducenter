<?php
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Access denied.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $siteName = $input['siteName'] ?? null;
    $siteDescription = $input['siteDescription'] ?? null;
    $adminEmail = $input['adminEmail'] ?? null;
    $itemsPerPage = $input['itemsPerPage'] ?? null;

    if (!$siteName || !$adminEmail || !$itemsPerPage) {
        echo json_encode(['success' => false, 'message' => 'Site Name, Admin Email, and Items Per Page are required.']);
        exit;
    }

    $conn = get_db_connection();

    // Update or insert settings
    $settingsToUpdate = [
        'site_name' => $siteName,
        'site_description' => $siteDescription,
        'admin_email' => $adminEmail,
        'items_per_page' => $itemsPerPage,
    ];

    foreach ($settingsToUpdate as $name => $value) {
        $stmt = $conn->prepare("INSERT INTO settings (setting_name, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$name, $value, $value]);
    }

    echo json_encode(['success' => true, 'message' => 'System settings updated successfully.']);

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>