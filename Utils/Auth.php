<?php
/**
 * Utility functions for authentication and authorization
 */

/**
 * Require specific roles to access a page
 * @param array $allowed_roles Array of allowed roles
 * @return void
 */
function requireRole($allowed_roles) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if user is logged in
    if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
        header("Location: ../login.php");
        exit();
    }
    
    // Check if user's role is allowed
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        header("Location: ../unauthorized.php");
        exit();
    }
}
?> 