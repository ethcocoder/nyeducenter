/**
 * Navigation Handler for NY Edu Center
 * Manages navigation menu, role-based access, and common navigation elements
 */
document.addEventListener('DOMContentLoaded', function() {
    // Check authentication
    const token = localStorage.getItem('token');
    const userStr = localStorage.getItem('user');
    let user = null;
    
    try {
        if (userStr) {
            user = JSON.parse(userStr);
        }
    } catch (error) {
        console.error('Error parsing user data:', error);
    }
    
    // Get current page path
    const currentPath = window.location.pathname;
    
    // Handle navigation placeholder
    const navigationPlaceholder = document.getElementById('navigation-placeholder');
    if (navigationPlaceholder) {
        loadNavigation(navigationPlaceholder, user);
    }
    
    // Check if on a protected page and redirect if not authenticated
    if (isProtectedPage(currentPath) && !token) {
        window.location.href = '../login.html?redirect=' + encodeURIComponent(currentPath);
        return;
    }
    
    // Check role-based access
    if (user && isRoleRestrictedPage(currentPath, user.role)) {
        window.location.href = getRoleHomePage(user.role);
        return;
    }
    
    // Setup logout functionality
    setupLogout();
    
    // Initialize responsive navigation
    initResponsiveNav();
    
    // Function to load the appropriate navigation
    function loadNavigation(container, user) {
        if (!user) {
            // Public navigation
            container.innerHTML = `
                <nav class="navbar">
                    <div class="navbar-logo">
                        <a href="../index.html">
                            <img src="../img/logo.png" alt="ኑር የትምህርት ማዕከል">
                            <span>ኑር የትምህርት ማዕከል</span>
                        </a>
                    </div>
                    <ul class="navbar-menu">
                        <li><a href="../index.html">መነሻ</a></li>
                        <li><a href="../about.html">ስለ እኛ</a></li>
                        <li><a href="../courses.html">ትምህርቶች</a></li>
                        <li><a href="../contact.html">ያግኙን</a></li>
                        <li class="auth-buttons">
                            <a href="../login.html" class="btn btn-login">ይግቡ</a>
                            <a href="../register.html" class="btn btn-primary">ይመዝገቡ</a>
                        </li>
                    </ul>
                    <button class="mobile-toggle" aria-label="Toggle navigation menu">
                        <span></span><span></span><span></span>
                    </button>
                </nav>
            `;
        } else if (user.role === 'admin') {
            // Admin navigation
            container.innerHTML = `
                <div class="sidebar">
                    <div class="sidebar-logo">
                        <img src="../img/logo.png" alt="Logo">
                        <h3>ኑር የትምህርት ማዕከል</h3>
                    </div>
                    <div class="user-profile">
                        <div class="user-avatar">
                            <img src="../img/avatars/admin.png" alt="User">
                        </div>
                        <div class="user-name">${user.fullName || user.username}</div>
                        <div class="user-role">አስተዳዳሪ</div>
                    </div>
                    <div class="menu">
                        <div class="menu-label">ዋና</div>
                        <a href="dashboard.html" class="menu-item ${isCurrentPage(currentPath, 'admin/dashboard.html') ? 'active' : ''}">
                            <i class="fas fa-tachometer-alt"></i> ዳሽቦርድ
                        </a>
                        <div class="menu-label">ተጠቃሚዎች</div>
                        <a href="manage-users.html" class="menu-item ${isCurrentPage(currentPath, 'admin/manage-users.html') ? 'active' : ''}">
                            <i class="fas fa-users"></i> ተጠቃሚዎች
                        </a>
                        <a href="manage-teachers.html" class="menu-item ${isCurrentPage(currentPath, 'admin/manage-teachers.html') ? 'active' : ''}">
                            <i class="fas fa-chalkboard-teacher"></i> መምህራን
                        </a>
                        <a href="manage-students.html" class="menu-item ${isCurrentPage(currentPath, 'admin/manage-students.html') ? 'active' : ''}">
                            <i class="fas fa-user-graduate"></i> ተማሪዎች
                        </a>
                        <div class="menu-label">ትምህርት</div>
                        <a href="manage-courses.html" class="menu-item ${isCurrentPage(currentPath, 'admin/manage-courses.html') ? 'active' : ''}">
                            <i class="fas fa-book"></i> ትምህርቶች
                        </a>
                        <a href="manage-grades.html" class="menu-item ${isCurrentPage(currentPath, 'admin/manage-grades.html') ? 'active' : ''}">
                            <i class="fas fa-graduation-cap"></i> ክፍሎች
                        </a>
                        <div class="menu-label">ሪፖርቶች</div>
                        <a href="reports.html" class="menu-item ${isCurrentPage(currentPath, 'admin/reports.html') ? 'active' : ''}">
                            <i class="fas fa-chart-line"></i> ሪፖርቶች
                        </a>
                        <div class="menu-label">ሲስተም</div>
                        <a href="settings.html" class="menu-item ${isCurrentPage(currentPath, 'admin/settings.html') ? 'active' : ''}">
                            <i class="fas fa-cog"></i> ቅንብሮች
                        </a>
                        <a href="#" class="menu-item logout-button">
                            <i class="fas fa-sign-out-alt"></i> ይውጡ
                        </a>
                    </div>
                </div>
            `;
        } else if (user.role === 'teacher') {
            // Teacher navigation
            container.innerHTML = `
                <div class="sidebar">
                    <div class="sidebar-logo">
                        <img src="../img/logo.png" alt="Logo">
                        <h3>ኑር የትምህርት ማዕከል</h3>
                    </div>
                    <div class="user-profile">
                        <div class="user-avatar">
                            <img src="../img/avatars/teacher.png" alt="User">
                        </div>
                        <div class="user-name">${user.fullName || user.username}</div>
                        <div class="user-role">መምህር</div>
                    </div>
                    <div class="menu">
                        <div class="menu-label">ዋና</div>
                        <a href="../teacher/dashboard.html" class="menu-item ${isCurrentPage(currentPath, 'teacher/dashboard.html') ? 'active' : ''}">
                            <i class="fas fa-tachometer-alt"></i> ዳሽቦርድ
                        </a>
                        <div class="menu-label">ትምህርት</div>
                        <a href="../teacher/my-courses.html" class="menu-item ${isCurrentPage(currentPath, 'teacher/my-courses.html') ? 'active' : ''}">
                            <i class="fas fa-book"></i> ትምህርቶቼ
                        </a>
                        <a href="../teacher/assignments.html" class="menu-item ${isCurrentPage(currentPath, 'teacher/assignments.html') ? 'active' : ''}">
                            <i class="fas fa-tasks"></i> ምዘናዎች
                        </a>
                        <div class="menu-label">ተማሪዎች</div>
                        <a href="../teacher/students.html" class="menu-item ${isCurrentPage(currentPath, 'teacher/students.html') ? 'active' : ''}">
                            <i class="fas fa-user-graduate"></i> ተማሪዎቼ
                        </a>
                        <a href="../teacher/grades.html" class="menu-item ${isCurrentPage(currentPath, 'teacher/grades.html') ? 'active' : ''}">
                            <i class="fas fa-chart-bar"></i> ውጤቶች
                        </a>
                        <div class="menu-label">መገናኛዎች</div>
                        <a href="../teacher/messages.html" class="menu-item ${isCurrentPage(currentPath, 'teacher/messages.html') ? 'active' : ''}">
                            <i class="fas fa-envelope"></i> መልዕክቶች
                        </a>
                        <div class="menu-label">መለያ</div>
                        <a href="../teacher/profile.html" class="menu-item ${isCurrentPage(currentPath, 'teacher/profile.html') ? 'active' : ''}">
                            <i class="fas fa-user"></i> መገለጫ
                        </a>
                        <a href="#" class="menu-item logout-button">
                            <i class="fas fa-sign-out-alt"></i> ይውጡ
                        </a>
                    </div>
                </div>
            `;
        } else if (user.role === 'student') {
            // Student navigation
            container.innerHTML = `
                <div class="sidebar">
                    <div class="sidebar-logo">
                        <img src="../img/logo.png" alt="Logo">
                        <h3>ኑር የትምህርት ማዕከል</h3>
                    </div>
                    <div class="user-profile">
                        <div class="user-avatar">
                            <img src="../img/avatars/student.png" alt="User">
                        </div>
                        <div class="user-name">${user.fullName || user.username}</div>
                        <div class="user-role">ተማሪ - ${user.grade || ''}</div>
                    </div>
                    <div class="menu">
                        <div class="menu-label">ዋና</div>
                        <a href="../student/dashboard.html" class="menu-item ${isCurrentPage(currentPath, 'student/dashboard.html') ? 'active' : ''}">
                            <i class="fas fa-tachometer-alt"></i> ዳሽቦርድ
                        </a>
                        <div class="menu-label">ትምህርት</div>
                        <a href="../student/courses.html" class="menu-item ${isCurrentPage(currentPath, 'student/courses.html') ? 'active' : ''}">
                            <i class="fas fa-book"></i> ትምህርቶቼ
                        </a>
                        <a href="../student/assignments.html" class="menu-item ${isCurrentPage(currentPath, 'student/assignments.html') ? 'active' : ''}">
                            <i class="fas fa-tasks"></i> ምዘናዎች
                        </a>
                        <a href="../student/exams.html" class="menu-item ${isCurrentPage(currentPath, 'student/exams.html') ? 'active' : ''}">
                            <i class="fas fa-file-alt"></i> ፈተናዎች
                        </a>
                        <div class="menu-label">ከፍሎች</div>
                        <a href="../student/grades.html" class="menu-item ${isCurrentPage(currentPath, 'student/grades.html') ? 'active' : ''}">
                            <i class="fas fa-chart-bar"></i> ውጤቶቼ
                        </a>
                        <div class="menu-label">መገናኛዎች</div>
                        <a href="../student/messages.html" class="menu-item ${isCurrentPage(currentPath, 'student/messages.html') ? 'active' : ''}">
                            <i class="fas fa-envelope"></i> መልዕክቶች
                        </a>
                        <div class="menu-label">መለያ</div>
                        <a href="../student/profile.html" class="menu-item ${isCurrentPage(currentPath, 'student/profile.html') ? 'active' : ''}">
                            <i class="fas fa-user"></i> መገለጫ
                        </a>
                        <a href="#" class="menu-item logout-button">
                            <i class="fas fa-sign-out-alt"></i> ይውጡ
                        </a>
                    </div>
                </div>
            `;
        }
    }
    
    // Check if current page matches the given path
    function isCurrentPage(currentPath, pagePath) {
        return currentPath.endsWith(pagePath);
    }
    
    // Setup logout functionality
    function setupLogout() {
        const logoutButtons = document.querySelectorAll('.logout-button');
        logoutButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                // Clear auth data
                localStorage.removeItem('token');
                localStorage.removeItem('user');
                // Redirect to login
                window.location.href = '../login.html';
            });
        });
    }
    
    // Toggle responsive navigation
    function initResponsiveNav() {
        const mobileToggle = document.querySelector('.mobile-toggle');
        if (mobileToggle) {
            mobileToggle.addEventListener('click', function() {
                const navbarMenu = document.querySelector('.navbar-menu');
                mobileToggle.classList.toggle('active');
                navbarMenu.classList.toggle('active');
            });
        }
        
        // Toggle sidebar
        const toggleSidebar = document.querySelector('.toggle-sidebar');
        if (toggleSidebar) {
            toggleSidebar.addEventListener('click', function() {
                document.querySelector('.wrapper').classList.toggle('sidebar-collapsed');
            });
        }
    }
    
    // Check if page requires authentication
    function isProtectedPage(path) {
        return path.includes('/admin/') || 
               path.includes('/teacher/') || 
               path.includes('/student/');
    }
    
    // Check if user has access to the page based on role
    function isRoleRestrictedPage(path, role) {
        if (path.includes('/admin/') && role !== 'admin') {
            return true;
        }
        
        if (path.includes('/teacher/') && role !== 'teacher' && role !== 'admin') {
            return true;
        }
        
        if (path.includes('/student/') && role !== 'student' && role !== 'admin') {
            return true;
        }
        
        return false;
    }
    
    // Get home page based on user role
    function getRoleHomePage(role) {
        switch(role) {
            case 'admin':
                return '../admin/dashboard.html';
            case 'teacher':
                return '../teacher/dashboard.html';
            case 'student':
                return '../student/dashboard.html';
            default:
                return '../index.html';
        }
    }
}); 

// Simple navigation functions for the educational center application

// Function to check if user is logged in
function isLoggedIn() {
    return localStorage.getItem('token') !== null;
}

// Function to log out user
function logout() {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    window.location.href = '/login.html';
}

// Redirect to login if not authenticated
function requireAuth() {
    if (!isLoggedIn()) {
        console.log('Auth required but user not logged in, redirecting to login');
        window.location.href = '/login.html';
        return false;
    }
    return true;
}

// Get current user details
function getCurrentUser() {
    try {
        const userJson = localStorage.getItem('user');
        if (userJson) {
            return JSON.parse(userJson);
        }
    } catch (error) {
        console.error('Error parsing user data:', error);
    }
    return null;
}

// Helper function to navigate to dashboard based on user role and grade
function navigateToDashboard() {
    const user = getCurrentUser();
    if (!user) {
        window.location.href = '/login.html';
        return;
    }
    
    // Extract grade (remove 'Grade ' prefix if present)
    let userGrade = user.grade || '9';
    if (typeof userGrade === 'string' && userGrade.includes('Grade ')) {
        userGrade = userGrade.replace('Grade ', '');
    }
    
    // Redirect using redirect.html
    const baseUrl = window.location.origin;
    window.location.href = `${baseUrl}/redirect.html?role=${user.role}&grade=${userGrade}`;
}

// Add event listeners once DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Handle logout button clicks
    const logoutButtons = document.querySelectorAll('.logout-btn');
    if (logoutButtons) {
        logoutButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                logout();
            });
        });
    }
    
    // Check for login status on protected pages
    const isProtectedPage = !window.location.pathname.includes('login.html') && 
                           !window.location.pathname.includes('index.html');
    
    if (isProtectedPage) {
        requireAuth();
    }

    console.log('Navigation.js loaded successfully');
}); 