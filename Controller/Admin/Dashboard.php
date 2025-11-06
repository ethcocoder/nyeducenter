<?php
include_once "../Models/Instructor.php";
include_once "../Models/Student.php";
include_once "../Models/LearningPath.php";
include_once "../Models/SystemLog.php";
include_once "../Database.php";

function getTotalTeachers() {
    $db = new Database();
    $conn = $db->getConnection();
    $instructor = new Instructor($conn);
    return $instructor->count();
}

function getTotalStudents() {
    $db = new Database();
    $conn = $db->getConnection();
    $student = new Student($conn);
    return $student->count();
}

function getTotalLearningPaths() {
    $db = new Database();
    $conn = $db->getConnection();
    $learningPath = new LearningPath($conn);
    return $learningPath->count();
}

function getRecentActivities() {
    $db = new Database();
    $conn = $db->getConnection();
    $systemLog = new SystemLog($conn);
    return $systemLog->getRecent(10);
}

function getSystemStatistics() {
    $db = new Database();
    $conn = $db->getConnection();
    
    $instructor = new Instructor($conn);
    $student = new Student($conn);
    $learningPath = new LearningPath($conn);
    
    return [
        'active_instructors' => $instructor->countByStatus('Active'),
        'active_students' => $student->countByStatus('Active'),
        'active_paths' => $learningPath->countByStatus('Active'),
        'total_enrollments' => $learningPath->getTotalEnrollments()
    ];
}
?> 