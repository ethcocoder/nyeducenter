<?php
session_start();
include "../../Utils/Util.php";
include "../../Controller/Admin/LearningPath.php";

if (isset($_POST['path_id']) && isset($_POST['title']) && isset($_POST['description']) && 
    isset($_POST['subject']) && isset($_POST['grade_level']) && isset($_POST['semester']) && 
    isset($_POST['credits']) && isset($_POST['department_id']) && isset($_POST['status'])) {
    
    $data = array(
        'title' => $_POST['title'],
        'description' => $_POST['description'],
        'subject' => $_POST['subject'],
        'grade_level' => $_POST['grade_level'],
        'semester' => $_POST['semester'],
        'credits' => $_POST['credits'],
        'department_id' => $_POST['department_id'],
        'status' => $_POST['status']
    );
    
    $result = updateLearningPath($_POST['path_id'], $data);
    
    if ($result) {
        $em = "Learning path updated successfully";
        Util::redirect("../Learning-Paths.php", "success", $em);
    } else {
        $em = "Error updating learning path";
        Util::redirect("../Learning-Path-Edit.php?id=" . $_POST['path_id'], "error", $em);
    }
} else {
    $em = "All fields are required";
    Util::redirect("../Learning-Paths.php", "error", $em);
}
?> 