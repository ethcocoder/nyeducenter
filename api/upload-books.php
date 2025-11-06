<?php
require_once '../includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$folder_id = $_POST['folder_id'] ?? 0;

if (!$folder_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing folder ID']);
    exit;
}

// Verify folder ownership
try {
    $db = Database::getInstance();
    
    $stmt = $db->prepare("SELECT id FROM user_folders WHERE id = ? AND user_id = ?");
    $stmt->execute([$folder_id, $user_id]);
    
    if ($stmt->rowCount() === 0) {
        echo json_encode(['error' => 'Folder not found or access denied']);
        exit;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error', 'message' => $e->getMessage()]);
    exit;
}

// Handle file uploads
$uploaded_files = [];
$errors = [];

if (isset($_FILES['files'])) {
    $files = $_FILES['files'];
    
    for ($i = 0; $i < count($files['name']); $i++) {
        $file_name = $files['name'][$i];
        $file_tmp = $files['tmp_name'][$i];
        $file_size = $files['size'][$i];
        $file_error = $files['error'][$i];
        
        // Check for upload errors
        if ($file_error !== UPLOAD_ERR_OK) {
            $errors[] = "Error uploading $file_name";
            continue;
        }
        
        // Validate file type
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if ($file_ext !== 'pdf') {
            $errors[] = "File $file_name is not a PDF";
            continue;
        }
        
        // Validate file size (50MB limit)
        if ($file_size > 50 * 1024 * 1024) {
            $errors[] = "File $file_name is too large (max 50MB)";
            continue;
        }
        
        // Generate unique filename
        $new_file_name = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9-_]/', '_', $file_name);
        $upload_path = "assets/uploads/user-books/" . $new_file_name;
        $full_upload_path = "../" . $upload_path;
        
        // Create upload directory if it doesn't exist
        $upload_dir = dirname($full_upload_path);
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Move uploaded file
        if (move_uploaded_file($file_tmp, $full_upload_path)) {
            try {
                // Insert into database
                $stmt = $db->prepare("INSERT INTO user_books (folder_id, user_id, title, file_path) VALUES (?, ?, ?, ?)");
                $result = $stmt->execute([$folder_id, $user_id, sanitize_filename($file_name), $upload_path]);
                
                if ($result) {
                    $uploaded_files[] = $file_name;
                } else {
                    // Delete file if database insert failed
                    unlink($full_upload_path);
                    $errors[] = "Failed to save $file_name to database";
                }
            } catch (Exception $e) {
                // Delete file if database error
                unlink($full_upload_path);
                $errors[] = "Database error for $file_name: " . $e->getMessage();
            }
        } else {
            $errors[] = "Failed to move uploaded file $file_name";
        }
    }
}

// Return response
if (count($uploaded_files) > 0) {
    echo json_encode([
        'success' => true,
        'message' => 'Files uploaded successfully',
        'uploaded' => $uploaded_files,
        'errors' => $errors
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No files were uploaded successfully',
        'errors' => $errors
    ]);
}