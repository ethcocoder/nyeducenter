<?php
require_once '../includes/functions.php';
require_login();

// Get current tab from URL parameter
$current_tab = $_GET['tab'] ?? 'recent';

// Get user data
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Get recent activity
$recent_activity = get_recent_activity($user_id, 10);

// Get grade categories
$grade_categories = get_grade_categories();

// Get user folders
$user_folders = get_user_folders($user_id);

// Get user statistics
$user_stats = get_user_statistics($user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo APP_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --light-bg: #ecf0f1;
            --dark-text: #2c3e50;
            --highlight-color: #fcf8e3;
            --text-light: #ecf0f1;
            --text-muted: #7f8c8d;
            --gradient-primary: linear-gradient(135deg, #2c3e50, #34495e);
            --gradient-secondary: linear-gradient(135deg, #3498db, #2980b9);
            --gradient-accent: linear-gradient(135deg, #e74c3c, #c0392b);
            --shadow-light: 0 2px 8px rgba(0, 0, 0, 0.1);
            --shadow-medium: 0 5px 15px rgba(0, 0, 0, 0.15);
            --shadow-heavy: 0 10px 30px rgba(0, 0, 0, 0.2);
            --bottom-nav-height: 60px;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--light-bg);
            color: var(--dark-text);
            padding-bottom: var(--bottom-nav-height);
            min-height: 100vh;
            box-sizing: border-box; /* Added for consistent box model */
        }

        .navbar-custom {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-bottom: none;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: white;
        }

        .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.7);
            font-weight: 500;
            transition: all 0.3s ease;
            border-radius: 10px;
            padding: 0.5rem 1rem;
            margin: 0 0.25rem;
        }

        .navbar-nav .nav-link:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-1px);
        }

        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.1);
            z-index: 1030;
            height: var(--bottom-nav-height);
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }

        .bottom-nav .nav-item {
            flex: 1;
            text-align: center;
        }

        .bottom-nav .nav-link {
            color: var(--dark-text);
            padding: 10px 5px;
            border-radius: 10px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100%;
            justify-content: center;
            position: relative;
        }

        .bottom-nav .nav-link:hover,
        .bottom-nav .nav-link.active {
            color: var(--primary-color);
            background: rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
        }

        .bottom-nav .nav-link.active::before {
            content: '';
            position: absolute;
            top: -2px;
            left: 50%;
            transform: translateX(-50%);
            width: 30px;
            height: 3px;
            background: var(--primary-color);
            border-radius: 2px;
        }

        .bottom-nav .nav-link i {
            font-size: 1.5rem;
            margin-bottom: 5px;
            transition: all 0.3s ease;
        }

        .bottom-nav .nav-link.active i {
            color: var(--primary-color);
            -webkit-background-clip: unset;
            -webkit-text-fill-color: unset;
            background-clip: unset;
        }

        .bottom-nav .nav-link span {
            font-size: 0.75rem;
            font-weight: 600;
        }

        .content-area {
            min-height: calc(100vh - var(--bottom-nav-height) - 76px);
            padding: 2rem 0;
        }

        .card-custom {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .card-custom:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .card-custom .card-body {
            padding: 2rem;
        }

        .card-custom .card-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .book-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            box-shadow: var(--shadow-light);
            transition: all 0.3s ease;
        }

        .book-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-medium);
        }

        .book-cover {
            width: 60px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin-right: 1rem;
            flex-shrink: 0;
            box-shadow: var(--shadow-light);
        }

        .book-card:hover .book-cover {
            transform: scale(1.05);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
        }

        .book-info h6 {
            font-weight: 600;
            color: var(--dark-text);
            margin-bottom: 0.25rem;
        }

        .book-info p {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-bottom: 0.25rem;
        }

        .folder-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            box-shadow: var(--shadow-light);
            transition: all 0.3s ease;
        }

        .folder-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-medium);
        }

        .folder-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-right: 1rem;
            flex-shrink: 0;
        }

        .folder-card:hover .folder-icon {
            transform: scale(1.1);
            color: #e67e22;
        }

        .folder-info h6 {
            font-weight: 600;
            color: var(--dark-text);
            margin-bottom: 0.25rem;
        }

        .folder-info p {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-bottom: 0.25rem;
        }

        .btn-fab {
            position: fixed;
            bottom: calc(var(--bottom-nav-height) + 20px);
            right: 20px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            box-shadow: var(--shadow-medium);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .btn-fab:hover {
            background: var(--secondary-color);
            box-shadow: var(--shadow-heavy);
            transform: translateY(-3px);
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--dark-text);
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.7;
            color: var(--secondary-color);
        }

        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            border: 1px solid rgba(0, 0, 0, 0.1);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-2px);
            background: #f8f9fa;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }

        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .stats-label {
            color: var(--dark-text);
            font-size: 0.9rem;
            margin: 0;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, var(--success-color), #2ecc71);
            border: none;
            color: white;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            color: white;
        }

        .btn-outline-custom {
            border: 2px solid var(--secondary-color);
            color: var(--secondary-color);
            background: transparent;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .btn-outline-custom:hover {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
            color: white;
            transform: translateY(-2px);
        }

        .footer-custom {
            background: var(--primary-color);
            color: white;
            text-align: center;
            padding: 1rem;
            margin-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        @media (max-width: 768px) {
            .content-area {
                padding: 1rem 0;
            }
            
            .card-custom {
                margin-bottom: 1rem;
            }
            
            .bottom-nav .nav-link i {
                font-size: 1.3rem;
            }
            
            .bottom-nav .nav-link span {
                font-size: 0.7rem;
            }

            .hero-stats {
                margin-top: 2rem;
            }

            .stats-card {
                margin-bottom: 1rem;
            }
        }

        /* Animation enhancements */
        .card-custom,
        .book-card,
        .folder-card {
            will-change: transform;
        }

        /* Reduce motion for accessibility */
        @media (prefers-reduced-motion: reduce) {
            .card-custom,
            .book-card,
            .folder-card,
            .btn-fab,
            .bottom-nav .nav-link {
                transition: none !important;
                animation: none !important;
            }
        }
    </style>
</head>
<body>
    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="#">
            <img src="../assets/images/logo.svg" alt="Logo" style="height: 40px; margin-right: 10px;">
            <?php echo APP_NAME; ?>
        </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="profile.php">
                    <i class="fas fa-user me-1"></i>
                    <?php echo htmlspecialchars($username); ?>
                </a>
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt me-1"></i>
                    Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <div class="container content-area">
        <?php if ($current_tab === 'recent'): ?>
            <!-- Recent Activity Tab -->
            <div class="card-custom">
                <div class="card-body">
                    <h4 class="card-title mb-4">
                        <i class="fas fa-history me-2"></i>
                        Recent Activity
                    </h4>
                    
                    <?php if (empty($recent_activity)): ?>
                        <div class="empty-state">
                            <i class="fas fa-book-open"></i>
                            <h5>No Recent Activity</h5>
                            <p>Start reading books to see them here!</p>
                        </div>
                            <div id="usersPagination" class="mt-3"></div>
                    <?php else: ?>
                        <?php foreach ($recent_activity as $activity): ?>
                            <?php 
                            $book_title = $activity['system_book_title'] ?? $activity['user_book_title'] ?? 'Unknown Book';
                            $book_path = $activity['system_book_path'] ?? $activity['user_book_path'] ?? '';
                            $book_type = $activity['book_type'];
                            $book_id = $activity['book_id'];
                            $progress = get_book_reading_progress($user_id, $book_id, $book_type);
                            // Underline the progress bar when reading is in progress
                            if ($progress && $progress['total_pages'] > 0 && $progress['current_page'] > 0) {
                                $progress_style = 'style="text-decoration: underline;"';
                            } else {
                                $progress_style = '';
                            }
                            ?>
                            <div class="book-card d-flex align-items-center" 
                                 onclick="openBook('<?php echo $book_type; ?>', <?php echo $book_id; ?>, '<?php echo htmlspecialchars($book_path); ?>')">
                                <div class="book-cover">
                                    <i class="fas fa-file-pdf"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($book_title); ?></h6>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        <?php echo format_date($activity['opened_at']); ?>
                                    </small>
                                    <?php if ($progress && $progress['total_pages'] > 0): ?>
                                        <div class="reading-progress mt-2">
                                            <div class="progress" style="height: 4px;">
                                                <div class="progress-bar" style="width: <?php echo min(100, round(($progress['current_page'] / $progress['total_pages']) * 100)); ?>%"></div>
                                            </div>
                                            <small class="text-muted"><?php echo $progress['current_page']; ?> / <?php echo $progress['total_pages']; ?> pages</small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="text-end">
                                    <i class="fas fa-chevron-right text-muted"></i>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif ($current_tab === 'books'): ?>
            <!-- Books Tab -->
            <div class="row">
                <div class="col-lg-6">
                    <div class="card-custom">
                        <div class="card-body">
                            <h5 class="card-title mb-4">
                                <i class="fas fa-layer-group me-2"></i>
                                Grade Categories
                            </h5>
                            
                            <div class="row">
                                <?php foreach ($grade_categories as $category): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="folder-card" 
                                             onclick="viewGradeBooks(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['grade_name']); ?>')">
                                            <div class="folder-icon">
                                                <i class="fas fa-folder"></i>
                                            </div>
                                            <h6><?php echo htmlspecialchars($category['grade_name']); ?></h6>
                                            <small class="text-muted">System Books</small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="card-custom">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-folder-open me-2"></i>
                                    My Books
                                </h5>
                                <button class="btn btn-sm btn-success" onclick="createFolder()">
                                    <i class="fas fa-plus me-1"></i>
                                    New Folder
                                </button>
                            </div>
                            
                            <?php if (empty($user_folders)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-folder-open"></i>
                                    <h6>No Custom Folders</h6>
                                    <p>Create your first folder to organize your books!</p>
                                </div>
                            <?php else: ?>
                                <div class="row">
                                    <?php foreach ($user_folders as $folder): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="folder-card" 
                                                 onclick="viewFolderBooks(<?php echo $folder['id']; ?>, '<?php echo htmlspecialchars($folder['folder_name']); ?>')"
                                                 oncontextmenu="showFolderOptions(<?php echo $folder['id']; ?>, '<?php echo htmlspecialchars($folder['folder_name']); ?>'); return false;">
                                                <div class="folder-icon">
                                                    <i class="fas fa-folder-open"></i>
                                                </div>
                                                <h6><?php echo htmlspecialchars($folder['folder_name']); ?></h6>
                                                <small class="text-muted">Custom Folder</small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($current_tab === 'account'): ?>
            <!-- Account Tab -->
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card-custom">
                        <div class="card-body text-center">
                            <div class="mb-4">
                                <div class="rounded-circle d-inline-flex align-items-center justify-content-center" 
                                     style="width: 100px; height: 100px; background: var(--gradient-primary);">
                                    <i class="fas fa-user text-white" style="font-size: 3rem;"></i>
                                </div>
                            </div>
                            <h4><?php echo htmlspecialchars($username); ?></h4>
                            <p class="text-muted mb-4">Library Member</p>
                            
                            <?php 
                            $reading_stats = get_user_reading_stats($user_id);
                            ?>
                            
                            <div class="row text-center mb-4">
                                <div class="col-3">
                                    <div class="stats-card">
                                        <div class="stats-number"><?php echo count($user_folders); ?></div>
                                        <div class="stats-label">Folders</div>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="stats-card">
                                        <div class="stats-number"><?php echo count($recent_activity); ?></div>
                                        <div class="stats-label">Recent</div>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="stats-card">
                                        <div class="stats-number"><?php echo $reading_stats['books_read']; ?></div>
                                        <div class="stats-label">Books Read</div>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="stats-card">
                                        <div class="stats-number"><?php echo $reading_stats['total_bookmarks']; ?></div>
                                        <div class="stats-label">Bookmarks</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="stats-card">
                                        <div class="stats-number"><?php echo $reading_stats['pages_read']; ?></div>
                                        <div class="stats-label">Pages Read</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="stats-card">
                                        <div class="stats-number"><?php echo count($grade_categories); ?></div>
                                        <div class="stats-label">Grades Access</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <a href="profile.php" class="btn btn-primary-custom">
                                    <i class="fas fa-user-edit me-2"></i>
                                    Edit Profile
                                </a>
                                <button class="btn btn-outline-custom mb-2" onclick="exportReadingData()">
                                    <i class="fas fa-download me-2"></i> Export Reading Data
                                </button>
                                <a href="logout.php" class="btn btn-outline-custom">
                                    <i class="fas fa-sign-out-alt me-2"></i>
                                    Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Floating Action Button for Mobile -->
    <?php if ($current_tab === 'books'): ?>
        <button class="btn btn-fab" onclick="createFolder()">
            <i class="fas fa-plus"></i>
        </button>
    <?php endif; ?>

    <!-- Bottom Navigation -->
    <nav class="bottom-nav">
        <div class="container-fluid">
            <div class="row h-100">
                <div class="col nav-item">
                    <a class="nav-link <?php echo $current_tab === 'recent' ? 'active' : ''; ?>" 
                       href="?tab=recent">
                        <i class="fas fa-history"></i>
                        <span>Recent</span>
                    </a>
                </div>
                <div class="col nav-item">
                    <a class="nav-link <?php echo $current_tab === 'books' ? 'active' : ''; ?>" 
                       href="?tab=books">
                        <i class="fas fa-book"></i>
                        <span>Books</span>
                    </a>
                </div>
                <div class="col nav-item">
                    <a class="nav-link <?php echo $current_tab === 'account' ? 'active' : ''; ?>" 
                       href="?tab=account">
                        <i class="fas fa-user"></i>
                        <span>Account</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Footer -->
    <footer class="footer-custom">
        <div class="container">
            <p class="mb-0">
                <i class="fas fa-heart text-danger"></i>
                Powered by Ethco Coders 2025
            </p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        function openBook(bookType, bookId, filePath) {
            if (!filePath) {
                alert('Book file not found');
                return;
            }
            
            // Add to recent activity via AJAX
            fetch('../api/add-recent-activity.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    book_id: bookId,
                    book_type: bookType
                })
            });
            
            // Open PDF viewer with additional parameters for enhanced experience
            const viewerUrl = `pdf-viewer.php?type=${bookType}&id=${bookId}&file=${encodeURIComponent(filePath)}&enhanced=1`;
            window.open(viewerUrl, '_blank');
        }

        // Export reading data function
        function exportReadingData() {
            // Create a simple data export
            fetch('../api/get-reading-data.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Create and download JSON file
                        const jsonData = JSON.stringify(data.reading_data, null, 2);
                        const blob = new Blob([jsonData], { type: 'application/json' });
                        const url = URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = `reading-data-${new Date().toISOString().split('T')[0]}.json`;
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        URL.revokeObjectURL(url);
                    } else {
                        alert('Failed to export reading data');
                    }
                })
                .catch(error => {
                    console.error('Export error:', error);
                    alert('Error exporting reading data');
                });
        }

        function viewGradeBooks(gradeId, gradeName) {
            window.location.href = 'grade-books.php?grade=' + gradeId + '&name=' + encodeURIComponent(gradeName);
        }

        function viewFolderBooks(folderId, folderName) {
            window.location.href = 'folder-books.php?folder=' + folderId + '&name=' + encodeURIComponent(folderName);
        }

        function createFolder() {
            const folderName = prompt('Enter folder name:');
            if (folderName && folderName.trim()) {
                // Create folder via AJAX - send as FormData to match PHP expectation
                const formData = new FormData();
                formData.append('folder_name', folderName.trim());
                
                fetch('../api/create-folder.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Failed to create folder');
                    }
                })
                .catch(error => {
                    console.error('Error creating folder:', error);
                    alert('Error creating folder');
                });
            }
        }

        function showFolderOptions(folderId, folderName) {
            if (confirm('Delete folder "' + folderName + '"? This will also delete all books in the folder.')) {
                // Delete folder via AJAX
                fetch('../api/delete-folder.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        folder_id: folderId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Failed to delete folder');
                    }
                })
                .catch(error => {
                    alert('Error deleting folder');
                });
            }
        }

        // Add touch gesture support for mobile
        let longPressTimer;
        document.querySelectorAll('.folder-card').forEach(card => {
            card.addEventListener('touchstart', function(e) {
                longPressTimer = setTimeout(() => {
                    this.dispatchEvent(new Event('contextmenu'));
                }, 800);
            });
            
            card.addEventListener('touchend', function() {
                clearTimeout(longPressTimer);
            });
            
            card.addEventListener('touchmove', function() {
                clearTimeout(longPressTimer);
            });
        });
    </script>
    <script>

        // Handle Backup Database button click
document.getElementById('backupDatabaseBtn').addEventListener('click', function () {
    Swal.fire({
        title: 'Are you sure?',
        text: "Do you want to backup the database?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, backup it!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('../api/admin-backup-database.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Success', data.message, 'success');
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error backing up database:', error);
                    Swal.fire('Error', 'Failed to backup database.', 'error');
                });
        }
    });
});

    // Function to load system settings into the modal
    function loadSystemSettings() {
        fetch('../api/admin-get-settings.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('siteName').value = data.settings.site_name;
                    document.getElementById('siteDescription').value = data.settings.site_description;
                    document.getElementById('adminEmail').value = data.settings.admin_email;
                    document.getElementById('itemsPerPage').value = data.settings.items_per_page;
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error fetching system settings:', error);
                Swal.fire('Error', 'Failed to fetch system settings.', 'error');
            });
    }

    // Event listener for opening the System Settings modal
    document.getElementById('systemSettingsModal').addEventListener('show.bs.modal', function () {
        loadSystemSettings();
    });

    // Handle System Settings form submission
    document.getElementById('systemSettingsForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const jsonData = {};
        formData.forEach((value, key) => {
            jsonData[key] = value;
        });

        fetch('../api/admin-update-settings.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(jsonData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire('Success', data.message, 'success');
                bootstrap.Modal.getInstance(document.getElementById('systemSettingsModal')).hide();
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error updating system settings:', error);
            Swal.fire('Error', 'Failed to update system settings.', 'error');
        });
    });

    </script>
</body>
</html>


</body>
</html>

</body>
</html>