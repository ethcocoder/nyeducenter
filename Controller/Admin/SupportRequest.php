<?php
include_once "../Models/SupportRequest.php";
include_once "../Models/Student.php";
include_once "../Models/Instructor.php";
include_once "../Database.php";

function getAllSupportRequests($offset = 0, $limit = 10) {
    $db = new Database();
    $conn = $db->getConnection();
    $supportRequest = new SupportRequest($conn);
    return $supportRequest->getAll($offset, $limit);
}

function getSupportRequestCount() {
    $db = new Database();
    $conn = $db->getConnection();
    $supportRequest = new SupportRequest($conn);
    return $supportRequest->count();
}

function getSupportRequestById($id) {
    $db = new Database();
    $conn = $db->getConnection();
    $supportRequest = new SupportRequest($conn);
    return $supportRequest->getById($id);
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

function updateSupportRequestStatus($id, $status) {
    $db = new Database();
    $conn = $db->getConnection();
    $supportRequest = new SupportRequest($conn);
    return $supportRequest->updateStatus($id, $status);
}

function addSupportRequestResponse($request_id, $admin_id, $message) {
    $db = new Database();
    $conn = $db->getConnection();
    $supportRequest = new SupportRequest($conn);
    return $supportRequest->addResponse($request_id, $admin_id, $message);
}

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'view':
            if (isset($_GET['id'])) {
                $request = getSupportRequestById($_GET['id']);
                echo json_encode($request);
            }
            break;
            
        case 'update_status':
            $data = json_decode(file_get_contents('php://input'), true);
            if (isset($data['request_id']) && isset($data['status'])) {
                $result = updateSupportRequestStatus($data['request_id'], $data['status']);
                echo json_encode(['success' => $result]);
            }
            break;
            
        case 'add_response':
            $data = json_decode(file_get_contents('php://input'), true);
            if (isset($data['request_id']) && isset($data['message'])) {
                $result = addSupportRequestResponse(
                    $data['request_id'],
                    $_SESSION['admin_id'],
                    $data['message']
                );
                echo json_encode(['success' => $result]);
            }
            break;
    }
    exit;
}
?> 