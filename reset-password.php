<?php
require_once "Utils/Session.php";
require_once "Utils/Validation.php";
require_once "Utils/Util.php";
require_once "Utils/PasswordReset.php";
require_once "Database.php";

// Initialize session
Session::init();

// Get token from URL
$token = isset($_GET['token']) ? $_GET['token'] : '';

if (empty($token)) {
    Util::redirect("login.php", "error", "Invalid reset token");
}

// Validate token
$db = new Database();
$conn = $db->getConnection();
$reset = new PasswordReset($conn);
$token_data = $reset->validateToken($token);

if (!$token_data) {
    Util::redirect("login.php", "error", "Invalid or expired reset token");
}

// Generate CSRF token
$csrf_token = Session::generateCSRFToken();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password - <?=SITE_NAME?></title>
    <link rel="stylesheet" type="text/css" href="Assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="Assets/css/login-signup.css">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .reset-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #343a40;
        }
        .reset-card {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 100%;
            max-width: 400px;
        }
        .reset-card .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .reset-card .logo img {
            max-width: 120px;
            margin-bottom: 10px;
        }
        .reset-card .logo h2 {
            font-size: 1.8rem;
            color: #343a40;
            margin-top: 10px;
        }
        .error {
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        .success {
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-card">
            <div class="logo">
                <img src="assets/img/Logo.png" alt="Logo">
                <h2>Reset Password</h2>
            </div>
            
            <?php if (isset($_GET['error'])) { ?>
                <p class="error"><?=htmlspecialchars($_GET['error'])?></p>
            <?php } ?>
            
            <?php if (isset($_GET['success'])) { ?>
                <p class="success"><?=htmlspecialchars($_GET['success'])?></p>
            <?php } ?>

            <form action="Action/reset-password.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?=$csrf_token?>">
                <input type="hidden" name="token" value="<?=htmlspecialchars($token)?>">
                
                <div class="mb-3">
                    <label for="password" class="form-label">New Password</label>
                    <input type="password"
                           class="form-control"
                           id="password"
                           name="password"
                           placeholder="Enter new password"
                           required
                           minlength="6"
                           autocomplete="new-password">
                </div>
                
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password"
                           class="form-control"
                           id="confirm_password"
                           name="confirm_password"
                           placeholder="Confirm new password"
                           required
                           minlength="6"
                           autocomplete="new-password">
                </div>
                
                <button type="submit" class="btn btn-primary w-100 mb-3">Reset Password</button>
                
                <div class="d-flex justify-content-between">
                    <a href="login.php" class="btn btn-link">Back to Login</a>
                    <a href="index.php" class="btn btn-link">Home</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Password validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
            }
        });
    </script>
</body>
</html> 