<?php
require_once '../includes/functions.php';
require_login();

$grade_id = $_GET['grade'] ?? 0;
$grade_name = $_GET['name'] ?? 'Unknown Grade';

if (!$grade_id) {
    header('Location: dashboard.php?tab=books');
    exit;
}

// Get books for this grade category
$books = get_system_books_by_grade($grade_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($grade_name); ?> Books - <?php echo APP_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --success-color: #27ae60;
            --light-bg: #ecf0f1;
            --dark-text: #2c3e50;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--light-bg);
            color: var(--dark-text);
        }

        .navbar-custom {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }

        .book-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .book-cover {
            width: 80px;
            height: 100px;
            background: linear-gradient(135deg, var(--secondary-color), #5dade2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            margin-right: 1.5rem;
        }

        .book-info h5 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .book-info .author {
            color: #6c757d;
            margin-bottom: 0.5rem;
        }

        .book-info .description {
            color: #495057;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .book-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .btn-custom {
            border-radius: 25px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-read {
            background: linear-gradient(135deg, var(--success-color), #2ecc71);
            color: white;
            border: none;
        }

        .btn-read:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
        }

        .btn-download {
            background: linear-gradient(135deg, var(--secondary-color), #5dade2);
            color: white;
            border: none;
        }

        .btn-download:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 5rem;
            margin-bottom: 2rem;
            opacity: 0.5;
        }

        .breadcrumb-custom {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 0.5rem;
        }

        .stats-label {
            color: #6c757d;
            font-weight: 500;
        }

        .search-box {
            background: white;
            border-radius: 25px;
            border: 2px solid var(--secondary-color);
            padding: 0.75rem 1.5rem;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }

        .search-box:focus {
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
            border-color: var(--secondary-color);
        }

        .back-btn {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateX(-2px);
        }

        @media (max-width: 768px) {
            .book-card {
                flex-direction: column;
                text-align: center;
            }

            .book-cover {
                margin: 0 auto 1rem auto;
            }

            .book-actions {
                justify-content: center;
            }

            .stats-number {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <a href="dashboard.php?tab=books" class="back-btn me-3">
                <i class="fas fa-arrow-left"></i>
            </a>
<a class="navbar-brand" href="#">
            <img src="../assets/images/logo.svg" alt="Logo" style="height: 40px; margin-right: 10px;">
            <i class="fas fa-layer-group me-2"></i>
            <?php echo htmlspecialchars($grade_name); ?> Books
        </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-home me-1"></i>
                    Home
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container my-5">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-custom">
                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                <li class="breadcrumb-item"><a href="dashboard.php?tab=books">Books</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($grade_name); ?></li>
            </ol>
        </nav>

        <!-- Stats -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?php echo count($books); ?></div>
                    <div class="stats-label">Books Available</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?php echo count(array_filter($books, function($book) { return !empty($book['author']); })); ?></div>
                    <div class="stats-label">Authors</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?php echo htmlspecialchars($grade_name); ?></div>
                    <div class="stats-label">Grade Category</div>
                </div>
            </div>
        </div>

        <!-- Search Box -->
        <div class="row mb-4">
            <div class="col-md-6 mx-auto">
                <input type="text" class="form-control search-box" id="searchInput" placeholder="Search books by title or author...">
            </div>
        </div>

        <!-- Books List -->
        <div class="row">
            <div class="col-12">
                <h4 class="mb-4">
                    <i class="fas fa-book me-2"></i>
                    Available Books
                </h4>
                
                <?php if (empty($books)): ?>
                    <div class="empty-state">
                        <i class="fas fa-book-open"></i>
                        <h5>No Books Available</h5>
                        <p>No books are currently available in this grade category.</p>
                        <a href="dashboard.php?tab=books" class="btn btn-primary mt-3">
                            <i class="fas fa-arrow-left me-2"></i>
                            Back to Books
                        </a>
                    </div>
                <?php else: ?>
                    <div id="booksContainer">
                        <?php foreach ($books as $book): ?>
                            <div class="book-card d-flex align-items-center" data-title="<?php echo htmlspecialchars(strtolower($book['title'])); ?>" data-author="<?php echo htmlspecialchars(strtolower($book['author'] ?? '')); ?>">
                                <div class="book-cover">
                                    <i class="fas fa-file-pdf"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h5><?php echo htmlspecialchars($book['title']); ?></h5>
                                    <?php if (!empty($book['author'])): ?>
                                        <div class="author">
                                            <i class="fas fa-user me-1"></i>
                                            <?php echo htmlspecialchars($book['author']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($book['description'])): ?>
                                        <div class="description">
                                            <?php echo htmlspecialchars(substr($book['description'], 0, 200)) . (strlen($book['description']) > 200 ? '...' : ''); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="book-actions">
                                        <button class="btn btn-read btn-custom" onclick="openBook('<?php echo $book['id']; ?>', '<?php echo htmlspecialchars($book['title']); ?>', '<?php echo urlencode($book['file_path']); ?>', true)">
                                            <i class="fas fa-book-open me-1"></i>
                                            Read Now
                                        </button>
                                        <button class="btn btn-download btn-custom" onclick="downloadBook('<?php echo htmlspecialchars($book['file_path']); ?>', '<?php echo htmlspecialchars($book['title']); ?>')">
                                            <i class="fas fa-download me-1"></i>
                                            Download
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function openBook(bookId, bookTitle, filePath, isSystemBook) {
            // Add to recent activity
            fetch('../api/add-recent-activity.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    book_id: bookId,
                    book_type: isSystemBook ? 'system' : 'user'
                })
            });
            
            let viewerUrl = '';
            if (isSystemBook) {
                viewerUrl = `pdf-viewer.php?type=system&id=${bookId}&file=${encodeURIComponent(filePath)}`;
            } else {
                viewerUrl = `pdf-viewer.php?type=user&id=${bookId}&file=${encodeURIComponent(filePath)}`;
            }
            window.open(viewerUrl, '_blank');
        }

        function downloadBook(filePath, bookTitle) {
            // Create a temporary link to trigger download
            const link = document.createElement('a');
            link.href = '../' + filePath;
            link.download = bookTitle + '.pdf';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const books = document.querySelectorAll('.book-card');
            
            books.forEach(book => {
                const title = book.getAttribute('data-title');
                const author = book.getAttribute('data-author');
                
                if (title.includes(searchTerm) || author.includes(searchTerm)) {
                    book.style.display = 'flex';
                } else {
                    book.style.display = 'none';
                }
            });
        });

        // Add smooth scrolling
        document.addEventListener('DOMContentLoaded', function() {
            const booksContainer = document.getElementById('booksContainer');
            if (booksContainer) {
                booksContainer.style.opacity = '0';
                booksContainer.style.transform = 'translateY(20px)';
                booksContainer.style.transition = 'all 0.5s ease';
                
                setTimeout(() => {
                    booksContainer.style.opacity = '1';
                    booksContainer.style.transform = 'translateY(0)';
                }, 100);
            }
        });
    </script>
</body>
</html>