<?php 
session_start();
include "../Utils/Util.php";
include "../Utils/Validation.php";
if (isset($_SESSION['username']) && isset($_SESSION['admin_id'])) {
    $title = "EduPulse - Add Course ";
    include "inc/Header.php";
    $course_title = $description = $status = "";
    if (isset($_GET["title"])) {
        $course_title = Validation::clean($_GET["title"]);
    }
    if (isset($_GET["description"])) {
        $description = Validation::clean($_GET["description"]);
    }
    if (isset($_GET["status"])) {
        $status = Validation::clean($_GET["status"]);
    }
?>
<div class="wrapper">
  <?php include "inc/NavBar.php"; ?>
  <div class="main-content p-4">
    <div class="container-fluid">
      <div class="row justify-content-center">
        <div class="col-md-6">
          <div class="card bg-dark text-white shadow">
            <div class="card-header text-center">
              <i class="fa fa-plus fa-2x"></i> Add Course
            </div>
            <div class="card-body">
              <form style="max-width: 100%;" action="Action/course-add.php" method="POST">
                <?php 
                  if (isset($_GET['error'])) { ?>
                    <div class="alert alert-danger text-center"><?=Validation::clean($_GET['error'])?></div>
                <?php } ?>
                <?php 
                  if (isset($_GET['success'])) { ?>
                    <div class="alert alert-success text-center"><?=Validation::clean($_GET['success'])?></div>
                <?php } ?>
                <div class="mb-3">
                  <label for="courseTitle" class="form-label">Title <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="courseTitle" name="title" placeholder="Enter course title" value="<?=$course_title?>" required>
                </div>
                <div class="mb-3">
                  <label for="courseDescription" class="form-label">Description <span class="text-danger">*</span></label>
                  <textarea class="form-control" id="courseDescription" name="description" placeholder="Enter course description" rows="4" required><?=$description?></textarea>
                </div>
                <div class="mb-3">
                  <label for="courseStatus" class="form-label">Status <span class="text-danger">*</span></label>
                  <select class="form-select" id="courseStatus" name="status" required>
                    <option value="" disabled selected>Select status</option>
                    <option value="Public" <?= $status=="Public" ? "selected" : "" ?>>Public</option>
                    <option value="Private" <?= $status=="Private" ? "selected" : "" ?>>Private</option>
                  </select>
                </div>
                <small class="text-muted">* Required fields</small>
                <button type="submit" class="btn btn-primary w-100 mt-3">Add Course</button>
              </form>
            </div>
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
}
?> 