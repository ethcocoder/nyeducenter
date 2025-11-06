<?php
session_start();
include "../../Utils/Util.php";
include "../../Controller/Admin/LearningPath.php";

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $result = deleteLearningPath($id);
    
    if ($result) {
        $em = "Learning path deleted successfully";
        Util::redirect("../Learning-Paths.php", "success", $em);
    } else {
        $em = "Error deleting learning path";
        Util::redirect("../Learning-Paths.php", "error", $em);
    }
} else {
    $em = "Invalid request";
    Util::redirect("../Learning-Paths.php", "error", $em);
}
?> 