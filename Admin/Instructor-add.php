<?php 
session_start();
include "../Utils/Util.php";
include "../Utils/Validation.php";
if (isset($_SESSION['username']) &&
    isset($_SESSION['admin_id'])) {

  $title = "EduPulse - Add Instructor ";
  include "inc/Header.php";

    $fname = $uname = $email =$bd = $lname ="";
    if (isset($_GET["fname"])) {
        $fname = Validation::clean($_GET["fname"]);
    }
    if (isset($_GET["uname"])) {
        $uname = Validation::clean($_GET["uname"]);
    }
    if (isset($_GET["email"])) {
        $email = Validation::clean($_GET["email"]);
    }
    if (isset($_GET["bd"])) {
        $bd = Validation::clean($_GET["bd"]);
    }
    if (isset($_GET["lname"])) {
        $lname = Validation::clean($_GET["lname"]);
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
              <i class="fa fa-user-plus fa-2x"></i> Add Instructor Profile
            </div>
            <div class="card-body">
              <form style="max-width: 100%;" action="Action/instructor-add.php" method="POST">
                <?php 
                  if (isset($_GET['error'])) { ?>
                    <div class="alert alert-danger text-center"><?=Validation::clean($_GET['error'])?></div>
                <?php } ?>
                <?php 
                  if (isset($_GET['success'])) { ?>
                    <div class="alert alert-success text-center"><?=Validation::clean($_GET['success'])?></div>
                <?php } ?>
                <div class="mb-3">
                  <label for="instructorFirstName" class="form-label">First Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="instructorFirstName" placeholder="Enter instructor's first name" name="fname" value="<?=$fname?>" required>
                </div>
                <div class="mb-3">
                  <label for="instructorLastName" class="form-label">Last Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="instructorLastName" placeholder="Enter instructor's last name" name="lname" value="<?=$lname?>" required>
                </div>
                <div class="mb-3">
                  <label for="instructorDOB" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                  <input type="date" class="form-control" id="instructorDOB" value="<?=$db?>" name="date_of_birth" required>
                </div>
                <div class="mb-3">
                  <label for="instructorEmail" class="form-label">Email <span class="text-danger">*</span></label>
                  <input type="email" class="form-control" id="instructorEmail" placeholder="Enter instructor's email"  name="email" value="<?=$email?>" required>
                </div>
                <div class="mb-3">
                  <label for="instructorUsername" class="form-label">Username <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <input type="text" class="form-control" id="instructorUsername" placeholder="Enter instructor's username" name="username" value="<?=$uname?>" required>
                    <button class="btn btn-outline-secondary" type="button" id="generateUsernameButton" onclick="generateUsername()">Auto Generate</button>
                  </div>
                </div>
                <div class="mb-3">
                  <label for="instructorPassword" class="form-label">Password <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <input type="password" class="form-control" id="instructorPassword" name="password" placeholder="Enter new password" aria-describedby="generatePasswordButton" required>
                    <button class="btn btn-outline-secondary" type="button" id="generatePasswordButton" onclick="generatePassword()">Auto Generate</button>
                  </div>
                </div>
                <small class="text-muted">* Required fields</small>
                <button type="submit" class="btn btn-primary w-100 mt-3">Save Changes</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
  function generatePassword() {
      const randomString = Math.random().toString(36).slice(-6);
      document.getElementById('instructorPassword').value = randomString;
      document.getElementById('instructorPassword').type = "text";
  }
  function generateUsername() {
      const randomString = Math.random().toString(36).slice(-3);
      let name = document.getElementById('instructorFirstName').value;
      name = name + randomString;
      document.getElementById('instructorUsername').value = name;
  }
</script>
<?php include "inc/Footer.php"; ?>

<?php

}else { 
$em = "First login ";
Util::redirect("../login.php", "error", $em);
} ?>