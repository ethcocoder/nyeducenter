<?php
include_once __DIR__ . "/../../Models/LearningPath.php";
include_once __DIR__ . "/../../Models/LearningModule.php";
include_once __DIR__ . "/../../Database.php";

function getStudentLearningPaths($student_id, $offset = 0, $limit = 10) {
    $db = new Database();
    $conn = $db->getConnection();
    $learning_path = new LearningPath($conn);
    return $learning_path->getStudentPaths($student_id, $offset, $limit);
}

function getStudentLearningPathCount($student_id) {
    $db = new Database();
    $conn = $db->getConnection();
    $learning_path = new LearningPath($conn);
    return $learning_path->getStudentPathCount($student_id);
}

function getAvailableLearningPaths($grade = '', $subject = '') {
    $db = new Database();
    $conn = $db->getConnection();
    $learning_path = new LearningPath($conn);
    return $learning_path->getAvailablePaths($grade, $subject);
}

function enrollStudentInPath($student_id, $path_id) {
    $db = new Database();
    $conn = $db->getConnection();
    $learning_path = new LearningPath($conn);
    return $learning_path->enrollStudent($student_id, $path_id);
}

function getLearningPathProgress($student_id, $path_id) {
    $db = new Database();
    $conn = $db->getConnection();
    $learning_path = new LearningPath($conn);
    return $learning_path->getStudentProgress($student_id, $path_id);
}

function getLearningPathModules($path_id, $student_id = null) {
    $db = new Database();
    $conn = $db->getConnection();
    $module = new LearningModule($conn);
    return $module->getByPathId($path_id);
}

function getModuleProgress($student_id, $module_id) {
    $db = new Database();
    $conn = $db->getConnection();
    $module = new LearningModule($conn);
    return $module->getStudentProgress($student_id, $module_id);
}

function updateModuleProgress($student_id, $module_id, $progress_data) {
    $db = new Database();
    $conn = $db->getConnection();
    $module = new LearningModule($conn);
    return $module->updateStudentProgress($student_id, $module_id, $progress_data);
}

function getModuleResources($module_id) {
    $db = new Database();
    $conn = $db->getConnection();
    $module = new LearningModule($conn);
    return $module->getResources($module_id);
}
?> 