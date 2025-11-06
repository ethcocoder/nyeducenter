<?php
// Application Configuration
define('APP_NAME', 'Innovation Trading Center');
define('APP_URL', 'http://localhost/innovation-trading-center');
define('APP_VERSION', '1.0.0');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'innovation_trading_center');
define('DB_USER', 'root');
define('DB_PASS', '');

// File Upload Configuration
define('UPLOAD_PATH', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);

// Session Configuration
define('SESSION_LIFETIME', 3600); // 1 hour

// Email Configuration (for future use)
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 587);
define('SMTP_USER', '');
define('SMTP_PASS', '');

// Pagination
define('ITEMS_PER_PAGE', 12);

// Supported Languages
define('SUPPORTED_LANGUAGES', ['en', 'am']); // English and Amharic
define('DEFAULT_LANGUAGE', 'en');

// Innovation Categories
define('INNOVATION_CATEGORIES', [
    'agriculture' => 'Agriculture Technology',
    'health' => 'Health Technology',
    'education' => 'Education Technology',
    'energy' => 'Energy & Environment',
    'finance' => 'Financial Technology',
    'transport' => 'Transportation',
    'manufacturing' => 'Manufacturing',
    'tourism' => 'Tourism & Hospitality',
    'other' => 'Other'
]);

// User Roles
define('ROLE_INNOVATOR', 'innovator');
define('ROLE_SPONSOR', 'sponsor');
define('ROLE_ADMIN', 'admin');

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Africa/Addis_Ababa');
?> 