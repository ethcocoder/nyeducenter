<?php
require_once "Utils/Session.php";
require_once "Utils/Validation.php";
require_once "Utils/Util.php";
require_once "Config.php";

// Initialize session
Session::init();

// Generate CSRF token
$csrf_token = Session::generateCSRFToken();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password - <?=SITE_NAME?></title>
    <link rel="stylesheet" type="text/css" href="Assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="Assets/css/login-signup.css">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .forgot-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #343a40;
        }
        .forgot-card {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 100%;
            max-width: 400px;
        }
        .forgot-card .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .forgot-card .logo img {
            max-width: 120px;
            margin-bottom: 10px;
        }
        .forgot-card .logo h2 {
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
    <div class="forgot-container">
        <div class="forgot-card">
            <div class="logo">
                <img src="assets/img/Logo.png" alt="Logo">
                <h2>Forgot Password</h2>
            </div>
            
            <?php if (isset($_GET['error'])) { ?>
                <p class="error"><?=htmlspecialchars($_GET['error'])?></p>
            <?php } ?>
            
            <?php if (isset($_GET['success'])) { ?>
                <p class="success"><?=htmlspecialchars($_GET['success'])?></p>
            <?php } ?>

            <form action="Action/forgot-password.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?=$csrf_token?>">
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email"
                           class="form-control"
                           id="email"
                           name="email"
                           placeholder="Enter your email"
                           required
                           autocomplete="email">
                </div>
                
                <div class="mb-3">
                    <label for="role" class="form-label">Role</label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="student">Student</option>
                        <option value="instructor">Instructor</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 mb-3">Reset Password</button>
                
                <div class="d-flex justify-content-between">
                    <a href="login.php" class="btn btn-link">Back to Login</a>
                    <a href="index.php" class="btn btn-link">Home</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html> 