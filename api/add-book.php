<?php
header('Content-Type: application/json');

require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Access denied.']);
    exit;
}

$title = trim($_POST['bookTitle'] ?? '');
$author = trim($_POST['bookAuthor'] ?? '');
$category_id = filter_var($_POST['bookCategory'] ?? '', FILTER_VALIDATE_INT);
$description = trim($_POST['bookDescription'] ?? '');

$file_path = null;
$cover_image = null; // Assuming no cover image upload for now, will add if needed
$file_size = 0; // Initialize file size

if (empty($title) || empty($category_id)) {
    echo json_encode(['success' => false, 'message' => 'Book title and category are required.']);
    exit;
}

$bookSourceType = $_POST['bookSourceType'] ?? 'upload';

if ($bookSourceType === 'upload') {
    // Handle file upload
    if (isset($_FILES['bookFile']) && $_FILES['bookFile']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['bookFile']['tmp_name'];
        $fileName = $_FILES['bookFile']['name'];
        $fileSize = $_FILES['bookFile']['size'];
        $fileType = $_FILES['bookFile']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $file_size = $_FILES['bookFile']['size'];
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;

        // Set the absolute path for upload directory
        $uploadFileDir = $_SERVER['DOCUMENT_ROOT'] . '/assets/uploads/system-books/';
        $dest_path = $uploadFileDir . $newFileName;

        // Ensure the directory exists, if not, create it
        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0755, true); // Create directory with proper permissions if it doesn't exist
        }

        // Try to move the uploaded file
        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            $file_path = '/assets/uploads/system-books/' . $newFileName;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file.']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error.']);
        exit;
    }
} elseif ($bookSourceType === 'link') {
    $bookLink = trim($_POST['bookLink'] ?? '');
    if (empty($bookLink)) {
        echo json_encode(['success' => false, 'message' => 'Book link is required.']);
        exit;
    }

    // Validate URL format (basic validation)
    if (!filter_var($bookLink, FILTER_VALIDATE_URL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid book link format.']);
        exit;
    }
    $file_path = $bookLink;
    $file_size = 0; // For linked books, file size is not applicable or 0
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid book source type.']);
    exit;
}

try {
    $pdo = get_db_connection();

    // Check if category exists
    $stmt = $pdo->prepare("SELECT id FROM grade_categories WHERE id = ?");
    $stmt->execute([$category_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Invalid category selected.']);
        exit;
    }

    // Insert new book
    $stmt = $pdo->prepare("INSERT INTO system_books (title, grade_category_id, file_path, file_size, cover_image) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$title, $category_id, $file_path, $file_size, $cover_image]);

    echo json_encode(['success' => true, 'message' => 'Book added successfully.']);
} catch (PDOException $e) {
    error_log("Error adding book: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}
?>
