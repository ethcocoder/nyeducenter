<?php
$password = "admin123";
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Password: " . $password . "\n";
echo "Hash: " . $hash . "\n";

// Verify the hash
if (password_verify($password, $hash)) {
    echo "Hash verification successful!\n";
} else {
    echo "Hash verification failed!\n";
}
?> 