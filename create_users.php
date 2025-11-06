<?php
include "Database.php";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Disable foreign key checks
    $conn->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Clear existing data
    $conn->exec("TRUNCATE TABLE `student`");
    $conn->exec("TRUNCATE TABLE `instructor`");
    $conn->exec("TRUNCATE TABLE `admin`");
    
    // Create admin
    $admin_password = password_hash("admin123", PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO `admin` (`admin_id`, `full_name`, `email`, `username`, `password`) VALUES (1, 'Admin User', 'admin@test.com', 'admin', ?)");
    $stmt->execute([$admin_password]);
    
    // Create students
    $student_password = password_hash("admin123", PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO `student` (`student_id`, `username`, `password`, `first_name`, `last_name`, `email`, `date_of_birth`, `date_of_joined`) VALUES (1, 'student1', ?, 'Test', 'Student', 'student@test.com', '2000-01-01', '2024-01-01')");
    $stmt->execute([$student_password]);
    
    $stmt = $conn->prepare("INSERT INTO `student` (`student_id`, `username`, `password`, `first_name`, `last_name`, `email`, `date_of_birth`, `date_of_joined`) VALUES (2, 'student2', ?, 'John', 'Student', 'john@test.com', '2000-01-01', '2024-01-01')");
    $stmt->execute([$student_password]);
    
    // Create instructor
    $instructor_password = password_hash("admin123", PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO `instructor` (`instructor_id`, `username`, `password`, `first_name`, `last_name`, `email`, `date_of_birth`, `date_of_joined`) VALUES (1, 'instructor1', ?, 'Test', 'Instructor', 'instructor@test.com', '1990-01-01', '2024-01-01')");
    $stmt->execute([$instructor_password]);
    
    // Re-enable foreign key checks
    $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "Users created successfully!\n";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 