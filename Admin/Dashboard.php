<?php
require_once "../Utils/Session.php";
require_once "../Utils/Validation.php";
require_once "../Utils/Util.php";
require_once "../Database.php";
require_once "../Models/Admin.php";

// Initialize session
Session::init();

// Check if user is logged in and is an admin
Session::requireRole('admin');

// Get admin data
$db = new Database();
$conn = $db->connect();
$admin = new Admin($conn);
$admin_data = $admin->getAdminById($_SESSION['admin_id']);

// Check session timeout
if (!Session::checkTimeout()) {
    header("Location: ../login.php?error=Session expired");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Online Learning System</title>
    <!-- Add security headers -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:;">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($admin_data['username']); ?>!</h1>
        
        <?php if (isset($_SESSION['error'])) { ?>
            <div class="alert alert-danger">
                <?php 
                    echo htmlspecialchars($_SESSION['error']);
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php } ?>

        <?php if (isset($_SESSION['success'])) { ?>
            <div class="alert alert-success">
                <?php 
                    echo htmlspecialchars($_SESSION['success']);
                    unset($_SESSION['success']);
                ?>
            </div>
        <?php } ?>

        <div class="dashboard-content">
            <h2>Admin Dashboard</h2>
            <p>This is your admin dashboard. You can manage the system from here.</p>
            
            <div class="dashboard-links">
                <a href="Manage-Users.php" class="btn btn-primary">Manage Users</a>
                <a href="Manage-Courses.php" class="btn btn-primary">Manage Courses</a>
                <a href="Manage-Assessments.php" class="btn btn-primary">Manage Assessments</a>
                <a href="Profile.php" class="btn btn-info">View Profile</a>
                <a href="../Action/logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </div>

    <script src="../js/script.js"></script>
</body>
</html> 