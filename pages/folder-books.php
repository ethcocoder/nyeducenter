<?php
require_once '../includes/functions.php';
require_login();

$folder_id = $_GET['folder'] ?? 0;
$folder_name = $_GET['name'] ?? 'Unknown Folder';

if (!$folder_id) {
    header('Location: dashboard.php?tab=books');
    exit;
}

$user_id = $_SESSION['user_id'];

// Verify folder ownership
$folder = get_user_folder_by_id($folder_id, $user_id);
if (!$folder) {
    header('Location: dashboard.php?tab=books');
    exit;
}

// Get books in this folder
$books = get_user_books_by_folder($folder_id, $user_id);

// Read and decode Json.md file
$json_file_path = 'Json.md';
$json_content = file_get_contents($json_file_path);
$all_books_data = json_decode($json_content, true);

// Add system books if folder name matches a grade in Json.md
if (isset($all_books_data[$folder_name])) {
    foreach ($all_books_data[$folder_name] as $subject => $url) {
        $books[] = [
            'id' => uniqid(),
            'book_name' => $subject,
            'book_path' => $url,
            'created_at' => date('Y-m-d H:i:s'),
            'file_size' => 0,
            'is_system_book' => true
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($folder_name); ?> - <?php echo APP_NAME; ?></title>
    
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--light-bg);
            color: var(--dark-text);
        }

        .navbar-custom {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-bottom: none;
            padding: 1rem 0;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: white;
        }

        .book-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            cursor: pointer;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .book-cover {
            width: 80px;
            height: 100px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            margin-right: 1.5rem;
            box-shadow: var(--shadow-medium);
        }

        .book-info h5 {
            color: var(--dark-text);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .book-info .uploaded {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .book-info .file-info {
            color: #495057;
            font-size: 0.85rem;
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
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
        }

        .btn-read:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(44, 62, 80, 0.3);
        }

        .btn-delete {
            background: linear-gradient(135deg, var(--accent-color), #c0392b);
            color: white;
            border: none;
        }

        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
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
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            margin-bottom: 2rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .stats-label {
            color: #6c757d;
            font-weight: 500;
        }

        .upload-area {
            border: 3px dashed var(--secondary-color);
            border-radius: 15px;
            padding: 3rem;
            text-align: center;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
            cursor: pointer;
            background: white;
            box-shadow: var(--shadow-light);
        }

        .upload-area:hover {
            background: rgba(52, 152, 219, 0.05);
            border-color: var(--primary-color);
            box-shadow: var(--shadow-medium);
        }

        .upload-area.dragover {
            background: rgba(52, 152, 219, 0.1);
            border-color: var(--primary-color);
        }

        .upload-icon {
            font-size: 3rem;
            color: var(--secondary-color);
            margin-bottom: 1rem;
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

        .search-box {
            background: white;
            border-radius: 25px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            padding: 0.75rem 1.5rem;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-light);
        }

        .search-box:focus {
            box-shadow: 0 0 0 0.2rem rgba(44, 62, 80, 0.25);
            border-color: var(--primary-color);
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

            .upload-area {
                padding: 2rem;
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
            <i class="fas fa-folder me-2"></i>
            <?php echo htmlspecialchars($folder_name); ?>
        </a>     </a>
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
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($folder_name); ?></li>
            </ol>
        </nav>

        <!-- Stats -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?php echo count($books); ?></div>
                    <div class="stats-label">Books in Folder</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $folder['created_at'] ? date('M j, Y', strtotime($folder['created_at'])) : 'N/A'; ?></div>
                    <div class="stats-label">Created</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?php echo htmlspecialchars($folder_name); ?></div>
                    <div class="stats-label">Folder Name</div>
                </div>
            </div>
        </div>

        <!-- Upload Area -->
        <div class="upload-area" id="uploadArea" onclick="document.getElementById('fileInput').click()">
            <div class="upload-icon">
                <i class="fas fa-cloud-upload-alt"></i>
            </div>
            <h5>Upload PDF Books</h5>
            <p class="text-muted">Drag and drop PDF files here or click to browse</p>
            <small class="text-muted">Maximum file size: 50MB</small>
        </div>

        <input type="file" id="fileInput" accept=".pdf" multiple style="display: none;">

        <!-- Search Box -->
        <div class="row mb-4">
            <div class="col-md-6 mx-auto">
                <input type="text" class="form-control search-box" id="searchInput" placeholder="Search books by name...">
            </div>
        </div>

        <!-- Books List -->
        <div class="row">
            <div class="col-12">
                <h4 class="mb-4">
                    <i class="fas fa-book me-2"></i>
                    My Books
                </h4>
                
                <?php if (empty($books)): ?>
                    <div class="empty-state">
                        <i class="fas fa-folder-open"></i>
                        <h5>No Books Yet</h5>
                        <p>Upload some PDF books to get started!</p>
                    </div>
                <?php else: ?>
                    <div id="booksContainer">
                        <?php foreach ($books as $book): ?>
                            <div class="book-card d-flex align-items-center" data-name="<?php echo htmlspecialchars(strtolower($book['book_name'])); ?>">
                                <div class="book-cover">
                                    <i class="fas fa-file-pdf"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h5><?php echo htmlspecialchars($book['book_name']); ?></h5>
                                    <div class="uploaded">
                                        <i class="fas fa-calendar me-1"></i>
                                        Uploaded <?php echo date('M j, Y', strtotime($book['created_at'])); ?>
                                    </div>
                                    <div class="file-info">
                                        <i class="fas fa-file me-1"></i>
                                        <?php echo isset($book['file_size']) && !$book['is_system_book'] ? number_format($book['file_size'] / 1024 / 1024, 2) : 'N/A'; ?> MB
                                    </div>
                                </div>
                                <div class="book-actions">
                                    <button class="btn btn-read btn-custom" onclick="openBook('<?php echo $book['id']; ?>', '<?php echo htmlspecialchars($book['book_name']); ?>', '<?php echo htmlspecialchars($book['book_path']); ?>', <?php echo isset($book['is_system_book']) && $book['is_system_book'] ? 'true' : 'false'; ?>)">
                                        <i class="fas fa-book-open me-1"></i>
                                        Read
                                    </button>
                                    <?php if (isset($book['is_system_book']) && $book['is_system_book']): ?>
                                        <a href="<?php echo htmlspecialchars($book['book_path']); ?>" class="btn btn-download btn-custom" download="<?php echo htmlspecialchars($book['book_name']); ?>.pdf">
                                            <i class="fas fa-download me-1"></i>
                                            Download
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-delete btn-custom" onclick="deleteBook('<?php echo $book['id']; ?>', '<?php echo htmlspecialchars($book['book_name']); ?>')">
                                            <i class="fas fa-trash me-1"></i>
                                            Delete
                                        </button>
                                    <?php endif; ?>
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
        let uploadArea = document.getElementById('uploadArea');
        let fileInput = document.getElementById('fileInput');

        // Drag and drop functionality
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
            
            let files = e.dataTransfer.files;
            handleFiles(files);
        });

        // File input change
        fileInput.addEventListener('change', function() {
            handleFiles(this.files);
        });

        function handleFiles(files) {
            let validFiles = [];
            
            for (let file of files) {
                if (file.type === 'application/pdf') {
                    if (file.size <= 50 * 1024 * 1024) { // 50MB limit
                        validFiles.push(file);
                    } else {
                        alert('File "' + file.name + '" is too large. Maximum size is 50MB.');
                    }
                } else {
                    alert('File "' + file.name + '" is not a PDF file.');
                }
            }
            
            if (validFiles.length > 0) {
                uploadFiles(validFiles);
            }
        }

        function uploadFiles(files) {
            let formData = new FormData();
            formData.append('folder_id', '<?php echo $folder_id; ?>');
            
            for (let file of files) {
                formData.append('files[]', file);
            }
            
            // Show loading state
            uploadArea.innerHTML = `
                <div class="upload-icon">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
                <h5>Uploading...</h5>
                <p class="text-muted">Please wait while your files are being uploaded</p>
            `;
            
            fetch('../api/upload-books.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Upload failed');
                    location.reload();
                }
            })
            .catch(error => {
                alert('Upload failed: ' + error.message);
                location.reload();
            });
        }

        function openBook(bookId, bookName, filePath, isSystemBook) {
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
            
            if (isSystemBook) {
                console.log("System Book filePath:", filePath); // Log the filePath for debugging
                window.open(filePath, '_blank');
            } else {
                // Open enhanced PDF viewer with reading progress support
                const viewerUrl = `pdf-viewer.php?type=user&id=${bookId}&file=${encodeURIComponent(filePath)}&enhanced=1`;
                window.open(viewerUrl, '_blank');
            }
        }

        function deleteBook(bookId, bookName) {
            if (confirm('Are you sure you want to delete "' + bookName + '"? This action cannot be undone.')) {
                fetch('../api/delete-book.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        book_id: bookId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Delete failed');
                    }
                })
                .catch(error => {
                    alert('Delete failed: ' + error.message);
                });
            }
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const books = document.querySelectorAll('.book-card');
            
            books.forEach(book => {
                const name = book.getAttribute('data-name');
                
                if (name.includes(searchTerm)) {
                    book.style.display = 'flex';
                } else {
                    book.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>