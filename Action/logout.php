<?php
require_once "../Utils/Session.php";

// Initialize session
Session::init();

// Clear all session data
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: ../login.php?success=Logged out successfully");
exit();
?> 