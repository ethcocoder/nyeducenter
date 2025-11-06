<?php
session_start();

// Check if the user is logged in and has an admin role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Redirect to login page if not authenticated or not an admin
    header('Location: admin_login.html'); // Assuming admin_login.html is your login page
    exit();
}

// Optionally, you can add more session validation here, like checking session expiry

?>