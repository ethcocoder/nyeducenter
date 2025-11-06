<?php
include_once "../Models/LearningPath.php";
include_once "../Models/SchoolDepartment.php";
include_once "../Database.php";

function getAllLearningPaths($offset = 0, $limit = 10) {
    $db = new Database();
    $conn = $db->getConnection();
    $learningPath = new LearningPath($conn);
    return $learningPath->getAll($offset, $limit);
}

function getLearningPathCount() {
    $db = new Database();
    $conn = $db->getConnection();
    $learning_path = new LearningPath($conn);
    return $learning_path->count();
}

function getLearningPathById($id) {
    $db = new Database();
    $conn = $db->getConnection();
    $learningPath = new LearningPath($conn);
    return $learningPath->getById($id);
}

function getAllDepartments() {
    $db = new Database();
    $conn = $db->getConnection();
    $department = new SchoolDepartment($conn);
    return $department->getAll();
}

function createLearningPath($data) {
    $db = new Database();
    $conn = $db->getConnection();
    $learningPath = new LearningPath($conn);
    return $learningPath->insert($data);
}

function updateLearningPath($id, $data) {
    $db = new Database();
    $conn = $db->getConnection();
    $learningPath = new LearningPath($conn);
    return $learningPath->update($id, $data);
}

function deleteLearningPath($id) {
    $db = new Database();
    $conn = $db->getConnection();
    $learningPath = new LearningPath($conn);
    return $learningPath->delete($id);
}

function getLearningPathStudents($path_id, $offset = 0, $limit = 10) {
    $db = new Database();
    $conn = $db->getConnection();
    $learningPath = new LearningPath($conn);
    return $learningPath->getStudents($path_id, $offset, $limit);
}

function getLearningPathStudentCount($path_id) {
    $db = new Database();
    $conn = $db->getConnection();
    $learningPath = new LearningPath($conn);
    return $learningPath->getStudentCount($path_id);
}
?> 