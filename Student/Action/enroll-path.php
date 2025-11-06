<?php
session_start();
include "../../Utils/Util.php";
include "../../Controller/Student/LearningPath.php";

header('Content-Type: application/json');

if (isset($_SESSION['student_id']) && isset($_POST['path_id'])) {
    $student_id = $_SESSION['student_id'];
    $path_id = $_POST['path_id'];
    
    $result = enrollStudentInPath($student_id, $path_id);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Successfully enrolled in learning path']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error enrolling in learning path']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?> 