<?php 
session_start();
include "../Utils/Util.php";
include "../Utils/Validation.php";
if (isset($_SESSION['username']) &&
    isset($_SESSION['instructor_id'])) {


  $title = "EduPulse - Upload Courses Materials ";
  include "inc/Header.php";
?>
<div class="wrapper">
  <!-- NavBar -->
  <?php include "inc/NavBar.php"; ?>
  
  <div class="main-content p-4">
    <div class="container-fluid d-flex justify-content-center">
      <div class="card bg-dark text-white shadow w-100" style="max-width: 700px;">
        <div class="card-body">
          <h4 class="mb-4">Upload Courses Materials <a href="Courses-Materials.php" class="btn btn-primary">All Materials</a></h4>

          <form id="Chapter" 
                action="Action/upload-materials.php" 
                enctype="multipart/form-data"
                method="POST">
                    <?php 
                    if (isset($_GET['error'])) { ?>
                      <p class="alert alert-warning text-center"><?=Validation::clean($_GET['error'])?></p>
                    <?php } ?>
                    <?php 
                    if (isset($_GET['success'])) { ?>
                      <p class="alert alert-success text-center"><?=Validation::clean($_GET['success'])?></p>
                    <?php } ?>
                  <div class="mb-3">
                      <label for="chapterTitle" class="form-label">File, Image, Video, PDF, Docx, Zip</label>
                      <input type="file" 
                             class="form-control" 
                             name="file" 
                             required>
                  </div>
                  <button type="submit" class="btn btn-primary">Upload</button>
              </form>
        </div>
      </div>
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