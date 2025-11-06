<?php 
session_start();
include "../Utils/Util.php";
include "../Utils/Validation.php";
if (isset($_SESSION['username']) &&
    isset($_SESSION['student_id'])) {
    include "../Controller/Student/Student.php";

    $_id =  $_SESSION['student_id'];
    $student = getById($_id);

    // --- START DEBUGGING --- //
    echo '<pre>Debugging $student:';
    var_dump($student);
    echo '</pre>';

    $certificates = getCertificate($_id);
    echo '<pre>Debugging $certificates:';
    var_dump($certificates);
    echo '</pre>';
    // --- END DEBUGGING --- //

   if (empty($student['student_id'])) {
     $em = "Invalid Student id";
     Util::redirect("../logout.php", "error", $em);
   }
   // get Certificates
   
    # Header
    $title = "EduPulse - Profile View ";
    include "inc/Header.php";

?>
<div class="wrapper">
  <?php include "inc/NavBar.php"; ?>
  <div class="main-content p-4">
    <div class="container-fluid">
      <div class="row justify-content-center">
        <!-- Profile Sidebar -->
        <div class="col-md-4 mb-4">
          <div class="card bg-dark text-white shadow h-100">
            <div class="card-body text-center">
              <img src="../Upload/profile/<?= htmlspecialchars($student['profile_img']) ?>" class="rounded-circle mb-3" alt="PROFILE IMG" width="150">
              <form action="Action/upload-profile.php" enctype="multipart/form-data" method="POST" class="mb-3">
                <input type="file" class="form-control form-control-sm mb-2" name="profile_picture">
                <button type="submit" class="btn btn-danger w-100">Change Profile Picture</button>
              </form>
              <h4 class="pt-2"><b><?= htmlspecialchars($student['username']) ?></b></h4>
              <ul class="list-group mt-3">
                <li class="list-group-item bg-dark border-0"><a href="Profile-View.php" class="btn btn-outline-primary w-100 text-white">View Profile</a></li>
                <li class="list-group-item bg-dark border-0"><a href="Profile-Edit.php" class="btn btn-outline-primary w-100 text-white">Edit Profile</a></li>
                <li class="list-group-item bg-dark border-0"><a href="Profile-Edit.php#ChangePassword" class="btn btn-outline-primary w-100 text-white">Change Password</a></li>
                <li class="list-group-item bg-dark border-0"><a href="../Logout.php" class="btn btn-outline-primary w-100 text-white">Logout</a></li>
              </ul>
            </div>
          </div>
        </div>
        <!-- Account Info and Certificates -->
        <div class="col-md-8 mb-4">
          <div class="card bg-dark text-white shadow mb-4">
            <div class="card-header bg-primary text-white">
              <h4 class="mb-0">Account Information</h4>
            </div>
            <div class="card-body p-0">
              <table class="table table-dark table-bordered mb-0">
                <tr><td>First name:</td><td><?= htmlspecialchars($student['first_name']) ?></td></tr>
                <tr><td>Last name:</td><td><?= htmlspecialchars($student['last_name']) ?></td></tr>
                <tr><td>Email:</td><td><?= htmlspecialchars($student['email']) ?></td></tr>
                <tr><td>Date of birth:</td><td><?= htmlspecialchars($student['date_of_birth']) ?></td></tr>
                <tr><td>Joined at:</td><td><?= htmlspecialchars($student['date_of_joined']) ?></td></tr>
                <tr><td>Student id:</td><td><?= htmlspecialchars($student['student_id']) ?></td></tr>
                <tr><td>Username:</td><td><?= htmlspecialchars($student['username']) ?></td></tr>
              </table>
            </div>
          </div>
          <div class="card bg-dark text-white shadow">
            <div class="card-header bg-primary text-white">
              <h4 class="mb-0">Certificates</h4>
            </div>
            <div class="card-body p-0">
              <?php if (!empty($certificates[0]["certificate_id"])) { ?>
                <ol class="list-group list-group-numbered list-group-flush">
                  <?php foreach ($certificates as $i => $certificate) { ?>
                    <li class="list-group-item bg-dark text-white border-0">
                      <a href="../Certificate.php?certificate_id=<?= htmlspecialchars($certificate['certificate_id']) ?>" class="text-info">
                        <?= htmlspecialchars($certificate['course_title']) ?>
                      </a>
                    </li>
                  <?php } ?>
                </ol>
              <?php } else { ?>
                <div class="alert alert-info m-3">No certificates found.</div>
              <?php } ?>
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
