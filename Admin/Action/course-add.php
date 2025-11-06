<?php
session_start();
include "../../Utils/Util.php";
include "../../Utils/Validation.php";
if (isset($_SESSION['username']) && isset($_SESSION['admin_id'])) {
    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        include "../../Database.php";
        include "../../Models/Course.php";
        $title = Validation::clean($_POST['title']);
        $description = Validation::clean($_POST['description']);
        $status = isset($_POST['status']) ? Validation::clean($_POST['status']) : 'Public';
        $data = "title=".$title."&description=".$description."&status=".$status;

        if (!Validation::name($title)) {
            $em = "Invalid course title";
            Util::redirect("../Course-add.php", "error", $em, $data);
        } else if (empty($description)) {
            $em = "Invalid course description";
            Util::redirect("../Course-add.php", "error", $em, $data);
        } else {
            $db = new Database();
            $conn = $db->getConnection();
            $course = new Course($conn);
            $course_data = [$title, $description, null, $status, "default_course.jpg"];
            $res = $course->insert($course_data);
            if ($res) {
                $sm = "Course successfully added!";
                Util::redirect("../Course-add.php", "success", $sm);
            } else {
                $em = "An error occurred while adding the course.";
                Util::redirect("../Course-add.php", "error", $em, $data);
            }
            $conn = null;
        }
    } else {
        $em = "REQUEST Error";
        Util::redirect("../Course-add.php", "error", $em);
    }
} else {
    $em = "First login ";
    Util::redirect("../../login.php", "error", $em);
} 