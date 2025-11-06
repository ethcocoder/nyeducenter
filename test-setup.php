<?php
// Test file to verify system setup
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

echo "<h1>Library System Setup Test</h1>";
echo "<h2>Configuration Check</h2>";
echo "<pre>";
echo "APP_NAME: " . APP_NAME . "\n";
echo "DB_HOST: " . DB_HOST . "\n";
echo "DB_NAME: " . DB_NAME . "\n";
echo "MAX_FILE_SIZE: " . number_format(MAX_FILE_SIZE / (1024*1024)) . " MB\n";
echo "</pre>";

echo "<h2>Database Connection Test</h2>";
try {
    $db = Database::getInstance();
    echo "<p style='color: green;'>✓ Database connection successful</p>";
    
    // Test if tables exist
    $tables = ['users', 'grade_categories', 'system_books', 'user_folders', 'user_books', 'recent_activity'];
    
    foreach ($tables as $table) {
        try {
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM $table");
            $stmt->execute();
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            echo "<p style='color: green;'>✓ Table '$table' exists ($count records)</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>✗ Table '$table' missing: " . $e->getMessage() . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
}

echo "<h2>File Permissions Test</h2>";
$upload_dirs = [
    'assets/uploads',
    'assets/uploads/system-books',
    'assets/uploads/user-books'
];

foreach ($upload_dirs as $dir) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "<p style='color: green;'>✓ Directory '$dir' is writable</p>";
        } else {
            echo "<p style='color: red;'>✗ Directory '$dir' is not writable</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Directory '$dir' does not exist</p>";
    }
}

echo "<h2>Sample Data Creation</h2>";
if (isset($_GET['create_sample'])) {
    try {
        $db = Database::getInstance();
        
        // Create sample categories if they don't exist
        $categories = [
            ['Grade 1', 'Elementary school first grade books'],
            ['Grade 2', 'Elementary school second grade books'],
            ['Grade 3', 'Elementary school third grade books'],
            ['Grade 4', 'Elementary school fourth grade books'],
            ['Grade 5', 'Elementary school fifth grade books'],
            ['Grade 6', 'Elementary school sixth grade books'],
        ];
        
        foreach ($categories as $cat) {
            $stmt = $db->prepare("SELECT id FROM grade_categories WHERE grade_name = ?");
            $stmt->execute([$cat[0]]);
            if (!$stmt->fetch()) {
                $stmt = $db->prepare("INSERT INTO grade_categories (grade_name, description) VALUES (?, ?)");
                $stmt->execute($cat);
                echo "<p style='color: green;'>✓ Created category: {$cat[0]}</p>";
            }
        }
        
        // Create sample users if they don't exist
        $sample_users = [
            ['student1', 'student1@example.com', 'Student One', password_hash('student123', PASSWORD_DEFAULT), 'user'],
            ['student2', 'student2@example.com', 'Student Two', password_hash('student123', PASSWORD_DEFAULT), 'user'],
            ['teacher1', 'teacher1@example.com', 'Teacher One', password_hash('teacher123', PASSWORD_DEFAULT), 'user'],
        ];
        
        foreach ($sample_users as $user) {
            $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$user[0]]);
            if (!$stmt->fetch()) {
                $stmt = $db->prepare("INSERT INTO users (username, email, full_name, password, role) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute($user);
                echo "<p style='color: green;'>✓ Created user: {$user[0]}</p>";
            }
        }
        
        echo "<p style='color: green; font-weight: bold;'>Sample data created successfully!</p>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Error creating sample data: " . $e->getMessage() . "</p>";
    }
}

echo "<hr>";
echo "<a href='?create_sample=1' class='btn btn-primary'>Create Sample Data</a> ";
echo "<a href='index.php' class='btn btn-secondary'>Go to Homepage</a> ";
echo "<a href='setup.php' class='btn btn-warning'>Run Setup</a>";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Library System Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <?php
        // Content is echoed above
        ?>
    </div>
</body>
</html>