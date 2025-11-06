<?php
session_start();
include "../../Utils/Util.php";
include "../../Controller/Teacher/LearningPath.php";

if (isset($_SESSION['username']) && isset($_SESSION['teacher_id'])) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $title = $_POST['title'];
        $subject = $_POST['subject'];
        $grade_level = $_POST['grade_level'];
        $semester = $_POST['semester'];
        $description = $_POST['description'];
        $objectives = isset($_POST['objectives']) ? $_POST['objectives'] : [];
        $prerequisites = isset($_POST['prerequisites']) ? $_POST['prerequisites'] : [];
        $credits = $_POST['credits'];
        $status = $_POST['status'];
        
        // Validate required fields
        if (empty($title) || empty($subject) || empty($grade_level) || 
            empty($semester) || empty($description) || empty($objectives)) {
            $em = "All required fields must be filled";
            Util::redirect("../Add-Learning-Path.php", "error", $em);
        }
        
        // Create learning path
        $result = createLearningPath(
            $_SESSION['teacher_id'],
            $title,
            $subject,
            $grade_level,
            $semester,
            $description,
            $objectives,
            $prerequisites,
            $credits,
            $status
        );
        
        if ($result) {
            $sm = "Learning path created successfully";
            Util::redirect("../Learning-Paths.php", "success", $sm);
        } else {
            $em = "Error creating learning path";
            Util::redirect("../Add-Learning-Path.php", "error", $em);
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