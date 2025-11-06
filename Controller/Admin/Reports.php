<?php
include_once "../Models/Student.php";
include_once "../Models/Course.php";
include_once "../Models/Enrollment.php";
include_once "../Models/SystemLog.php";
include_once "../Database.php";

function getTotalEnrollments() {
    $db = new Database();
    $conn = $db->getConnection();
    $enrollment = new Enrollment($conn);
    return $enrollment->count();
}

function getCompletionRate() {
    $db = new Database();
    $conn = $db->getConnection();
    $enrollment = new Enrollment($conn);
    return $enrollment->getCompletionRate();
}

function getAverageProgress() {
    $db = new Database();
    $conn = $db->getConnection();
    $enrollment = new Enrollment($conn);
    return $enrollment->getAverageProgress();
}

function getActiveUsers() {
    $db = new Database();
    $conn = $db->getConnection();
    $systemLog = new SystemLog($conn);
    return $systemLog->getActiveUsers();
}

function getEnrollmentTrends() {
    $db = new Database();
    $conn = $db->getConnection();
    $enrollment = new Enrollment($conn);
    
    $trends = $enrollment->getEnrollmentTrends();
    return [
        'labels' => array_column($trends, 'date'),
        'data' => array_column($trends, 'count')
    ];
}

function getCoursePopularity() {
    $db = new Database();
    $conn = $db->getConnection();
    $course = new Course($conn);
    
    $popularity = $course->getPopularity();
    return [
        'labels' => array_column($popularity, 'title'),
        'data' => array_column($popularity, 'enrollments')
    ];
}

function getTopCourses() {
    $db = new Database();
    $conn = $db->getConnection();
    $course = new Course($conn);
    return $course->getTopPerforming(5);
}

function getStudentProgress() {
    $db = new Database();
    $conn = $db->getConnection();
    $student = new Student($conn);
    return $student->getProgressReport(5);
}
?> 