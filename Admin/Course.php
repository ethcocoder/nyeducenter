<?php 
session_start();
include "../Utils/Util.php";
include "../Utils/Validation.php";
if (isset($_SESSION['username']) &&
    isset($_SESSION['admin_id'])) {
    
  if (isset($_GET['course_id'])) {
     include "../Controller/Admin/Course.php";
     $_id = Validation::clean($_GET['course_id']);
     $_chapter_id = 1;
     $_topic_id = 1;
     if(isset($_GET['chapter'])) {
      $_chapter_id = Validation::clean($_GET['chapter']);
     }
     if(isset($_GET['topic'])) {
      $_topic_id = Validation::clean($_GET['topic']);
     }
     $psag_exes = pageExes($_id, $_chapter_id);
     if($psag_exes == 0){
         Util::redirect("../404.php", "error", "404");
     }
     
     $course = getCourseDetailsById($_id, $_chapter_id, $_topic_id);

     if (empty($course['course']['course_id'])) {
       $em = "Invalid course id";
       Util::redirect("Courses.php", "error", $em);
     }
      $num_topic = 0;

    # Header
    $title = "EduPulse - ". $course['course']["title"];
    include "inc/Header.php";
    
?>
<div class="wrapper">
  <?php include "inc/NavBar.php";?>
  <div class="main-content p-4">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-4">
          <div class="card bg-dark text-white shadow mb-4">
            <div class="card-header">Chapters & Topics</div>
            <div class="card-body">
              <ul class="list-group">
                <?php foreach ($course['chapters'] as $chapter ) { ?>
                  <li class="list-group-item bg-dark text-white">
                    <a href="#" class="btn btn-outline-primary btn-sm mb-2 w-100"><?=$chapter['title'] ?></a>
                    <ul class="list-unstyled ms-3">
                      <?php foreach ($course['topics'] as $topic ) {
                        if ($chapter['chapter_id'] != $topic['chapter_id']) continue;
                      ?>
                        <li>
                          <a href="Course.php?course_id=<?=$_id ?>&chapter=<?=$chapter['chapter_id']?>&topic=<?=$topic['topic_id']?>" class="btn btn-outline-secondary btn-sm mb-1 w-100"><?=$topic["title"]?></a>
                        </li>
                      <?php } ?>
                    </ul>
                  </li>
                <?php } ?>
              </ul>
            </div>
          </div>
        </div>
        <div class="col-md-8">
          <div class="card bg-dark text-white shadow">
            <div class="card-header d-flex justify-content-between align-items-center">
              <span><?=$course['course']["title"]?></span>
              <a href="Course-edit.php?course_id=<?=$_id ?>" class="btn btn-primary btn-sm">Update Course</a>
            </div>
            <div class="card-body">
              <h6 class="mb-2">Chapter: <?=$chapter_title ?? ''?> | Topic: <?=$topic_title ?? ''?></h6>
              <hr>
              <div>
                <?php 
                if (!empty($course['content']["data"])) {
                  echo $course['content']["data"];
                } else {
                  echo '<p class="text-muted">No content available for this topic.</p>';
                }
                ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

 <!-- Footer -->
<?php include "inc/Footer.php"; ?>

<?php
}else { 
  $em = "Invalid course id";
  Util::redirect("Courses.php", "error", $em);
  }

 }else { 
$em = "First login ";
Util::redirect("../login.php", "error", $em);
} ?>