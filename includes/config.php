<?php
// Database Configuration
define('DB_HOST', 'sql100.infinityfree.com');
define('DB_NAME', 'if0_40118513_library_system');
define('DB_USER', 'if0_40118513');
define('DB_PASS', 'changed1221');

// Application Configuration
define('APP_NAME', 'wereda4no2 digistal lybrary');
define('APP_URL', 'https://wereda4no2library.gt.tc/');
define('ADMIN_USERNAME', 'ethco');
define('ADMIN_PASSWORD', 'ethco123');

// File Upload Configuration
define('MAX_FILE_SIZE', 50 * 1024 * 1024); // 50MB
define('ALLOWED_FILE_TYPES', ['application/pdf']);
define('UPLOAD_PATH_SYSTEM', 'assets/uploads/system-books/');
define('UPLOAD_PATH_USER', 'assets/uploads/user-books/');

// Session Configuration
define('SESSION_NAME', 'library_session');
define('SESSION_LIFETIME', 3600); // 1 hour

// Security Configuration
define('PASSWORD_MIN_LENGTH', 6);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900); // 15 minutes

// Error Reporting (Set to 0 in production)
define('DEBUG_MODE', 1);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
?>