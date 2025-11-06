<?php
if (file_exists(__DIR__ . '/../../../includes/config.php')) {
    require_once __DIR__ . '/../../../includes/config.php';
} else {
    // Fallback if the direct path is incorrect, try a more general path
    require_once __DIR__ . '/../../includes/config.php';
}
?>
<nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
    <div class="sb-sidenav-menu">
        <div class="nav">
            <div class="sb-sidenav-menu-heading">Core</div>
            <a class="nav-link" href="<?php echo APP_URL; ?>index.php">
                <div class="sb-nav-link-icon"><i class="fas fa-home"></i></div>
                Home
            </a>
            <a class="nav-link" href="dashboard.php#dashboard">
                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                Dashboard
            </a>
            <div class="sb-sidenav-menu-heading">Management</div>
            <a class="nav-link" href="dashboard.php#users">
                <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                Users
            </a>
            <a class="nav-link" href="dashboard.php#categories">
                <div class="sb-nav-link-icon"><i class="fas fa-list-alt"></i></div>
                Categories
            </a>
            <a class="nav-link" href="dashboard.php#books">
                <div class="sb-nav-link-icon"><i class="fas fa-book"></i></div>
                Books
            </a>
            <a class="nav-link" href="dashboard.php#activity">
                <div class="sb-nav-link-icon"><i class="fas fa-history"></i></div>
                Activity Log
            </a>
            <a class="nav-link" href="dashboard.php#settings">
                <div class="sb-nav-link-icon"><i class="fas fa-cog"></i></div>
                Settings
            </a>
            <a class="nav-link" href="<?php echo APP_URL; ?>pages/logout.php">
                <div class="sb-nav-link-icon"><i class="fas fa-sign-out-alt"></i></div>
                Log out
            </a>
        </div>
    </div>
    <div class="sb-sidenav-footer">
        <div class="small">Logged in as:</div>
        Admin
    </div>
</nav>