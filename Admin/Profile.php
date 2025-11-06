<?php 
session_start();
include "../Utils/Util.php";
include "../Database.php";
include "../Models/Admin.php";
if (isset($_SESSION['username']) && isset($_SESSION['admin_id'])) {
    $db = new Database();
    $conn = $db->getConnection();
    $admin_id = $_SESSION['admin_id'];
    $admin = new Admin($conn);
    $admin->init($admin_id);
    $admin_data = $admin->get();
    $title = "EduPulse - Admin Profile";
    include "inc/Header.php";
    // Profile image logic
    $profile_img = isset($_SESSION['admin_profile_img']) && file_exists("../assets/Upload/profile/".$_SESSION['admin_profile_img'])
        ? $_SESSION['admin_profile_img']
        : 'default.jpg';
    // Show upload messages
    $msg = '';
    if (isset($_GET['error'])) {
        $msg = '<div class="alert alert-danger">' . htmlspecialchars($_GET['error']) . '</div>';
    } elseif (isset($_GET['success'])) {
        $msg = '<div class="alert alert-success">' . htmlspecialchars($_GET['success']) . '</div>';
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
              <i class="fa fa-user-circle fa-2x"></i> Admin Profile
            </div>
            <div class="card-body text-center">
              <?= $msg ?>
              <img src="../assets/Upload/profile/<?= htmlspecialchars($profile_img) ?>" alt="Profile" class="rounded-circle mb-3" width="100" height="100">
              <form action="Action/upload-admin-profile.php" method="POST" enctype="multipart/form-data" class="mb-3">
                <div class="input-group justify-content-center">
                  <input type="file" name="profile_img" class="form-control" style="max-width:200px;">
                  <button type="submit" class="btn btn-secondary ms-2">Upload</button>
                </div>
              </form>
              <h4><?= htmlspecialchars($admin_data['full_name']) ?></h4>
              <p class="mb-1"><strong>Username:</strong> <?= htmlspecialchars($admin_data['username']) ?></p>
              <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($admin_data['email']) ?></p>
              <div class="mt-4">
                <a href="Change-Password.php" class="btn btn-primary me-2">Change Password</a>
                <a href="Reset-Password.php?for=Admin&admin_id=<?= $admin_id ?>" class="btn btn-secondary">Reset Password</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include "inc/Footer.php"; ?>
<?php 
} else { 
  $em = "First login ";
  Util::redirect("../login.php", "error", $em);
} 
?> 