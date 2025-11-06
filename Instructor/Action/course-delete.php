<?php
session_start();
include "../../Utils/Util.php";
include "../../Utils/Validation.php";

if (isset($_SESSION['username']) && isset($_SESSION['instructor_id'])) {
    if (isset($_GET['course_id'])) {
        $course_id = Validation::clean($_GET['course_id']);
        $instructor_id = $_SESSION['instructor_id'];
        
        include "../../Database.php";
        include "../../Models/Course.php";
        
        $db = new Database();
        $conn = $db->getConnection();
        $course = new Course($conn);
        
        // Verify that the instructor owns this course
        $course_data = $course->getById($course_id);
        if (!$course_data || $course_data['created_by'] !== $instructor_id) {
            $em = "Access denied";
            Util::redirect("../Courses.php", "error", $em);
            exit;
        }
        
        $success = $course->delete($course_id);
        
        if ($success) {
            $em = "Course deleted successfully";
            Util::redirect("../Courses.php", "success", $em);
        } else {
            $em = "Failed to delete course";
            Util::redirect("../Courses.php", "error", $em);
        }
    } else {
        $em = "Invalid course ID";
        Util::redirect("../Courses.php", "error", $em);
    }
} else {
    $em = "First login";
    Util::redirect("../../login.php", "error", $em);
}
?> 