<?php
session_start();
include "../../Utils/Util.php";
include "../../Controller/Admin/LearningPath.php";

if (isset($_POST['title']) && isset($_POST['description']) && isset($_POST['subject']) && 
    isset($_POST['grade_level']) && isset($_POST['semester']) && isset($_POST['credits']) && 
    isset($_POST['department_id'])) {
    
    $data = array(
        'title' => $_POST['title'],
        'description' => $_POST['description'],
        'subject' => $_POST['subject'],
        'grade_level' => $_POST['grade_level'],
        'semester' => $_POST['semester'],
        'credits' => $_POST['credits'],
        'department_id' => $_POST['department_id']
    );
    
    $result = addLearningPath($data);
    
    if ($result) {
        $em = "Learning path added successfully";
        Util::redirect("../Learning-Paths.php", "success", $em);
    } else {
        $em = "Error adding learning path";
        Util::redirect("../Learning-Paths.php", "error", $em);
    }
} else {
    $em = "All fields are required";
    Util::redirect("../Learning-Paths.php", "error", $em);
}
?> 