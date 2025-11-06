<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Access denied.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $conn = get_db_connection();
    $db_name = DB_NAME;
    $db_user = DB_USER;
    $db_password = DB_PASS;
    $db_host = DB_HOST;

    $backup_dir = __DIR__ . '/../includes/backups/';
    if (!is_dir($backup_dir)) {
        mkdir($backup_dir, 0777, true);
    }

    $filename = 'db-backup-' . date('Y-m-d_H-i-s') . '.sql';
    $filepath = $backup_dir . $filename;

    // Path to mysqldump.exe on Windows (assuming XAMPP default location)
    $mysqldump_path = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';

    // Command to backup database
    $command = "\"" . $mysqldump_path . "\" -h" . $db_host . " -u" . $db_user . " -p" . $db_password . " " . $db_name . " > " . $filepath;

    // Execute the command
    exec($command, $output, $return_var);

    if ($return_var === 0) {
        echo json_encode(['success' => true, 'message' => 'Database backup created successfully.', 'filename' => $filename]);
    } else {
        error_log("Database backup failed. Command: " . $command . " Output: " . implode("\n", $output) . " Return Var: " . $return_var);
        echo json_encode(['success' => false, 'message' => 'Failed to create database backup.']);
    }


} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>