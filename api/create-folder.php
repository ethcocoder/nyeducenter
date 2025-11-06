<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/db.php';

// Check if user is logged in
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Please login to create folders']);
    exit;
}

// Get POST data
$folder_name = isset($_POST['folder_name']) ? sanitize_input($_POST['folder_name']) : '';

// Validate input
if (empty($folder_name)) {
    echo json_encode(['success' => false, 'message' => 'Folder name is required']);
    exit;
}

if (strlen($folder_name) > 50) {
    echo json_encode(['success' => false, 'message' => 'Folder name must be less than 50 characters']);
    exit;
}

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Check if folder name already exists for this user
try {
    $db = Database::getInstance();
    $stmt = $db->prepare("SELECT id FROM user_folders WHERE user_id = ? AND folder_name = ?");
    $stmt->execute([$user_id, $folder_name]);
    
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Folder name already exists']);
        exit;
    }
    
    // Begin transaction for database and filesystem operations
    $db->beginTransaction();
    
    // Create folder in database
    $stmt = $db->prepare("INSERT INTO user_folders (user_id, folder_name) VALUES (?, ?)");
    $stmt->execute([$user_id, $folder_name]);
    $folder_id = $db->lastInsertId();
    
    // Create physical directory in system directory
    $sanitized_folder_name = sanitize_filename($folder_name);
    $system_folder_path = '../assets/uploads/system-books/' . $sanitized_folder_name . '_' . $folder_id;
    
    if (!file_exists($system_folder_path)) {
        if (!mkdir($system_folder_path, 0755, true)) {
            // Rollback database transaction if directory creation fails
            $db->rollBack();
            echo json_encode(['success' => false, 'message' => 'Failed to create system directory']);
            exit;
        }
    }
    
    // Commit transaction if both operations succeed
    $db->commit();
    
    echo json_encode(['success' => true, 'message' => 'Folder created successfully in system directory', 'folder_id' => $folder_id]);
    
} catch (Exception $e) {
    // Rollback transaction on any error
    if (isset($db)) {
        $db->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}