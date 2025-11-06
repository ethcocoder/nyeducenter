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

        $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
        $role = strtolower($_POST["role"]);
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        $db = new Database();
        $conn = $db->getConnection();
        
        // Initialize password reset handler
        $reset = new PasswordReset($conn);
        
        // Generate reset token
        $token = $reset->generateResetToken($email, $role);
        if (!$token) {
            throw new Exception("Failed to generate reset token");
        }
        
        // Create reset link
        $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/reset-password.php?token=" . $token;
        
        // Send email (you'll need to implement this)
        $to = $email;
        $subject = "Password Reset Request";
        $message = "Hello,\n\n";
        $message .= "You have requested to reset your password. Click the link below to reset your password:\n\n";
        $message .= $reset_link . "\n\n";
        $message .= "This link will expire in 1 hour.\n\n";
        $message .= "If you did not request this reset, please ignore this email.\n\n";
        $message .= "Best regards,\n";
        $message .= SITE_NAME;
        
        $headers = "From: " . SITE_NAME . " <noreply@" . $_SERVER['HTTP_HOST'] . ">\r\n";
        $headers .= "Reply-To: noreply@" . $_SERVER['HTTP_HOST'] . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        
        if (mail($to, $subject, $message, $headers)) {
            Util::redirect("../forgot-password.php", "success", "Password reset instructions have been sent to your email");
        } else {
            throw new Exception("Failed to send reset email");
        }

    } catch (Exception $e) {
        error_log("Password reset error: " . $e->getMessage());
        Util::redirect("../forgot-password.php", "error", $e->getMessage());
    }
} else {
    Util::redirect("../forgot-password.php", "error", "Invalid request method");
} 