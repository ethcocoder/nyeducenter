<?php
require_once '../includes/functions.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if (!is_admin()) {
    $response['message'] = 'Access denied.';
    echo json_encode($response);
    exit;
}

if (isset($_GET['id'])) {
    $userId = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

    if ($userId) {
        $conn = get_db_connection();
        $stmt = $conn->prepare("SELECT id, username, email, role FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $response['success'] = true;
            $response['message'] = 'User data fetched successfully.';
            $response['user'] = $user;
        } else {
            $response['message'] = 'User not found.';
        }
    } else {
        $response['message'] = 'Invalid user ID.';
    }
} else {
    $response['message'] = 'User ID not provided.';
}

echo json_encode($response);
?>