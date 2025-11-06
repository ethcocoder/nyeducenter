<?php
if (!defined('DB_CONFIG_LOADED')) {
    define('DB_CONFIG_LOADED', true);
    
    // Database configuration
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'edupulsedb');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    
    // PDO options
    define('DB_OPTIONS', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ]);
}

// Create database connection
try {
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS,
        DB_OPTIONS
    );
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Function to get database connection
function getDBConnection() {
    global $conn;
    return $conn;
} 