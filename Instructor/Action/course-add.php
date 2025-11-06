<?php 
require_once "../../Utils/Session.php";
require_once "../../Utils/Validation.php";
require_once "../../Utils/Util.php";
require_once "../../Database.php";
require_once "../../Models/Course.php";

// Initialize session
Session::init();

if (isset($_SESSION['username']) && isset($_SESSION['instructor_id'])) {
    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        try {
            // Validate CSRF token
            if (!isset($_POST['csrf_token']) || !Session::validateCSRFToken($_POST['csrf_token'])) {
                throw new Exception("Invalid request");
            }

            $title = Validation::clean($_POST["title"]);
            $description = Validation::clean($_POST["description"]);
            $instructor_id = $_SESSION['instructor_id'];
            $cover = "default_course.jpg";

            $data = "title=".$title."&description=".$description;

            if (!Validation::name($title)) {
                throw new Exception("Invalid Title");
            }
            
            if (Validation::is_empty($description)) {
                throw new Exception("Description is required");
            }

            if (isset($_FILES['cover']['name'])) {
                $img_name = $_FILES['cover']['name'];
                $tmp_name = $_FILES['cover']['tmp_name'];
                $error = $_FILES['cover']['error'];

                if($error === 0){
                    $img_ex = pathinfo($img_name, PATHINFO_EXTENSION);
                    $img_ex_to_lc = strtolower($img_ex);

                    $allowed_exs = array('jpg', 'jpeg', 'png');
                    if(in_array($img_ex_to_lc, $allowed_exs)){
                        $new_img_name = uniqid("COVER-", true).'.'.$img_ex_to_lc;
                        $img_upload_path = '../../Upload/thumbnail/'.$new_img_name;
                        move_uploaded_file($tmp_name, $img_upload_path);
                        // update the Database
                        $cover = $new_img_name;
                    }else {
                        throw new Exception("You can't upload files of this type");
                    }
                }else {
                    throw new Exception("unknown error occurred!");
                }
            }

            $db = new Database();
            $conn = $db->getConnection();
            $course = new Course($conn);
            
            if ($course->create($title, $description, $instructor_id, $cover)) {
                Util::redirect("../Courses.php", "success", "Course created successfully");
            } else {
                throw new Exception("Failed to create course");
            }
        } catch (Exception $e) {
            error_log("Course creation error: " . $e->getMessage());
            Util::redirect("../Courses-add.php", "error", $e->getMessage(), $data);
        }
    } else {
        Util::redirect("../Courses-add.php", "error", "Invalid request method");
    }
} else {
    Util::redirect("../../login.php", "error", "Please login first");
}