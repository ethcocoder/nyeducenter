<?php
require_once 'config/database.php';

try {
    $db = getDBConnection();
    echo "Database connection successful!\n";

    // Check if student table exists
    $query = "SHOW TABLES LIKE 'student'";
    $result = $db->query($query);
    if ($result->rowCount() == 0) {
        echo "Error: student table does not exist!\n";
        exit;
    }
    echo "Student table exists!\n";

    // Show table structure
    $query = "DESCRIBE student";
    $result = $db->query($query);
    echo "\nTable structure:\n";
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }

    // Test insert
    $test_query = "INSERT INTO student (username, password, first_name, last_name, email, date_of_birth, date_of_joined, status) 
                  VALUES (?, ?, ?, ?, ?, ?, CURDATE(), 'Active')";
    $stmt = $db->prepare($test_query);
    $result = $stmt->execute([
        'test_user',
        password_hash('test123', PASSWORD_DEFAULT),
        'Test',
        'User',
        'test@example.com',
        '2000-01-01'
    ]);

    if ($result) {
        echo "\nTest insert successful! Last ID: " . $db->lastInsertId() . "\n";
    } else {
        echo "\nTest insert failed!\n";
        print_r($stmt->errorInfo());
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 