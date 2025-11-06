<?php
require_once '../../includes/functions.php';

// Check if user is admin
if (!is_admin()) {
    header('Location: ../dashboard.php');
    exit;
}

// Get statistics
$stats = get_admin_stats();
?>
<?php include 'partials/head.php'; ?>

    <style>
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #50e3c2;
            --accent-color: #f5a623;
            --dark-bg: #f4f7f6;
            --light-bg: #ffffff;
            --dark-text: #333333;
            --light-text: #ffffff;
            --border-color: rgba(0, 0, 0, 0.1);
            --card-bg: #ffffff;
            --header-bg: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            --button-hover-bg: linear-gradient(to right, #3a7bd5, #42b883);
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --highlight-color: #e0f7fa;
            --text-light: #f8f9fa;
            --text-muted: #6c757d;
            --gradient-primary: linear-gradient(45deg, #4a90e2, #50e3c2);
            --gradient-secondary: linear-gradient(45deg, #f5a623, #f76b1c);
            --gradient-accent: linear-gradient(45deg, #9b59b6, #8e44ad);
            --shadow-light: 0 2px 8px rgba(0, 0, 0, 0.05);
            --shadow-medium: 0 5px 15px rgba(0, 0, 0, 0.1);
            --shadow-heavy: 0 10px 30px rgba(0, 0, 0, 0.15);
            --bottom-nav-height: 60px;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--dark-bg);
            color: var(--dark-text);
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        .main-content {
            padding: 2rem;
            background-color: var(--dark-bg);
            min-height: 100vh;
        }
        .navbar-custom {
            background: var(--header-bg);
            color: var(--light-text);
            padding: 1rem 0;
            border-bottom: none;
            box-shadow: var(--shadow-medium);
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 1.75rem;
            color: white;
        }

        .card {
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            box-shadow: var(--shadow-light);
            transition: all 0.3s ease;
            background-color: var(--card-bg);
        }

        .card:hover {
            box-shadow: var(--shadow-medium);
            transform: translateY(-2px);
        }
        .card-header {
            background: var(--gradient-primary);
            color: white;
            font-weight: 600;
            border-bottom: none;
            border-top-left-radius: 0.75rem;
            border-top-right-radius: 0.75rem;
            padding: 1rem 1.5rem;
        }

        .stat-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            box-shadow: var(--shadow-light);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-medium);
        }
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: white;
            margin-right: 1rem;
            flex-shrink: 0;
        }

        .stat-icon.bg-primary {
            background: var(--gradient-primary);
        }

        .stat-icon.bg-success {
            background: linear-gradient(45deg, #2ecc71, #27ae60);
        }

        .stat-icon.bg-warning {
            background: var(--gradient-secondary);
        }

        .stat-icon.bg-danger {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-text);
            line-height: 1;
        }

        .stat-label {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-top: 0.25rem;
        }
        .table {
            --bs-table-bg: var(--card-bg);
            --bs-table-color: var(--dark-text);
            --bs-table-border-color: var(--border-color);
        }

        .table thead {
            background-color: var(--highlight-color);
        }

        .table th {
            color: var(--primary-color);
            font-weight: 600;
        }

        .table-striped > tbody > tr:nth-of-type(odd) > * {
            background-color: var(--highlight-color);
        }
        .btn-primary {
            background: var(--gradient-primary);
            border: none;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: var(--button-hover-bg);
            box-shadow: var(--shadow-light);
            transform: translateY(-1px);
        }
        .btn-danger {
            background-color: var(--danger-color);
            border: none;
            transition: all 0.3s ease;
        }

        .btn-danger:hover {
            background-color: #c0392b;
            box-shadow: var(--shadow-light);
            transform: translateY(-1px);
        }
        .action-buttons .btn {
            margin-right: 0.5rem;
            border-radius: 0.5rem;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
        }
        .badge {
            padding: 0.5em 0.75em;
            border-radius: 0.5rem;
            font-weight: 600;
        }

        .badge.bg-primary {
            background: var(--gradient-primary) !important;
        }

        .badge.bg-success {
            background-color: var(--success-color) !important;
        }

        .badge.bg-warning {
            background-color: var(--warning-color) !important;
        }

        .badge.bg-danger {
            background-color: var(--danger-color) !important;
        }
        .modal-content {
            border-radius: 0.75rem;
            border: none;
            box-shadow: var(--shadow-heavy);
            background-color: var(--card-bg);
            color: var(--dark-text);
        }
        .form-control {
            border-radius: 0.5rem;
            border: 1px solid var(--border-color);
            padding: 0.75rem 1rem;
            background-color: var(--light-bg);
            color: var(--dark-text);
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(74, 144, 226, 0.25);
            background-color: var(--light-bg);
            color: var(--dark-text);
        }
        .form-label {
            font-weight: 600;
            color: var(--dark-text);
        }
        .pagination .page-item .page-link {
            color: var(--primary-color);
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            margin: 0 0.25rem;
            transition: all 0.3s ease;
        }

        .pagination .page-item.active .page-link {
            background: var(--gradient-primary);
            border-color: var(--primary-color);
            color: var(--light-text);
            box-shadow: var(--shadow-light);
        }

        .pagination .page-item .page-link:hover {
            background-color: var(--highlight-color);
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .footer {
            background-color: var(--card-bg);
            color: var(--text-muted);
            padding: 1.5rem;
            border-top: 1px solid var(--border-color);
            box-shadow: var(--shadow-light);
            text-align: center;
            font-size: 0.9rem;
        }
    </style>

<body>
    <div id="layoutSidenav">
        <header class="sidebar-header">
            <button class="btn btn-primary" id="sidebarToggle"><i class="fas fa-bars"></i></button>
        </header>
        <div id="layoutSidenav_nav">
            <?php include __DIR__ . '/partials/sidebar_navigation.php'; ?>
        </div>
        <main id="layoutSidenav_content">
            <div class="main-content">
                <div class="container-fluid px-4">
                    <header class="page-header">
                        <h1 class="mt-4">Dashboard</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>
                    </header>

                    <?php include 'partials/dashboard_section.php'; ?>
                    <?php include 'partials/users_section.php'; ?>
                    <?php include 'partials/categories_section.php'; ?>
                    <?php include 'partials/books_section.php'; ?>
                    <?php include 'partials/activity_section.php'; ?>
                    <?php include 'partials/settings_section.php'; ?>

                </div>
            </div>
            <?php include 'partials/modals.php'; ?>
        </main>
    </div>

    <!-- Bootstrap JS -->

     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script src="../../assets/js/admin_dashboard.js"></script>
    <script src="../../assets/js/user_management.js"></script>
    <script src="../../assets/js/category_management.js"></script>
    <script src="../../assets/js/book_management.js"></script>
    <script src="../../assets/js/system_settings_management.js"></script>
    <script src="../../assets/js/theme-toggle.js"></script>
    <script>
        // Navigation functionality
        document.addEventListener('DOMContentLoaded', function() {
            function activateSectionFromHash() {
                const hash = window.location.hash.substring(1); // Remove '#'
                const defaultSection = 'dashboard';
                const sectionToActivate = hash || defaultSection;

                // Remove active class from all links and hide all sections
                document.querySelectorAll('.sidebar .nav-link').forEach(l => l.classList.remove('active'));
                document.querySelectorAll('.content-section').forEach(section => {
                    section.style.display = 'none';
                });

                // Activate the corresponding sidebar link
                const activeLink = document.querySelector(`.sidebar .nav-link[data-section="${sectionToActivate}"]`);
                if (activeLink) {
                    activeLink.classList.add('active');
                }

                // Show selected section
                const targetSection = document.getElementById(sectionToActivate + '-section');
                if (targetSection) {
                    targetSection.style.display = 'block';
                    loadSectionData(sectionToActivate);
                } else {
                    // Fallback to dashboard if hash section not found
                    document.querySelector(`.sidebar .nav-link[data-section="${defaultSection}"]`).classList.add('active');
                    document.getElementById(defaultSection + '-section').style.display = 'block';
                    loadSectionData(defaultSection);
                }
            }

            // Activate section on initial load
            activateSectionFromHash();

            // Activate section when hash changes
            window.addEventListener('hashchange', activateSectionFromHash);

            document.querySelectorAll('.sidebar .nav-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    // Only prevent default and handle as section if data-section attribute is present
                    if (this.hasAttribute('data-section')) {
                        e.preventDefault();
                        const sectionName = this.getAttribute('data-section');
                        window.location.hash = sectionName; // Update URL hash
                    }
                });
            });

        // Load section data
        function loadSectionData(section) {
            switch(section) {
                case 'users':
                    loadUsers();
                    break;
                case 'categories':
                    loadCategories();
                    break;
                case 'books':
                    loadBooks();
                    break;
                case 'activity':
                    loadActivity();
                    break;
            }
        }

        // Load users
        function loadUsers() {
            fetch('../../api/admin-get-users.php')
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('usersTableBody');
                    tbody.innerHTML = '';
                    
                    data.users.forEach(user => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${user.id}</td>
                            <td>${user.username}</td>
                            <td>${user.email}</td>
                            <td>
                                <span class="badge ${user.role === 'admin' ? 'bg-danger' : 'bg-primary'}">
                                    ${user.role}
                                </span>
                            </td>
                            <td>${new Date(user.created_at).toLocaleDateString()}</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-sm btn-primary edit-user-btn" data-id="${user.id}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteUser(${user.id})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        `;
                        tbody.appendChild(row);
                    });
                })
                .catch(error => {
                    console.error('Error loading users:', error);
                });
        }

        // Load categories
        function loadCategories() {
            fetch('../../api/admin-get-categories.php')
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('categoriesTableBody');
                    tbody.innerHTML = '';
                    
                    if (data.success && data.categories) {
                        data.categories.forEach(category => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${category.id}</td>
                                <td>${category.grade_name}</td>
                                <td>${category.description || 'N/A'}</td>
                                <td>${category.book_count}</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-sm btn-primary edit-category-btn" data-id="${category.id}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteCategory(${category.id})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            `;
                            tbody.appendChild(row);
                        });
                    } else {
                        console.error('API returned an error or no categories:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error loading categories:', error);
                });
        }

        // Load books
        function loadBooks() {
            fetch('../../api/admin-get-books.php')
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('booksTableBody');
                    tbody.innerHTML = '';
                    
                    data.books.forEach(book => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${book.id}</td>
                            <td>${book.title}</td>
                            <td>${book.author || 'N/A'}</td>
                            <td>${book.category_name}</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-sm btn-primary" onclick="editBook(${book.id})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteBook(${book.id})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        `;
                        tbody.appendChild(row);
                    });
                })
                .catch(error => {
                    console.error('Error loading books:', error);
                });
        }

        
        function loadActivity() {
            fetch('../../api/admin-get-activity.php')
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('activityTableBody');
                    tbody.innerHTML = '';
                    
                    data.activities.forEach(activity => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${activity.id}</td>
                            <td>${activity.username}</td>
                            <td>${activity.book_title}</td>
                            <td>
                                <span class="badge ${activity.book_type === 'system' ? 'bg-primary' : 'bg-success'}">
                                    ${activity.book_type}
                                </span>
                            </td>
                            <td>${new Date(activity.opened_at).toLocaleString()}</td>
                        `;
                        tbody.appendChild(row);
                    });
                })
                .catch(error => {
                    console.error('Error loading activity:', error);
                });
        }
    }); // Closing tag for DOMContentLoaded
  </script>
</body>
</html>