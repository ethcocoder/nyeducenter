<?php
require_once __DIR__ . '/../../Utils/PathHelper.php';
$pathHelper = PathHelper::getInstance();

$profile_img = isset($_SESSION['profile_img']) ? $_SESSION['profile_img'] : 'default.png';
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Instructor';
?>
<div class="sidebar bg-dark text-white p-3">
    <div class="logo text-center mb-4">
        <?php
        $profile_img_path = $pathHelper->getProfileImagePath($profile_img);
        ?>
        <img src="<?= $profile_img_path ?>" alt="Profile" class="rounded-circle mb-2" style="width:70px;height:70px;object-fit:cover;">
        <h2 class="h5"><?= htmlspecialchars($username) ?></h2>
    </div>
    <ul class="nav flex-column mb-auto">
        <li class="nav-item mb-2">
            <a class="nav-link btn btn-primary text-start text-white" href="Dashboard.php">
                <i class="fa fa-dashboard me-2"></i> Dashboard
            </a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link btn btn-primary text-start text-white" href="My-Courses.php">
                <i class="fa fa-graduation-cap me-2"></i> My Courses
            </a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link btn btn-primary text-start text-white" href="Learning-Paths.php">
                <i class="fa fa-road me-2"></i> Learning Paths
            </a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link btn btn-primary text-start text-white" href="Assignments.php">
                <i class="fa fa-tasks me-2"></i> Assignments
            </a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link btn btn-primary text-start text-white" href="Assessments.php">
                <i class="fa fa-check-square me-2"></i> Assessments
            </a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link btn btn-primary text-start text-white" href="Projects.php">
                <i class="fa fa-project-diagram me-2"></i> Projects
            </a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link btn btn-primary text-start text-white" href="Search.php">
                <i class="fa fa-search me-2"></i> Search
            </a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link btn btn-primary text-start text-white" href="Support-Services.php">
                <i class="fa fa-life-ring me-2"></i> Support Services
            </a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link btn btn-primary text-start text-white" href="Progress.php">
                <i class="fa fa-chart-line me-2"></i> Progress Tracking
            </a>
        </li>
    </ul>
    <ul class="nav flex-column mt-auto pt-3 border-top border-secondary">
        <li class="nav-item mb-2">
            <a class="nav-link btn btn-primary text-start text-white" href="Profile-View.php">
                <i class="fa fa-user-circle me-2"></i> View Profile
            </a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link btn btn-primary text-start text-white" href="Profile-Edit.php">
                <i class="fa fa-user-edit me-2"></i> Edit Profile
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

    @media (max-width: 768px) {
        .sidebar {
            margin-left: -250px;
        }
        .sidebar.active {
            margin-left: 0;
        }
        body {
            padding-left: 0;
        }
        body.active {
            padding-left: 250px;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add mobile menu toggle functionality
    const menuToggle = document.createElement('button');
    menuToggle.className = 'btn btn-primary d-md-none position-fixed';
    menuToggle.style.cssText = 'top: 10px; left: 10px; z-index: 1001;';
    menuToggle.innerHTML = '<i class="fa fa-bars"></i>';
    document.body.appendChild(menuToggle);

    menuToggle.addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('active');
        document.body.classList.toggle('active');
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        const sidebar = document.querySelector('.sidebar');
        const menuToggle = document.querySelector('.btn-primary.d-md-none');
        
        if (window.innerWidth <= 768 && 
            !sidebar.contains(e.target) && 
            !menuToggle.contains(e.target) && 
            sidebar.classList.contains('active')) {
            sidebar.classList.remove('active');
            document.body.classList.remove('active');
        }
    });
});
</script> 