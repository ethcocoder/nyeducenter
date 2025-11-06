<?php
if (!class_exists('NavBar')) {
class NavBar {
    private static $instance = null;
    private $user_role;
    private $user_id;
    private $username;

    private function __construct() {
        if (isset($_SESSION['role'])) {
            $this->user_role = $_SESSION['role'];
            $this->user_id = $_SESSION[$this->user_role . '_id'] ?? null;
            $this->username = $_SESSION['username'] ?? null;
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function render() {
        $role = $this->user_role;
        $username = $this->username;
        
        // Get current page for active state
        $current_page = basename($_SERVER['PHP_SELF']);
        
        // Start navbar HTML
        $html = '<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top shadow-sm">
            <div class="container-fluid">
                <a class="navbar-brand d-flex align-items-center" href="/index.php">
                    <img src="/assets/img/Logo.png" alt="Logo" width="32" height="32" class="me-2">
                    <span>EduPulse</span>
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
                        data-bs-target="#navbarMain" aria-controls="navbarMain" 
                        aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarMain">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">';
        
        // Common navigation items
        $html .= $this->getCommonNavItems($current_page);
        
        // Role-specific navigation items
        switch ($role) {
            case 'student':
                $html .= $this->getStudentNavItems($current_page);
                break;
            case 'instructor':
                $html .= $this->getInstructorNavItems($current_page);
                break;
            case 'admin':
                $html .= $this->getAdminNavItems($current_page);
                break;
        }
        
        // End main nav items
        $html .= '</ul>';
        
        // Right side items (profile, language, logout)
        $html .= $this->getRightSideItems($username);
        
        // Close navbar
        $html .= '</div></div></nav>';
        
        return $html;
    }

    private function getCommonNavItems($current_page) {
        return '
            <li class="nav-item">
                <a class="nav-link ' . ($current_page === 'Dashboard.php' ? 'active' : '') . '" 
                   href="/' . $this->user_role . '/Dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link ' . ($current_page === 'Courses.php' ? 'active' : '') . '" 
                   href="/' . $this->user_role . '/Courses.php">
                    <i class="fas fa-book"></i> Courses
                </a>
            </li>';
    }

    private function getStudentNavItems($current_page) {
        return '
            <li class="nav-item">
                <a class="nav-link ' . ($current_page === 'My-Courses.php' ? 'active' : '') . '" 
                   href="/Student/My-Courses.php">
                    <i class="fas fa-graduation-cap"></i> My Courses
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link ' . ($current_page === 'Assignments.php' ? 'active' : '') . '" 
                   href="/Student/Assignments.php">
                    <i class="fas fa-tasks"></i> Assignments
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link ' . ($current_page === 'Assessments.php' ? 'active' : '') . '" 
                   href="/Student/Assessments.php">
                    <i class="fas fa-clipboard-check"></i> Assessments
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link ' . ($current_page === 'Projects.php' ? 'active' : '') . '" 
                   href="/Student/Projects.php">
                    <i class="fas fa-project-diagram"></i> Projects
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link ' . ($current_page === 'Learning-Paths.php' ? 'active' : '') . '" 
                   href="/Student/Learning-Paths.php">
                    <i class="fas fa-road"></i> Learning Paths
                </a>
            </li>';
    }

    private function getInstructorNavItems($current_page) {
        return '
            <li class="nav-item">
                <a class="nav-link ' . ($current_page === 'Courses-add.php' ? 'active' : '') . '" 
                   href="/Instructor/Courses-add.php">
                    <i class="fas fa-plus-circle"></i> Add Course
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link ' . ($current_page === 'Quiz-Create.php' ? 'active' : '') . '" 
                   href="/Instructor/Quiz-Create.php">
                    <i class="fas fa-question-circle"></i> Create Quiz
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link ' . ($current_page === 'Track-Progress.php' ? 'active' : '') . '" 
                   href="/Instructor/Track-Progress.php">
                    <i class="fas fa-chart-line"></i> Track Progress
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link ' . ($current_page === 'Learning-Paths.php' ? 'active' : '') . '" 
                   href="/Instructor/Learning-Paths.php">
                    <i class="fas fa-road"></i> Learning Paths
                </a>
            </li>';
    }

    private function getAdminNavItems($current_page) {
        return '
            <li class="nav-item">
                <a class="nav-link ' . ($current_page === 'Students.php' ? 'active' : '') . '" 
                   href="/Admin/Students.php">
                    <i class="fas fa-user-graduate"></i> Students
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link ' . ($current_page === 'Instructors.php' ? 'active' : '') . '" 
                   href="/Admin/Instructors.php">
                    <i class="fas fa-chalkboard-teacher"></i> Instructors
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link ' . ($current_page === 'Reports.php' ? 'active' : '') . '" 
                   href="/Admin/Reports.php">
                    <i class="fas fa-chart-bar"></i> Reports
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link ' . ($current_page === 'System-Logs.php' ? 'active' : '') . '" 
                   href="/Admin/System-Logs.php">
                    <i class="fas fa-history"></i> System Logs
                </a>
            </li>';
    }

    private function getRightSideItems($username) {
        return '
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" 
                       role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle"></i> ' . htmlspecialchars($username) . '
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                        <li>
                            <a class="dropdown-item" href="/' . $this->user_role . '/Profile-View.php">
                                <i class="fas fa-user"></i> View Profile
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="/' . $this->user_role . '/Profile-Edit.php">
                                <i class="fas fa-user-edit"></i> Edit Profile
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="/logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" id="languageSwitcher">
                        <img src="/assets/img/et-flag.png" alt="Language" width="24">
                    </a>
                </li>
            </ul>';
    }
}
} 