<?php
include_once __DIR__ . "/../../Models/Assessment.php";
include_once __DIR__ . "/../../Database.php";

function getStudentAssessments($student_id, $offset = 0, $limit = 10) {
    $db = new Database();
    $conn = $db->getConnection();
    $assessment = new Assessment($conn);
    return $assessment->getStudentAssessments($student_id, $offset, $limit);
}

function getStudentAssessmentCount($student_id) {
    $db = new Database();
    $conn = $db->getConnection();
    $assessment = new Assessment($conn);
    return $assessment->getStudentAssessmentCount($student_id);
}

function getAssessmentDetails($assessment_id) {
    $db = new Database();
    $conn = $db->getConnection();
    $assessment = new Assessment($conn);
    return $assessment->getById($assessment_id);
}

function getAssessmentQuestions($assessment_id) {
    $db = new Database();
    $conn = $db->getConnection();
    $assessment = new Assessment($conn);
    return $assessment->getQuestions($assessment_id);
}

function submitAssessment($student_id, $assessment_id, $answers) {
    $db = new Database();
    $conn = $db->getConnection();
    $assessment = new Assessment($conn);
    return $assessment->submitStudentAnswers($student_id, $assessment_id, $answers);
}

function getAssessmentResult($student_id, $assessment_id) {
    $db = new Database();
    $conn = $db->getConnection();
    $assessment = new Assessment($conn);
    return $assessment->getStudentResult($student_id, $assessment_id);
}
?> 