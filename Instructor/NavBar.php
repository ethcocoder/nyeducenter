<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) session_start();
$profile_img = isset($_SESSION['profile_img']) ? $_SESSION['profile_img'] : 'default.png';
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Teacher';
?>
<div class="sidebar bg-dark text-white p-3">
    <div class="logo text-center mb-4">
        <?php
        $profile_img = isset($_SESSION['profile_img']) && file_exists(__DIR__ . '/../../assets/Upload/profile/' . $_SESSION['profile_img'])
            ? '../assets/Upload/profile/' . $_SESSION['profile_img']
            : '../assets/img/teacher_placeholder.png';
        ?>
        <img src="<?= $profile_img ?>" alt="Profile" class="rounded-circle mb-2" style="width:70px;height:70px;object-fit:cover;">
        <h2 class="h5"><?= htmlspecialchars($_SESSION['username'] ?? 'Teacher') ?></h2>
    </div>
    <ul class="nav flex-column mb-auto">
        <li class="nav-item mb-2">
            <a class="nav-link btn btn-primary text-start text-white" href="Dashboard.php">
                <i class="fa fa-dashboard me-2"></i> Dashboard
            </a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link btn btn-primary text-start text-white" href="Learning-Paths.php">
                <i class="fa fa-graduation-cap me-2"></i> Learning Paths
            </a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link btn btn-primary text-start text-white" href="Manage-Students.php">
                <i class="fa fa-users me-2"></i> Students
            </a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link btn btn-primary text-start text-white" href="Resources.php">
                <i class="fa fa-book me-2"></i> Resources
            </a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link btn btn-primary text-start text-white" href="Assessments.php">
                <i class="fa fa-check-square me-2"></i> Assessments
            </a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link btn btn-primary text-start text-white" href="Support-Requests.php">
                <i class="fa fa-life-ring me-2"></i> Support
            </a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link btn btn-primary text-start text-white" href="Reports.php">
                <i class="fa fa-chart-bar me-2"></i> Reports
            </a>
        </li>
    </ul>
    <ul class="nav flex-column mt-auto pt-3 border-top border-secondary">
        <li class="nav-item mb-2">
            <a class="nav-link btn btn-primary text-start text-white" href="Profile.php">
                <i class="fa fa-user-circle me-2"></i> Profile
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link btn btn-danger text-start text-white" href="../Logout.php">
                <i class="fa fa-sign-out me-2"></i> Logout
            </a>
        </li>
    </ul>
</div>

<style>
    .sidebar {
        min-width: 250px;
        max-width: 250px;
        height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1000;
        overflow-y: auto;
        transition: all 0.3s;
        display: flex;
        flex-direction: column;
    }

    .sidebar .nav-link {
        padding: 10px 15px;
        border-radius: 5px;
        margin-bottom: 5px;
    }

    .sidebar .nav-link:hover {
        background-color: #0d6efd;
        color: #fff !important;
    }

    .sidebar .nav-link.btn-outline-danger:hover {
        background-color: #dc3545;
    }

    body {
        padding-left: 250px;
    }
</style> 