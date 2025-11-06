<?php
require_once '../includes/functions.php';
require_login();

$book_type = $_GET['type'] ?? '';
$book_id = $_GET['id'] ?? 0;
$file_path = urldecode($_GET['file'] ?? '');

// Validate book access
if (!$book_type || !$book_id || !$file_path) {
    header('Location: dashboard.php');
    exit;
}

// Determine the full URL for the PDF file
$pdf_url = '';
if (filter_var($file_path, FILTER_VALIDATE_URL)) {
    // If it's already a full URL, use it directly
    $pdf_url = $file_path;
} else {
    // Otherwise, prepend APP_URL for local files
    $pdf_url = APP_URL . $file_path;
}

// For local files, verify existence and within allowed directories
if (!filter_var($file_path, FILTER_VALIDATE_URL)) {
        $full_path = realpath('../' . $file_path);
        $uploads_dir = realpath('../assets/uploads/');

        if (!$full_path || !str_starts_with($full_path, $uploads_dir)) {
            die('Invalid file path');
        }

        if (!file_exists($full_path)) {
            die('File not found');
        }
    }

// Get book details
$book_title = 'Unknown Book';
if ($book_type === 'system') {
    $book = get_system_book_by_id($book_id);
    $book_title = $book['title'] ?? 'Unknown Book';
} elseif ($book_type === 'user') {
    $book = get_user_book_by_id($book_id);
    $book_title = $book['book_name'] ?? 'Unknown Book';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($book_title); ?> - PDF Viewer</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- PDF.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <!-- Google Fonts - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">



    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // PDF.js configuration
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        // ... rest of your JavaScript code ...
    </script>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // PDF.js configuration
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        // ... rest of your JavaScript code ...
    </script>



    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #e74c3c;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --light-bg: #ecf0f1;
            --dark-text: #2c3e50;
            --highlight-color: #3498db;
            --text-light: #ecf0f1;
            --text-muted: #bdc3c7;
            --gradient-primary: linear-gradient(135deg, #2c3e50, #34495e);
            --gradient-secondary: linear-gradient(135deg, #34495e, #2c3e50);
            --gradient-accent: linear-gradient(135deg, #e74c3c, #c0392b);
            --shadow-light: 0 4px 10px rgba(0, 0, 0, 0.05);
            --shadow-medium: 0 8px 25px rgba(0, 0, 0, 0.1);
            --shadow-heavy: 0 15px 35px rgba(0, 0, 0, 0.15);
            --bottom-nav-height: 60px;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--light-bg);
            color: var(--dark-text);
            margin: 0;
            padding: 0;
            overflow: hidden;
        }

        .viewer-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .viewer-header {
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            color: white;
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            z-index: 1000;
        }

        .book-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .book-cover-small {
            width: 45px;
            height: 60px;
            background: linear-gradient(135deg, var(--warning-color), #f1c40f);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.3rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .book-cover-small:hover {
            transform: scale(1.05);
        }

        .book-info h5 {
            margin: 0;
            font-weight: 600;
            font-size: 1.1rem;
            color: white;
        }

        .book-info small {
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        .viewer-controls {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: white;
            padding: 0.75rem 1.5rem;
            box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.05);
            justify-content: center;
            z-index: 1000;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }

        .viewer-controls button {
            background: var(--primary-color);
            border: none;
            color: white;
            padding: 0.75rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 45px;
            height: 45px;
        }

        .viewer-controls button:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .viewer-controls button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .page-info {
            color: var(--dark-text);
            font-weight: 500;
            background: #f0f0f0;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .viewer-content {
            flex: 1;
            overflow: auto;
            background-color: var(--light-bg);
            position: relative;
            padding: 2rem;
        }

        .pdf-canvas {
            display: block;
            margin: 2rem auto;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .pdf-canvas:hover {
            transform: scale(1.01);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .zoom-controls {
            position: fixed;
            bottom: 30px;
            right: 30px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            z-index: 100;
        }

        .zoom-btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--gradient-accent);
            color: white;
            border: none;
            font-size: 1.3rem;
            cursor: pointer;
            box-shadow: var(--shadow-heavy);
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .zoom-btn:hover {
            transform: scale(1.1) translateY(-5px);
            box-shadow: var(--shadow-heavy);
        }

        .page-thumbnails {
            position: fixed;
            left: 0;
            top: 80px;
            bottom: 0;
            width: 220px;
            background: rgba(26, 26, 46, 0.95);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            overflow-y: auto;
            transform: translateX(-220px);
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 50;
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow-medium);
        }

        .page-thumbnails.open {
            transform: translateX(0);
        }

        .thumbnail {
            padding: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            cursor: pointer;
            transition: all 0.3s ease;
            border-radius: 12px;
            margin: 8px;
            backdrop-filter: blur(10px);
        }

        .thumbnail:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }

        .thumbnail.active {
            background: var(--gradient-accent);
            color: white;
            box-shadow: var(--shadow-light);
        }

        .thumbnail canvas {
            width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: var(--shadow-light);
        }

        .viewer-content.with-thumbnails {
            margin-left: 220px;
            transition: margin-left 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .error-message {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-muted);
            background: rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            max-width: 500px;
            margin: 0 auto;
        }

        .error-message i {
            font-size: 5rem;
            margin-bottom: 2rem;
            background: var(--gradient-secondary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .error-message h5 {
            color: var(--text-light);
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .progress-bar {
            position: absolute;
            top: 0;
            left: 0;
            height: 3px;
            background: var(--gradient-accent);
            transition: width 0.3s ease;
            z-index: 1001;
            border-radius: 0 0 2px 2px;
        }

        .reading-progress {
            position: absolute;
            bottom: 20px;
            left: 30px;
            background: rgba(26, 26, 46, 0.95);
            color: var(--text-light);
            padding: 1rem 1.5rem;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: var(--shadow-medium);
            font-weight: 500;
        }

        .reading-progress .progress {
            height: 6px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
            overflow: hidden;
            margin-top: 0.5rem;
        }

        .reading-progress .progress-bar {
            background: var(--gradient-accent);
            border-radius: 3px;
            position: relative;
            height: 100%;
        }

        .bookmarks-panel {
            position: fixed;
            right: -300px;
            top: 80px;
            bottom: 0;
            width: 300px;
            background: rgba(26, 26, 46, 0.95);
            border-left: 1px solid rgba(255, 255, 255, 0.1);
            overflow-y: auto;
            transition: right 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 50;
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow-medium);
        }

        .bookmarks-header {
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .bookmarks-header h4 {
            color: white;
            margin: 0;
            font-size: 18px;
        }

        .bookmarks-actions {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .bookmarks-panel.open {
            right: 0;
        }

        .bookmark-item {
            padding: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            cursor: pointer;
            transition: all 0.3s ease;
            border-radius: 12px;
            margin: 8px;
            backdrop-filter: blur(10px);
        }

        .bookmark-item:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(-5px);
        }

        .bookmark-item .page-number {
            font-weight: 600;
            color: var(--highlight-color);
            font-size: 0.9rem;
        }

        .bookmark-item .bookmark-title {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 0.25rem;
        }

        .viewer-content.with-bookmarks {
            margin-right: 300px;
            transition: margin-right 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .add-bookmark-btn {
            position: fixed;
            top: 50%;
            right: 30px;
            transform: translateY(-50%);
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--gradient-secondary);
            color: white;
            border: none;
            font-size: 1.3rem;
            cursor: pointer;
            box-shadow: var(--shadow-heavy);
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            z-index: 60;
        }

        .add-bookmark-btn:hover {
            transform: translateY(-50%) scale(1.1);
            box-shadow: var(--shadow-heavy);
        }

        /* Reading Progress */
        .reading-progress {
            position: fixed;
            bottom: 80px;
            right: 20px;
            width: 200px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 15px;
            color: white;
            font-size: 12px;
            z-index: 999;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .reading-progress .progress {
            height: 6px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
            overflow: hidden;
        }

        .reading-progress .progress-bar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: width 0.3s ease;
            border-radius: 3px;
        }

        .search-panel {
            position: fixed;
            top: -200px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(26, 26, 46, 0.95);
            padding: 1.5rem;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: var(--shadow-heavy);
            z-index: 200;
            transition: top 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            min-width: 350px;
        }

        .search-panel.open {
            top: 100px;
        }

        .search-input {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--text-light);
            border-radius: 12px;
            padding: 0.75rem 1rem;
            width: 100%;
            backdrop-filter: blur(10px);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--highlight-color);
            box-shadow: 0 0 0 3px rgba(233, 69, 96, 0.1);
        }

        .search-input::placeholder {
            color: var(--text-muted);
        }

        @media (max-width: 768px) {
            .viewer-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
                padding: 1rem;
            }

            .viewer-controls {
                justify-content: center;
                flex-wrap: wrap;
                gap: 0.5rem;
            }

            .page-thumbnails {
                width: 180px;
                transform: translateX(-180px);
            }

            .viewer-content.with-thumbnails {
                margin-left: 180px;
            }

            .zoom-controls {
                bottom: 20px;
                right: 20px;
                gap: 10px;
            }

            .zoom-btn {
                width: 50px;
                height: 50px;
                font-size: 1.1rem;
            }

            .reading-progress {
                bottom: 10px;
                left: 10px;
                padding: 0.75rem 1rem;
                font-size: 0.9rem;
            }

            .viewer-content {
                padding: 1rem;
            }

            .pdf-canvas {
                margin: 1rem auto;
                border-radius: 10px;
            }
        }

        @media (max-width: 480px) {
            .page-thumbnails {
                width: 100%;
                transform: translateY(100%);
                top: auto;
                bottom: 0;
                left: 0;
                right: 0;
                height: 40vh;
                border-right: none;
                border-top: 1px solid rgba(255, 255, 255, 0.1);
            }

            .page-thumbnails.open {
                transform: translateY(0);
            }

            .viewer-content.with-thumbnails {
                margin-left: 0;
                margin-bottom: 40vh;
            }

            .bookmarks-panel {
                width: 100%;
                transform: translateX(100%);
                right: 0;
                top: 0;
                height: 100vh;
            }

            .bookmarks-panel.open {
                transform: translateX(0);
            }

            .viewer-content.with-bookmarks {
                margin-right: 0;
            }

            .search-panel {
                width: 90%;
                min-width: auto;
                left: 5%;
                transform: none;
            }

            .add-bookmark-btn {
                right: 20px;
                width: 50px;
                height: 50px;
                font-size: 1.1rem;
            }

            .zoom-controls {
                bottom: calc(40vh + 20px);
            }

            .reading-progress {
                width: 150px;
                bottom: 70px;
                right: 15px;
                padding: 10px;
                font-size: 11px;
            }
        }

        /* Smooth animations */
        .pdf-canvas,
        .thumbnail,
        .viewer-controls button,
        .zoom-btn {
            will-change: transform;
        }

        /* Reduce motion for accessibility */
        @media (prefers-reduced-motion: reduce) {
            .page-thumbnails,
            .viewer-content.with-thumbnails,
            .pdf-canvas,
            .thumbnail,
            .viewer-controls button,
            .zoom-btn {
                transition: none !important;
                animation: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="viewer-container">
        <!-- Header -->
        <div class="viewer-header">
            <div class="book-info">
                <div class="book-cover-small">
                    <i class="fas fa-file-pdf"></i>
                </div>
                <div>
                    <h5 class="mb-0"><?php echo htmlspecialchars($book_title); ?></h5>
                    <small>PDF Viewer</small>
                </div>
            </div>
            
            <div class="viewer-controls">
                <button onclick="toggleThumbnails()" title="Toggle Thumbnails">
                    <i class="fas fa-th"></i>
                </button>
                <button onclick="toggleBookmarks()" title="Bookmarks">
                    <i class="fas fa-bookmark"></i>
                </button>
                <button onclick="toggleSearch()" title="Search">
                    <i class="fas fa-search"></i>
                </button>
                <button onclick="previousPage()" id="prevBtn" title="Previous Page">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <span class="page-info">
                    <span id="pageNum">1</span> / <span id="pageCount">0</span>
                    <input type="number" id="pageInput" min="1" max="1" style="width: 60px; margin: 0 10px; padding: 5px; border: 1px solid rgba(255,255,255,0.3); background: rgba(255,255,255,0.1); color: white; border-radius: 4px;" onkeypress="if(event.key==='Enter'){goToPage(parseInt(this.value))}">
                    <button onclick="goToPage(parseInt(document.getElementById('pageInput').value))" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.3); color: white; padding: 5px 10px; border-radius: 4px; cursor: pointer;">Go</button>
                </span>
                <button onclick="nextPage()" id="nextBtn" title="Next Page">
                    <i class="fas fa-chevron-right"></i>
                </button>
                <button onclick="toggleFullScreen()" title="Full Screen">
                    <i class="fas fa-expand"></i>
                </button>
                <button onclick="closeViewer()" title="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Thumbnails Sidebar -->
        <div class="page-thumbnails" id="thumbnails"></div>

        <!-- Bookmarks Panel -->
        <div class="bookmarks-panel" id="bookmarksPanel">
            <div class="bookmarks-header">
                <h4>Bookmarks</h4>
                <div class="bookmarks-actions">
                    <button onclick="exportBookmarks()" title="Export Bookmarks" style="background: none; border: none; color: white; margin-right: 10px; cursor: pointer;">
                        <i class="fas fa-download"></i>
                    </button>
                    <label for="importBookmarks" style="background: none; border: none; color: white; margin-right: 10px; cursor: pointer;">
                        <i class="fas fa-upload"></i>
                        <input type="file" id="importBookmarks" accept=".json" onchange="importBookmarks(event)" style="display: none;">
                    </label>
                    <button class="close-panel" onclick="toggleBookmarks()">&times;</button>
                </div>
            </div>
            <div class="bookmarks-list" id="bookmarksList">
                <div class="text-muted text-center py-4">
                    <i class="fas fa-bookmark fa-2x mb-2 opacity-50"></i>
                    <p class="mb-0">No bookmarks yet</p>
                    <small>Click the + button to add a bookmark</small>
                </div>
            </div>
        </div>

        <!-- Search Panel -->
        <div class="search-panel" id="searchPanel">
            <div class="mb-3">
                <h6 class="text-white mb-3">
                    <i class="fas fa-search me-2"></i>Search in Document
                </h6>
                <div class="input-group">
                    <input type="text" class="search-input" id="searchInput" placeholder="Enter search term...">
                    <button class="btn btn-outline-secondary" onclick="performSearch()">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            <div id="searchResults" class="search-results"></div>
        </div>

        <!-- Content Area -->
        <div class="viewer-content" id="viewerContent">
            <div class="loading-overlay" id="loadingOverlay">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p>Loading PDF...</p>
            </div>

            <div id="errorMessage" class="error-message" style="display: none;">
                <i class="fas fa-exclamation-triangle"></i>
                <h5 id="errorTitle">Error Loading PDF</h5>
                <p id="errorText">Unable to load the PDF file. Please try again.</p>
            </div>

            <canvas id="pdfCanvas"></canvas>
            <!-- <div class="error-message" id="errorMessage" style="display: none;">
                <i class="fas fa-exclamation-triangle"></i>
                <h5>Error Loading PDF</h5>
                <p>Unable to load the PDF file. Please try again.</p>
            </div> -->
        </div>

        <!-- Zoom Controls -->
        <div class="zoom-controls">
            <button class="zoom-btn" onclick="zoomIn()" title="Zoom In">
                <i class="fas fa-search-plus"></i>
            </button>
            <button class="zoom-btn" onclick="zoomOut()" title="Zoom Out">
                <i class="fas fa-search-minus"></i>
            </button>
            <button class="zoom-btn" onclick="resetZoom()" title="Reset Zoom">
                <i class="fas fa-compress-arrows-alt"></i>
            </button>
        </div>

        <!-- Add Bookmark Button -->
        <button class="add-bookmark-btn" onclick="addBookmark()" title="Add Bookmark">
            <i class="fas fa-plus"></i>
        </button>

        <!-- Reading Progress -->
        <div class="reading-progress">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span>Reading Progress</span>
                <div>
                    <span id="progressPercent">0%</span>
                    <button onclick="clearReadingProgress()" title="Clear Progress" style="background: none; border: none; color: #ff6b6b; margin-left: 8px; cursor: pointer;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="progress">
                <div class="progress-bar" role="progressbar" style="width: 0%"></div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // PDF.js configuration
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        let pdfDoc = null;
        let pageNum = 1;
        let pageRendering = false;
        let pageNumPending = null;
        let scale = 1.0;
        let canvas = document.getElementById('pdfCanvas');
        let ctx = canvas.getContext('2d');
        let thumbnails = [];
        let bookmarks = [];
        let searchResults = [];

        // Load PDF
        const url = '<?php echo htmlspecialchars($pdf_url); ?>';

        console.log('APP_URL:', '<?php echo APP_URL; ?>');
        console.log('Constructed PDF URL:', url);
        const bookId = '<?php echo $book_id; ?>';
        const bookType = '<?php echo $book_type; ?>';
        const bookTitle = '<?php echo htmlspecialchars($book_title); ?>'; // Added bookTitle

        console.log('Attempting to load PDF from URL:', url); // Debugging line

        // Show loading overlay
        document.getElementById('loadingOverlay').style.display = 'flex';

        // Load bookmarks from localStorage
        function loadBookmarks() {
            const key = `bookmarks_${bookType}_${bookId}`;
            const saved = localStorage.getItem(key);
            bookmarks = saved ? JSON.parse(saved) : [];
            renderBookmarks();
        }

        // Save bookmarks to localStorage
        function saveBookmarks() {
            const key = `bookmarks_${bookType}_${bookId}`;
            localStorage.setItem(key, JSON.stringify(bookmarks));
        }

        // Render bookmarks
        function renderBookmarks() {
            const bookmarksList = document.getElementById('bookmarksList');
            
            if (bookmarks.length === 0) {
                bookmarksList.innerHTML = `
                    <div class="text-muted text-center py-4">
                        <i class="fas fa-bookmark fa-2x mb-2 opacity-50"></i>
                        <p class="mb-0">No bookmarks yet</p>
                        <small>Click the + button to add a bookmark</small>
                    </div>
                `;
                return;
            }

            bookmarksList.innerHTML = bookmarks.map(bookmark => `
                <div class="bookmark-item" onclick="goToPage(${bookmark.page})">
                    <div class="page-number">Page ${bookmark.page}</div>
                    <div class="bookmark-title">${bookmark.title}</div>
                    <small class="text-muted">${bookmark.date}</small>
                </div>
            `).join('');
        }

        // Add bookmark
        function addBookmark() {
            const title = prompt('Enter bookmark title:', `Page ${pageNum}`);
            if (!title) return;

            const bookmark = {
                page: pageNum,
                title: title,
                date: new Date().toLocaleString()
            };

            bookmarks.push(bookmark);
            saveBookmarks();
            renderBookmarks();
            
            // Show success message
            showToast('Bookmark added successfully!', 'success');
        }

        // Go to page
        function goToPage(page) {
            pageNum = page;
            renderPage(pageNum);
            updateThumbnailHighlight();
        }

        // Toggle bookmarks panel
        function toggleBookmarks() {
            const bookmarksPanel = document.getElementById('bookmarksPanel');
            const viewerContent = document.getElementById('viewerContent');
            
            bookmarksPanel.classList.toggle('open');
            viewerContent.classList.toggle('with-bookmarks');
        }

        // Toggle search panel
        function toggleSearch() {
            const searchPanel = document.getElementById('searchPanel');
            searchPanel.classList.toggle('open');
            
            if (searchPanel.classList.contains('open')) {
                document.getElementById('searchInput').focus();
            }
        }

        // Show toast notification
        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'info'} border-0`;
            toast.setAttribute('role', 'alert');
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            
            document.body.appendChild(toast);
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }

        // Toggle Full Screen
        function toggleFullScreen() {
            const viewerContainer = document.querySelector('.viewer-container');
            if (!document.fullscreenElement) {
                viewerContainer.requestFullscreen().catch(err => {
                    alert(`Error attempting to enable full-screen mode: ${err.message} (${err.name})`);
                });
            } else {
                document.exitFullscreen();
            }
        }

        // Load PDF document
        try {
            pdfjsLib.getDocument(url).promise.then(function(pdfDoc_) {
                pdfDoc = pdfDoc_;
                document.getElementById('pageCount').textContent = pdfDoc.numPages;
                document.getElementById('pageInput').max = pdfDoc.numPages;
                
                // Hide loading overlay
                document.getElementById('loadingOverlay').style.display = 'none';
                
                // Load bookmarks
                loadBookmarks();
                
                // Load reading progress
                loadReadingProgress();
                
                // Track recent activity
                trackRecentActivity();
                
                // Initial page render
                renderPage(pageNum);
                
                // Generate thumbnails
                generateThumbnails();
            }).catch(function(error) {
                console.error('Error loading PDF:', error);
                document.getElementById('loadingOverlay').style.display = 'none';
                document.getElementById('errorMessage').style.display = 'flex';
                document.getElementById('errorText').textContent = 'Unable to load the PDF file. Please try again. Details: ' + error.message;
            });
        } catch (e) {
            console.error('Synchronous error before PDF.js getDocument:', e);
            document.getElementById('loadingOverlay').style.display = 'none';
            document.getElementById('errorMessage').style.display = 'flex';
            document.getElementById('errorText').textContent = 'An unexpected error occurred. Details: ' + e.message;
        }

        // Search functionality
        function performSearch() {
            const searchTerm = document.getElementById('searchInput').value.trim();
            if (!searchTerm) return;

            // Check if the search term is a number and within page range
            const pageNumber = parseInt(searchTerm, 10);
            if (!isNaN(pageNumber) && pageNumber >= 1 && pageNumber <= pdfDoc.numPages) {
                goToPage(pageNumber);
                // Optionally, clear search results or show a message that it navigated to the page
                document.getElementById('searchResults').innerHTML = `
                    <div class="text-muted text-center py-3">
                        <i class="fas fa-info-circle fa-lg mb-2 opacity-50"></i>
                        <p class="mb-0">Navigated to page ${pageNumber}</p>
                    </div>
                `;
                return;
            }

            // Clear previous search results
            searchResults = [];
            
            // Search through all pages
            const searchPromises = [];
            for (let i = 1; i <= pdfDoc.numPages; i++) {
                searchPromises.push(
                    pdfDoc.getPage(i).then(function(page) {
                        return page.getTextContent().then(function(textContent) {
                            const text = textContent.items.map(item => item.str).join(' ');
                            if (text.toLowerCase().includes(searchTerm.toLowerCase())) {
                                searchResults.push({
                                    page: i,
                                    text: text.substring(0, 200) + '...'
                                });
                            }
                        });
                    })
                );
            }

            Promise.all(searchPromises).then(function() {
                displaySearchResults();
            });
        }

        // Display search results
        function displaySearchResults() {
            const searchResultsDiv = document.getElementById('searchResults');
            
            if (searchResults.length === 0) {
                searchResultsDiv.innerHTML = `
                    <div class="text-muted text-center py-3">
                        <i class="fas fa-search fa-lg mb-2 opacity-50"></i>
                        <p class="mb-0">No results found</p>
                    </div>
                `;
                return;
            }

            searchResultsDiv.innerHTML = searchResults.map(result => `
                <div class="bookmark-item" onclick="goToPage(${result.page})">
                    <div class="page-number">Page ${result.page}</div>
                    <div class="bookmark-title">${result.text}</div>
                </div>
            `).join('');
        }

        // Handle search input enter key
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });

        // Reading progress functions
        function saveReadingProgress() {
            const key = `reading_progress_${bookType}_${bookId}`;
            const progress = {
                currentPage: pageNum,
                totalPages: pdfDoc.numPages,
                lastRead: new Date().toISOString()
            };
            localStorage.setItem(key, JSON.stringify(progress));
        }

        function loadReadingProgress() {
            const key = `reading_progress_${bookType}_${bookId}`;
            const saved = localStorage.getItem(key);
            if (saved) {
                try {
                    const progress = JSON.parse(saved);
                    if (progress.currentPage && progress.currentPage <= pdfDoc.numPages) {
                        pageNum = progress.currentPage;
                        showToast('Reading progress restored', 'success');
                    }
                } catch (error) {
                    console.warn('Error loading reading progress:', error);
                }
            }
        }

        function updateReadingProgress() {
            const progress = (pageNum / pdfDoc.numPages) * 100;
            
            // Update progress bar if it exists
            const progressBar = document.querySelector('.reading-progress .progress-bar');
            if (progressBar) {
                progressBar.style.width = progress + '%';
            }
            
            // Update progress text
            const progressText = document.querySelector('.reading-progress .progress-text');
            if (progressText) {
                progressText.textContent = `${Math.round(progress)}% Complete`;
            }
            
            // Update progress percentage display
            const progressPercent = document.getElementById('progressPercent');
            if (progressPercent) {
                progressPercent.textContent = Math.round(progress) + '%';
            }
        }

        // Render page
        function renderPage(num) {
            pageRendering = true;

            // Using promise to fetch the page
            pdfDoc.getPage(num).then(function(page) {
                const viewerContent = document.getElementById('viewerContent');
                const desiredWidth = viewerContent.offsetWidth - 40; // 40px for padding

                const viewport = page.getViewport({ scale: 1 });
                let newScale = desiredWidth / viewport.width;

                // Ensure scale is within reasonable bounds
                if (newScale < 0.5) newScale = 0.5;
                if (newScale > 2.0) newScale = 2.0;

                scale = newScale; // Update global scale

                const scaledViewport = page.getViewport({ scale: scale });
                canvas.height = scaledViewport.height;
                canvas.width = scaledViewport.width;

                // Render PDF page into canvas context
                let renderContext = {
                    canvasContext: ctx,
                    viewport: scaledViewport
                };
                let renderTask = page.render(renderContext);

                // Wait for rendering to finish
                renderTask.promise.then(function() {
                    pageRendering = false;
                    if (pageNumPending !== null) {
                        // New page rendering is pending
                        renderPage(pageNumPending);
                        pageNumPending = null;
                    }
                });
            });

            // Update page counters
            document.getElementById('pageNum').textContent = num;

            // Update reading progress
            updateReadingProgress();

            // Save current page to localStorage
            saveReadingProgress();
        }

        // Function to handle resizing and re-rendering
        function resizeCanvasAndRender() {
            if (pdfDoc) {
                renderPage(pageNum);
            }
        }

        // Add event listener for window resize
        window.addEventListener('resize', resizeCanvasAndRender);

        // Export bookmarks
    function exportBookmarks() {
        const bookmarksData = JSON.stringify(bookmarks, null, 2);
        const blob = new Blob([bookmarksData], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `bookmarks_${bookTitle || 'book'}_${new Date().toISOString().split('T')[0]}.json`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
        showToast('Bookmarks exported successfully', 'success');
    }

    // Import bookmarks
    function importBookmarks(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const importedBookmarks = JSON.parse(e.target.result);
                    if (Array.isArray(importedBookmarks)) {
                        bookmarks = importedBookmarks;
                        saveBookmarks();
                        renderBookmarks();
                        showToast('Bookmarks imported successfully', 'success');
                    } else {
                        showToast('Invalid bookmark file format', 'error');
                    }
                } catch (error) {
                    showToast('Error importing bookmarks', 'error');
                }
            };
            reader.readAsText(file);
        }
    }

    // Track recent activity
    function trackRecentActivity() {
        fetch('../api/add-recent-activity.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                book_id: bookId,
                book_type: bookType,
                action: 'opened'
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Activity tracked:', data);
        })
        .catch(error => {
            console.error('Error tracking activity:', error);
        });
    }

        // Queue render page
        function queueRenderPage(num) {
            if (pageRendering) {
                pageNumPending = num;
            } else {
                renderPage(num);
            }
        }

        // Previous page
        function previousPage() {
            if (pageNum <= 1) {
                return;
            }
            pageNum--;
            queueRenderPage(pageNum);
            updateThumbnailHighlight();
        }

        // Next page
        function nextPage() {
            if (pageNum >= pdfDoc.numPages) {
                return;
            }
            pageNum++;
            queueRenderPage(pageNum);
            updateThumbnailHighlight();
        }

        // Go to specific page
        function goToPage(pageNumber) {
            if (pageNumber >= 1 && pageNumber <= pdfDoc.numPages) {
                pageNum = pageNumber;
                queueRenderPage(pageNum);
                updateThumbnailHighlight();
                updateReadingProgress();
                saveReadingProgress();
                
                // Close any open panels when navigating
                const searchPanel = document.getElementById('searchPanel');
                const bookmarksPanel = document.getElementById('bookmarksPanel');
                const thumbnails = document.getElementById('thumbnails');
                
                if (searchPanel && searchPanel.classList.contains('open')) {
                    searchPanel.classList.remove('open');
                }
                if (bookmarksPanel && bookmarksPanel.classList.contains('open')) {
                    bookmarksPanel.classList.remove('open');
                }
                if (thumbnails && thumbnails.classList.contains('open')) {
                    thumbnails.classList.remove('open');
                    document.getElementById('viewerContent').classList.remove('with-thumbnails');
                }
            }
        }

        // Zoom functions
        function zoomIn() {
            scale += 0.2;
            renderPage(pageNum);
        }

        function zoomOut() {
            if (scale <= 0.4) return;
            scale -= 0.2;
            renderPage(pageNum);
        }

        function resetZoom() {
            scale = 1.0;
            renderPage(pageNum);
        }

        // Generate thumbnails
        function generateThumbnails() {
            const thumbnailsContainer = document.getElementById('thumbnails');
            
            for (let i = 1; i <= pdfDoc.numPages; i++) {
                pdfDoc.getPage(i).then(function(page) {
                    const thumbnail = document.createElement('div');
                    thumbnail.className = 'thumbnail';
                    thumbnail.dataset.page = i;
                    
                    const thumbCanvas = document.createElement('canvas');
                    const thumbCtx = thumbCanvas.getContext('2d');
                    
                    const viewport = page.getViewport({scale: 0.2});
                    thumbCanvas.height = viewport.height;
                    thumbCanvas.width = viewport.width;
                    
                    page.render({
                        canvasContext: thumbCtx,
                        viewport: viewport
                    }).promise.then(function() {
                        thumbnail.appendChild(thumbCanvas);
                        thumbnail.addEventListener('click', function() {
                            pageNum = i;
                            renderPage(pageNum);
                            updateThumbnailHighlight();
                        });
                        
                        thumbnailsContainer.appendChild(thumbnail);
                        thumbnails.push(thumbnail);
                    });
                });
            }
        }

        // Update thumbnail highlight
        function updateThumbnailHighlight() {
            thumbnails.forEach(thumb => {
                thumb.classList.remove('active');
                if (parseInt(thumb.dataset.page) === pageNum) {
                    thumb.classList.add('active');
                }
            });
        }

        // Toggle thumbnails sidebar
        function toggleThumbnails() {
            const thumbnails = document.getElementById('thumbnails');
            const viewerContent = document.getElementById('viewerContent');
            
            thumbnails.classList.toggle('open');
            viewerContent.classList.toggle('with-thumbnails');
        }

        // Close viewer
        function closeViewer() {
            window.close();
            // Fallback for browsers that don't support window.close()
            window.location.href = 'dashboard.php';
        }

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            switch(e.key) {
                case 'ArrowLeft':
                    e.preventDefault();
                    previousPage();
                    break;
                case 'ArrowRight':
                    e.preventDefault();
                    nextPage();
                    break;
                case 'Escape':
                    e.preventDefault();
                    // Close panels first, then viewer
                    const searchPanel = document.getElementById('searchPanel');
                    const bookmarksPanel = document.getElementById('bookmarksPanel');
                    const thumbnails = document.getElementById('thumbnails');
                    
                    if (searchPanel.classList.contains('open')) {
                        searchPanel.classList.remove('open');
                    } else if (bookmarksPanel.classList.contains('open')) {
                        bookmarksPanel.classList.remove('open');
                        document.getElementById('viewerContent').classList.remove('with-bookmarks');
                    } else if (thumbnails.classList.contains('open')) {
                        thumbnails.classList.remove('open');
                        document.getElementById('viewerContent').classList.remove('with-thumbnails');
                    } else {
                        closeViewer();
                    }
                    break;
                case '+':
                case '=':
                    e.preventDefault();
                    zoomIn();
                    break;
                case '-':
                    e.preventDefault();
                    zoomOut();
                    break;
                case '0':
                    e.preventDefault();
                    resetZoom();
                    break;
                case 'b':
                    if (e.ctrlKey) {
                        e.preventDefault();
                        addBookmark();
                    }
                    break;
                case 'f':
                    if (e.ctrlKey) {
                        e.preventDefault();
                        toggleSearch();
                    }
                    break;
                case 't':
                    if (e.ctrlKey) {
                        e.preventDefault();
                        toggleThumbnails();
                    }
                    break;
            }
        });

        // Touch/swipe navigation for mobile
        let touchStartX = 0;
        let touchEndX = 0;

        canvas.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
        });

        canvas.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        });

        function handleSwipe() {
            const swipeThreshold = 50;
            const diff = touchStartX - touchEndX;
            
            if (Math.abs(diff) > swipeThreshold) {
                if (diff > 0) {
                    nextPage(); // Swipe left
                } else {
                    previousPage(); // Swipe right
                }
            }
        }
    </script>
</body>
</html>