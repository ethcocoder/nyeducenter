<?php
include_once __DIR__ . "/../../Models/Assignment.php";
include_once __DIR__ . "/../../Database.php";

function getStudentAssignments($student_id, $offset = 0, $limit = 10) {
    $db = new Database();
    $conn = $db->getConnection();
    $assignment = new Assignment($conn);
    return $assignment->getStudentAssignments($student_id, $offset, $limit);
}

function getStudentAssignmentCount($student_id) {
    $db = new Database();
    $conn = $db->getConnection();
    $assignment = new Assignment($conn);
    return $assignment->getStudentAssignmentCount($student_id);
}

function getAssignmentDetails($assignment_id) {
    $db = new Database();
    $conn = $db->getConnection();
    $assignment = new Assignment($conn);
    return $assignment->getById($assignment_id);
}

function submitAssignment($student_id, $assignment_id, $submission_data) {
    $db = new Database();
    $conn = $db->getConnection();
    $assignment = new Assignment($conn);
    return $assignment->submitAssignment($student_id, $assignment_id, $submission_data);
}

function getAssignmentSubmission($student_id, $assignment_id) {
    $db = new Database();
    $conn = $db->getConnection();
    $assignment = new Assignment($conn);
    return $assignment->getStudentSubmission($student_id, $assignment_id);
}
?> 