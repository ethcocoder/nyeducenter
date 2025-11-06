<?php
require_once "../Utils/Session.php";
require_once "../Utils/Validation.php";
require_once "../Utils/Util.php";
require_once "../Utils/PasswordReset.php";
require_once "../Database.php";

// Initialize session
Session::init();

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    try {
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || !Session::validateCSRFToken($_POST['csrf_token'])) {
            throw new Exception("Invalid request");
        }

        $token = $_POST['token'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (empty($token)) {
            throw new Exception("Invalid reset token");
        }
        
        if (strlen($password) < 6) {
            throw new Exception("Password must be at least 6 characters long");
        }
        
        if ($password !== $confirm_password) {
            throw new Exception("Passwords do not match");
        }

        $db = new Database();
        $conn = $db->getConnection();
        
        // Initialize password reset handler
        $reset = new PasswordReset($conn);
        
        // Validate token
        $token_data = $reset->validateToken($token);
        if (!$token_data) {
            throw new Exception("Invalid or expired reset token");
        }
        
        // Update password
        if (!$reset->updatePassword($token_data['email'], $token_data['role'], $password)) {
            throw new Exception("Failed to update password");
        }
        
        // Mark token as used
        $reset->markTokenAsUsed($token);
        
        // Clean up expired tokens
        $reset->cleanupExpiredTokens();
        
        Util::redirect("../login.php", "success", "Password has been reset successfully. Please login with your new password.");

    } catch (Exception $e) {
        error_log("Password reset error: " . $e->getMessage());
        Util::redirect("../reset-password.php?token=" . urlencode($token), "error", $e->getMessage());
    }
} else {
    Util::redirect("../login.php", "error", "Invalid request method");
} 