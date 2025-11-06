<?php
include "../../Models/LearningModule.php";
include "../../Database.php";

function getModulesByPathId($path_id, $offset = 0, $limit = 10) {
    $db = new Database();
    $conn = $db->getConnection();
    $module = new LearningModule($conn);
    return $module->getByPathId($path_id, $offset, $limit);
}

function getModuleById($id) {
    $db = new Database();
    $conn = $db->getConnection();
    $module = new LearningModule($conn);
    return $module->getById($id);
}

function addModule($data) {
    $db = new Database();
    $conn = $db->getConnection();
    $module = new LearningModule($conn);
    return $module->insert($data);
}

function updateModule($id, $data) {
    $db = new Database();
    $conn = $db->getConnection();
    $module = new LearningModule($conn);
    return $module->update($id, $data);
}

function deleteModule($id) {
    $db = new Database();
    $conn = $db->getConnection();
    $module = new LearningModule($conn);
    return $module->delete($id);
}

function updateModuleOrder($id, $new_order) {
    $db = new Database();
    $conn = $db->getConnection();
    $module = new LearningModule($conn);
    return $module->updateOrder($id, $new_order);
}

function getModuleResources($module_id) {
    $db = new Database();
    $conn = $db->getConnection();
    $module = new LearningModule($conn);
    return $module->getResources($module_id);
}

function getStudentModuleProgress($student_id, $module_id) {
    $db = new Database();
    $conn = $db->getConnection();
    $module = new LearningModule($conn);
    return $module->getStudentProgress($student_id, $module_id);
}

function updateStudentModuleProgress($student_id, $module_id, $progress_data) {
    $db = new Database();
    $conn = $db->getConnection();
    $module = new LearningModule($conn);
    return $module->updateStudentProgress($student_id, $module_id, $progress_data);
}
?> 