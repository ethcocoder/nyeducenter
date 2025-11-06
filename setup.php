<?php
// Database Setup Script
require_once 'includes/config.php';

try {
    // Create connection without database selection
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Drop database if it exists
    $pdo->exec("DROP DATABASE IF EXISTS " . DB_NAME);
    echo "Database dropped if it existed.<br>";
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database created successfully.<br>";
    
    // Connect to the database
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('user', 'admin') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_username (username),
        INDEX idx_email (email)
    )";
    $pdo->exec($sql);
    echo "Users table created.<br>";
    
    // Create grade categories table
    $sql = "CREATE TABLE IF NOT EXISTS grade_categories (
        id INT PRIMARY KEY AUTO_INCREMENT,
        grade_name VARCHAR(20) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_grade_name (grade_name)
    )";
    $pdo->exec($sql);
    echo "Grade categories table created.<br>";
    
    // Insert default grade categories
    $grades = [
        'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6',
        'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12'
    ];
    
    $stmt = $pdo->prepare("INSERT INTO grade_categories (grade_name, description) VALUES (?, ?)");
    foreach ($grades as $grade) {
        $stmt->execute([$grade, "Books for $grade students"]);
    }
    echo "Default grade categories inserted.<br>";
    
    // Create system books table
    $sql = "CREATE TABLE IF NOT EXISTS system_books (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        file_path VARCHAR(500) NOT NULL,
        grade_category_id INT,
        cover_image VARCHAR(500),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (grade_category_id) REFERENCES grade_categories(id) ON DELETE CASCADE,
        INDEX idx_grade_category (grade_category_id),
        INDEX idx_title (title)
    )";
    $pdo->exec($sql);
    echo "System books table created.<br>";
    
    // Create user folders table
    $sql = "CREATE TABLE IF NOT EXISTS user_folders (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        folder_name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_user_folders (user_id)
    )";
    $pdo->exec($sql);
    echo "User folders table created.<br>";
    
    // Create user books table
    $sql = "CREATE TABLE IF NOT EXISTS user_books (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        folder_id INT,
        title VARCHAR(255) NOT NULL,
        file_path VARCHAR(500) NOT NULL,
        cover_image VARCHAR(500),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (folder_id) REFERENCES user_folders(id) ON DELETE CASCADE,
        INDEX idx_user_books (user_id),
        INDEX idx_folder_books (folder_id)
    )";
    $pdo->exec($sql);
    echo "User books table created.<br>";
    
    // Create recent activity table
    $sql = "CREATE TABLE IF NOT EXISTS recent_activity (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        book_id INT,
        book_type ENUM('system', 'user') NOT NULL,
        opened_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_user_activity (user_id),
        INDEX idx_opened_at (opened_at)
    )";
    $pdo->exec($sql);
    echo "Recent activity table created.<br>";
    
    // Create reading progress table
    $sql = "CREATE TABLE IF NOT EXISTS reading_progress (\n        id INT PRIMARY KEY AUTO_INCREMENT,\n        user_id INT NOT NULL,\n        book_id INT NOT NULL,\n        book_type ENUM('system', 'user') NOT NULL,\n        current_page INT DEFAULT 0,\n        total_pages INT DEFAULT 0,\n        bookmarks JSON,\n        last_read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,\n        INDEX idx_user_book (user_id, book_id, book_type)\n    )";
    $pdo->exec($sql);
    echo "Reading progress table created.<br>";
    
    // Create admin user
    $admin_password = password_hash(ADMIN_PASSWORD, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')");
    $stmt->execute([ADMIN_USERNAME, 'admin@library.com', $admin_password]);
    echo "Admin user created with username: " . ADMIN_USERNAME . " and password: " . ADMIN_PASSWORD . "<br>";
    
    echo "<br><h2>Setup completed successfully!</h2>";
    echo "<p>You can now proceed to the <a href='index.php'>main application</a>.</p>";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>