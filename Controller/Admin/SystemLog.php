<?php
include_once "../Models/SystemLog.php";
include_once "../Models/Student.php";
include_once "../Models/Instructor.php";
include_once "../Models/Admin.php";
include_once "../Database.php";

function getRecentLogs($offset = 0, $limit = 50) {
    $db = new Database();
    $conn = $db->getConnection();
    $systemLog = new SystemLog($conn);
    return $systemLog->getRecent($limit);
}

function getStudentById($id) {
    $db = new Database();
    $conn = $db->getConnection();
    $student = new Student($conn);
    return $student->getById($id);
}

function getInstructorById($id) {
    $db = new Database();
    $conn = $db->getConnection();
    $instructor = new Instructor($conn);
    return $instructor->getById($id);
}

function getAdminById($id) {
    $db = new Database();
    $conn = $db->getConnection();
    $admin = new Admin($conn);
    return $admin->getById($id);
}

// Handle AJAX requests
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'export':
            $db = new Database();
            $conn = $db->getConnection();
            $systemLog = new SystemLog($conn);
            
            // Get filter parameters
            $action = $_GET['action'] ?? '';
            $user_type = $_GET['user_type'] ?? '';
            $start_date = $_GET['start_date'] ?? '';
            $end_date = $_GET['end_date'] ?? '';
            
            // Get filtered logs
            $logs = $systemLog->getFiltered($action, $user_type, $start_date, $end_date);
            
            // Set headers for CSV download
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="system_logs_' . date('Y-m-d') . '.csv"');
            
            // Create CSV file
            $output = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($output, ['Time', 'Action', 'Description', 'User', 'User Type']);
            
            // Add data
            foreach ($logs as $log) {
                $user = '';
                if ($log['user_id']) {
                    if ($log['user_type'] == 'student') {
                        $student = getStudentById($log['user_id']);
                        $user = $student['first_name'] . ' ' . $student['last_name'];
                    } else if ($log['user_type'] == 'instructor') {
                        $instructor = getInstructorById($log['user_id']);
                        $user = $instructor['first_name'] . ' ' . $instructor['last_name'];
                    } else if ($log['user_type'] == 'admin') {
                        $admin = getAdminById($log['user_id']);
                        $user = $admin['username'];
                    }
                } else {
                    $user = 'System';
                }
                
                fputcsv($output, [
                    date('Y-m-d H:i:s', strtotime($log['created_at'])),
                    $log['action'],
                    $log['description'],
                    $user,
                    ucfirst($log['user_type'] ?? 'System')
                ]);
            }
            
            fclose($output);
            exit;
            break;
    }
}
?> 