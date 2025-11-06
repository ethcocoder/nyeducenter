<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Prevent multiple inclusions
if (!defined('FUNCTIONS_LOADED')) {
    define('FUNCTIONS_LOADED', true);
    
    require_once 'db.php';

// Security Functions
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validate_password($password) {
    return strlen($password) >= PASSWORD_MIN_LENGTH;
}

// Authentication Functions
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function require_login() {
    if (!is_logged_in()) {
        header("Location: " . APP_URL . "pages/login.php");
        exit();
    }
}

function require_admin($is_api_request = false) {
    if (!is_admin()) {
        if ($is_api_request) {
            return false;
        } else {
            header("Location: " . APP_URL . "pages/dashboard.php");
            exit();
        }
    }
    return true;
}

function get_db_connection() {
    return Database::getInstance()->getConnection();
}

// User Functions
function create_user($username, $email, $password) {
    $conn = get_db_connection();
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    
    try {
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $hashed_password]);
        return $conn->lastInsertId();
    } catch (PDOException $e) {
        return false;
    }
}

function get_user_by_username($username) {
    $conn = get_db_connection();
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    return $stmt->fetch();
}

function get_user_by_email($email) {
    $conn = get_db_connection();
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch();
}

function verify_password($password, $hashed_password) {
    return password_verify($password, $hashed_password);
}

// Book Functions
function get_grade_categories() {
    $conn = get_db_connection();
    $stmt = $conn->prepare("SELECT * FROM grade_categories ORDER BY grade_name");
    $stmt->execute();
    return $stmt->fetchAll();
}

function get_system_books_by_grade($grade_id) {
    $conn = get_db_connection();
    $stmt = $conn->prepare("SELECT * FROM system_books WHERE grade_category_id = ? ORDER BY title");
    $stmt->execute([$grade_id]);
    return $stmt->fetchAll();
}

function get_user_folders($user_id) {
    $conn = get_db_connection();
    $stmt = $conn->prepare("SELECT * FROM user_folders WHERE user_id = ? ORDER BY folder_name");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function create_user_folder($user_id, $folder_name) {
    $conn = get_db_connection();
    try {
        $stmt = $conn->prepare("INSERT INTO user_folders (user_id, folder_name) VALUES (?, ?)");
        $stmt->execute([$user_id, $folder_name]);
        return $conn->lastInsertId();
    } catch (PDOException $e) {
        return false;
    }
}

function delete_user_folder($folder_id, $user_id) {
    $conn = get_db_connection();
    try {
        $stmt = $conn->prepare("DELETE FROM user_folders WHERE id = ? AND user_id = ?");
        return $stmt->execute([$folder_id, $user_id]);
    } catch (PDOException $e) {
        return false;
    }
}

function get_user_books_by_folder($folder_id, $user_id) {
    $conn = get_db_connection();
    $stmt = $conn->prepare("SELECT id, folder_id, user_id, title as book_name, file_path as book_path, created_at FROM user_books WHERE user_id = ? AND folder_id = ? ORDER BY title");
    $stmt->execute([$user_id, $folder_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Recent Activity Functions
function add_recent_activity($user_id, $book_id, $book_type) {
    $conn = get_db_connection();
    try {
        $stmt = $conn->prepare("INSERT INTO recent_activity (user_id, book_id, book_type) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $book_id, $book_type]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

function get_recent_activity($user_id, $limit = 10) {
    $conn = get_db_connection();
    $stmt = $conn->prepare("
        SELECT ra.*, sb.title as system_book_title, sb.file_path as system_book_path, 
               ub.title as user_book_title, ub.file_path as user_book_path
        FROM recent_activity ra
        LEFT JOIN system_books sb ON ra.book_id = sb.id AND ra.book_type = 'system'
        LEFT JOIN user_books ub ON ra.book_id = ub.id AND ra.book_type = 'user'
        WHERE ra.user_id = ?
        ORDER BY ra.opened_at DESC
        LIMIT ?
    ");
    $stmt->execute([$user_id, $limit]);
    return $stmt->fetchAll();
}

// File Upload Functions
function upload_file($file, $upload_path) {
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return false;
    }
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return false;
    }
    
    // Check file type
    if (!in_array($file['type'], ALLOWED_FILE_TYPES)) {
        return false;
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $destination = $upload_path . $filename;
    
    // Create directory if it doesn't exist
    if (!file_exists($upload_path)) {
        mkdir($upload_path, 0755, true);
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $filename;
    }
    
    return false;
}

// Admin Functions
function get_admin_stats() {
    try {
        $conn = get_db_connection();
        
        // Get total users
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users");
        $stmt->execute();
        $total_users = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Get total system books
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM system_books");
        $stmt->execute();
        $total_books = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Get total categories
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM grade_categories");
        $stmt->execute();
        $total_categories = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Get total recent activity
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM recent_activity");
        $stmt->execute();
        $total_activity = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        return [
            'total_users' => $total_users,
            'total_books' => $total_books,
            'total_categories' => $total_categories,
            'total_activity' => $total_activity
        ];
        
    } catch (Exception $e) {
        return [
            'total_users' => 0,
            'total_books' => 0,
            'total_categories' => 0,
            'total_activity' => 0
        ];
    }
}

// User Profile Functions
function get_user_by_id($user_id) {
    $conn = get_db_connection();
    $stmt = $conn->prepare("SELECT id, username, email, role, created_at FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

function update_user_profile($user_id, $username, $email) {
    $conn = get_db_connection();
    try {
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        return $stmt->execute([$username, $email, $user_id]);
    } catch (PDOException $e) {
        return false;
    }
}

function update_user_password($user_id, $new_password) {
    $conn = get_db_connection();
    try {
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([$hashed_password, $user_id]);
    } catch (PDOException $e) {
        return false;
    }
}

function get_user_statistics($user_id) {
    $conn = get_db_connection();
    
    try {
        // Get user's books count
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM user_books WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $total_books = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Get user's folders count
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM user_folders WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $total_folders = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Get user's activity count
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM recent_activity WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $total_activity = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        return [
            'total_books' => $total_books,
            'total_folders' => $total_folders,
            'total_activity' => $total_activity
        ];
        
    } catch (PDOException $e) {
        return [
            'total_books' => 0,
            'total_folders' => 0,
            'total_activity' => 0
        ];
    }
}

// Utility Functions
function redirect($url) {
    header("Location: $url");
    exit;
}

function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function format_date($date) {
    return date('M j, Y g:i A', strtotime($date));
}

function sanitize_filename($filename) {
    // Remove anything that isn't a letter, number, underscore, or dash
    $filename = preg_replace('/[^a-zA-Z0-9_.-]/', '', $filename);
    // Replace spaces with underscores
    $filename = str_replace(' ', '_', $filename);
    return $filename;
}

function get_book_reading_progress($user_id, $book_id, $book_type) {
    $conn = get_db_connection();
    try {
        $stmt = $conn->prepare("SELECT current_page, total_pages FROM reading_progress WHERE user_id = ? AND book_id = ? AND book_type = ?");
        $stmt->execute([$user_id, $book_id, $book_type]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting reading progress: " . $e->getMessage());
        return false;
    }
}

function get_user_reading_stats($user_id) {
    $conn = get_db_connection();
    try {
        // Total books read (assuming a book is 'read' if current_page >= total_pages)
        $stmt = $conn->prepare("SELECT COUNT(DISTINCT book_id) FROM reading_progress WHERE user_id = ? AND current_page >= total_pages");
        $stmt->execute([$user_id]);
        $books_read = $stmt->fetchColumn();

        // Total pages read (sum of current_page for all books)
        $stmt = $conn->prepare("SELECT SUM(current_page) FROM reading_progress WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $pages_read = $stmt->fetchColumn();

        // Average pages per book (simple average of total_pages for all books in progress)
        $stmt = $conn->prepare("SELECT AVG(total_pages) FROM reading_progress WHERE user_id = ? AND total_pages > 0");
        $stmt->execute([$user_id]);
        $avg_pages_per_book = round($stmt->fetchColumn() ?? 0);

        // Total bookmarks
        $stmt = $conn->prepare("SELECT COUNT(*) FROM reading_progress rp JOIN user_books ub ON rp.book_id = ub.id WHERE rp.user_id = ? AND rp.bookmarks IS NOT NULL AND rp.bookmarks != ''");
        $stmt->execute([$user_id]);
        $total_bookmarks = $stmt->fetchColumn();

        return [
            'books_read' => $books_read,
            'pages_read' => $pages_read,
            'avg_pages_per_book' => $avg_pages_per_book,
            'total_bookmarks' => $total_bookmarks
        ];
    } catch (PDOException $e) {
        error_log("Error getting user reading stats: " . $e->getMessage());
        return [
            'books_read' => 0,
            'pages_read' => 0,
            'avg_pages_per_book' => 0
        ];
    }
}

function get_system_book_by_id($book_id) {
    $conn = get_db_connection();
    $stmt = $conn->prepare("SELECT * FROM system_books WHERE id = ?");
    $stmt->execute([$book_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_user_book_by_id($book_id) {
    $conn = get_db_connection();
    $stmt = $conn->prepare("SELECT * FROM user_books WHERE id = ?");
    $stmt->execute([$book_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_user_folder_by_id($folder_id, $user_id) {
    $conn = get_db_connection();
    $stmt = $conn->prepare("SELECT * FROM user_folders WHERE id = ? AND user_id = ?");
    $stmt->execute([$folder_id, $user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Initialize session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

} // End of FUNCTIONS_LOADED guard
?>