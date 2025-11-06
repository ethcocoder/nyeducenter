<?php
session_start();
include "../../Utils/Util.php";
include "../../Controller/Teacher/LearningPath.php";

if (isset($_SESSION['username']) && isset($_SESSION['teacher_id'])) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $path_id = $_POST['path_id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $duration = $_POST['duration'];
        $status = $_POST['status'];
        $objectives = isset($_POST['objectives']) ? $_POST['objectives'] : [];
        $criteria = isset($_POST['criteria']) ? $_POST['criteria'] : [];
        
        // Validate required fields
        if (empty($path_id) || empty($title) || empty($description) || 
            empty($duration) || empty($status) || empty($objectives)) {
            $em = "All required fields must be filled";
            Util::redirect("../Add-Module.php?path_id=" . $path_id, "error", $em);
        }
        
        // Verify teacher has access to the learning path
        $learning_path = getLearningPathDetails($path_id);
        if (!$learning_path || $learning_path['teacher_id'] != $_SESSION['teacher_id']) {
            $em = "Learning path not found or you don't have access";
            Util::redirect("../Learning-Paths.php", "error", $em);
        }
        
        // Create module
        $result = createModule(
            $path_id,
            $title,
            $description,
            $duration,
            $status,
            $objectives,
            $criteria
        );
        
        if ($result) {
            $sm = "Module created successfully";
            Util::redirect("../Manage-Modules.php?path_id=" . $path_id, "success", $sm);
        } else {
            $em = "Error creating module";
            Util::redirect("../Add-Module.php?path_id=" . $path_id, "error", $em);
        }
    } else {
        $em = "Invalid request method";
        Util::redirect("../Learning-Paths.php", "error", $em);
    }
} else {
    $em = "First login ";
    Util::redirect("../../login.php", "error", $em);
}
?> 