<?php 
session_start();
include "../Utils/Util.php";
include "../Utils/Validation.php";
if (isset($_SESSION['username']) &&
    isset($_SESSION['student_id'])) {
    
   if (isset($_GET['course_id'])) {
      include "../Controller/Student/Course.php";
      $_id = Validation::clean($_GET['course_id']);
      $course = getCourseDetails($_id); 
    }else{
        $em = "Invalid course id ";
        Util::redirect("../Courses.php", "error", $em);
    }
    if ($course == 0) {
       $em = "Invalid course id ";
        Util::redirect("Courses.php", "error", $em);
    }
    # Header
    $title = "EduPulse - Students ";
    include "inc/Header.php";

?>
<div class="wrapper">
  <?php include "inc/NavBar.php"; ?>
  <div class="main-content p-4">
    <div class="container-fluid d-flex justify-content-center">
      <div class="card bg-dark text-white shadow w-100" style="max-width:700px;">
        <div class="card-header bg-primary text-white">
          <h4 class="mb-0">Course Title: <?= isset($course['title']) ? $course['title'] : 'N/A' ?></h4>
        </div>
        <div class="card-body">
          <h5 class="mb-3">Course Description:</h5>
          <p><?= isset($course['description']) ? $course['description'] : 'N/A' ?></p>
          <ul class="list-group list-group-flush mb-3">
            <li class="list-group-item bg-dark text-white">Lessons: <?= isset($course['topic_nums']) ? $course['topic_nums'] : 'N/A' ?></li>
            <li class="list-group-item bg-dark text-white">Chapters: <?= isset($course['chapter_nums']) ? $course['chapter_nums'] : 'N/A' ?></li>
            <li class="list-group-item bg-dark text-white">Instructor: <?= isset($course['instructor_name']) ? $course['instructor_name'] : 'N/A' ?></li>
            <li class="list-group-item bg-dark text-white">Created at: <mark><?= isset($course['created_at']) ? $course['created_at'] : 'N/A' ?></mark></li>
            <li class="list-group-item bg-dark text-white"><mark>Certificate After Complete The Course</mark></li>
          </ul>
        </div>
      </div>
    </div>
    <div class="container-fluid d-flex justify-content-center mt-4">
      <div class="card bg-dark text-white shadow w-100" style="max-width:700px;">
        <div class="card-body">
          <img src="../Upload/thumbnail/<?= isset($course['cover_img']) && file_exists("../Upload/thumbnail/" . $course['cover_img']) ? $course['cover_img'] : 'default_course.jpg' ?>" class="img-fluid rounded mb-3" alt="course cover">
          <div>
            <strong>Lessons:</strong> <?= isset($course['topic_nums']) ? $course['topic_nums'] : 'N/A' ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include "inc/Footer.php"; ?>

<?php
 }else { 
$em = "First login ";
Util::redirect("../login.php", "error", $em);
} ?>