<?php
session_start();
include "../../Utils/Util.php";
include "../../Controller/Student/LearningPath.php";

header('Content-Type: application/json');

if (isset($_SESSION['student_id'])) {
    $grade = isset($_GET['grade']) ? $_GET['grade'] : '';
    $subject = isset($_GET['subject']) ? $_GET['subject'] : '';
    
    $paths = getAvailableLearningPaths($grade, $subject);
    
    echo json_encode($paths);
} else {
    echo json_encode(['error' => 'Unauthorized']);
}
?> 