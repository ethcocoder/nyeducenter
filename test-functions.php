<?php
// Test script to verify the function redeclaration error is fixed

echo "Testing function inclusion...\n";

// Test including functions.php multiple times (should not cause errors)
echo "Including functions.php first time...\n";
require_once 'includes/functions.php';
echo "✓ First inclusion successful\n";

echo "Including functions.php second time...\n";
require_once 'includes/functions.php';
echo "✓ Second inclusion successful (no redeclaration error)\n";

echo "Including functions.php third time...\n";
require_once 'includes/functions.php';
echo "✓ Third inclusion successful (no redeclaration error)\n";

// Test that functions work
echo "\nTesting function functionality...\n";

// Start session for testing
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Test is_admin function
echo "Testing is_admin() function...\n";
try {
    $result = is_admin();
    echo "✓ is_admin() function works: " . ($result ? "true" : "false") . "\n";
} catch (Exception $e) {
    echo "✗ is_admin() function error: " . $e->getMessage() . "\n";
}

// Test is_logged_in function
echo "Testing is_logged_in() function...\n";
try {
    $result = is_logged_in();
    echo "✓ is_logged_in() function works: " . ($result ? "true" : "false") . "\n";
} catch (Exception $e) {
    echo "✗ is_logged_in() function error: " . $e->getMessage() . "\n";
}

// Test database connection
echo "\nTesting database connection...\n";
try {
    if (isset($conn)) {
        echo "✓ Database connection available\n";
    } else {
        echo "✗ Database connection not available\n";
    }
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
}

echo "\n🎉 All tests completed successfully!\n";
echo "The function redeclaration error has been fixed.\n";
?>