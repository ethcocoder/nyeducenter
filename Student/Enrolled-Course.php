<?php 
session_start();
include "../Utils/Util.php";
if (isset($_SESSION['username']) &&
    isset($_SESSION['student_id'])) {

    include "../Controller/Student/Course.php";
    include "../Controller/Student/EnrolledStudent.php";
    
    $student_id = $_SESSION['student_id'];
    $enrolled_courses = getEnrolledCourses($student_id);
    $row_count =  $enrolled_courses[0]['count'];

    # Header
    $title = "EduPulse - Enrolled Courses ";
    include "inc/Header.php";
    
?>
<div class="wrapper">
  <?php include "inc/NavBar.php"; ?>
  <div class="main-content p-4">
    <h4 class="mb-4">Enrolled Courses (<?=$row_count?>)</h4>
    <div class="row">
      <?php for ($i = 1; $i <= $row_count; $i++) { $course = $enrolled_courses[$i]; ?>
      <?php
        $cover = !empty($course["cover_img"]) && file_exists("../Upload/thumbnail/" . $course["cover_img"])
            ? $course["cover_img"]
            : "default_course.jpg";
      ?>
      <div class="col-md-6 col-lg-4 mb-4">
        <div class="card bg-dark text-white shadow h-100">
          <img src="../Upload/thumbnail/<?=$cover?>" class="card-img-top" alt="course" style="height:200px;object-fit:cover;">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title"><?=$course["title"]?></h5>
            <p class="card-text flex-grow-1"><?=$course["description"]?></p>
            <p class="card-text"><small class="text-body-secondary"><?=$course["created_at"]?></small></p>
            <a href="Course.php?course_id=<?=$course["course_id"]?>" class="btn btn-primary mt-auto">View Course</a>
          </div>
        </div>
      </div>
      <?php } ?>
    </div>
  </div>
</div>

 <!-- Footer -->
<?php include "inc/Footer.php"; ?>

<?php
 }else { 
$em = "First login ";
Util::redirect("../login.php", "error", $em);
} ?>