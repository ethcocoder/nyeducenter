<?php 
session_start();
include "../Utils/Util.php";
include "../Utils/Validation.php";
if (isset($_SESSION['username']) &&
    isset($_SESSION['instructor_id'])) {
    include "../Controller/Instructor/Instructor.php";

    $_id =  $_SESSION['instructor_id'];
    $instructor = getById($_id);

   if (empty($instructor['instructor_id'])) {
     $em = "Invalid instructor id";
     Util::redirect("../logout.php", "error", $em);
   }
    # Header
    $title = "EduPulse - Instructor ";
    include "inc/Header.php";

?>
<div class="wrapper">
  <!-- NavBar -->
  <?php include "inc/NavBar.php"; ?>
  <div class="main-content p-4">
    <div class="container-fluid">
      <div class="row justify-content-center">
        <!-- Profile Sidebar -->
        <div class="col-md-4 mb-4">
          <div class="card bg-dark text-white shadow h-100">
            <div class="card-body text-center">
              <img src="../Upload/profile/<?= htmlspecialchars($instructor['profile_img']) ?>" class="rounded-circle mb-3" alt="PROFILE IMG" width="150">
              <form action="Action/upload-profile.php" enctype="multipart/form-data" method="POST" class="mb-3">
                <input type="file" class="form-control form-control-sm mb-2" name="profile_picture">
                <button type="submit" class="btn btn-danger w-100">Change Profile Picture</button>
              </form>
              <h4 class="pt-2"><b><?= htmlspecialchars($instructor['username']) ?></b></h4>
              <ul class="list-group mt-3">
                <li class="list-group-item bg-dark border-0"><a href="Profile-View.php" class="btn btn-outline-primary w-100 text-white">View Profile</a></li>
                <li class="list-group-item bg-dark border-0"><a href="Profile-Edit.php" class="btn btn-outline-primary w-100 text-white">Edit Profile</a></li>
                <li class="list-group-item bg-dark border-0"><a href="Profile-Edit.php#ChangePassword" class="btn btn-outline-primary w-100 text-white">Change Password</a></li>
                <li class="list-group-item bg-dark border-0"><a href="../Logout.php" class="btn btn-outline-danger w-100 text-white">Logout</a></li>
              </ul>
            </div>
          </div>
        </div>
        <!-- Account Information -->
        <div class="col-md-8 mb-4">
          <div class="card bg-dark text-white shadow h-100">
            <div class="card-header bg-primary text-white">
              <h4 class="mb-0">Account Information</h4>
            </div>
            <div class="card-body p-0">
              <table class="table table-dark table-bordered mb-0">
                <tr><td>First name:</td><td><?= htmlspecialchars($instructor['first_name']) ?></td></tr>
                <tr><td>Last name:</td><td><?= htmlspecialchars($instructor['last_name']) ?></td></tr>
                <tr><td>Email:</td><td><?= htmlspecialchars($instructor['email']) ?></td></tr>
                <tr><td>Date of birth:</td><td><?= htmlspecialchars($instructor['date_of_birth']) ?></td></tr>
                <tr><td>Joined at:</td><td><?= htmlspecialchars($instructor['date_of_joined']) ?></td></tr>
                <tr><td>Username:</td><td><?= htmlspecialchars($instructor['username']) ?></td></tr>
              </table>
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
$em = "First login ";
Util::redirect("../login.php", "error", $em);
} ?>
