<?php
require_once "Utils/Session.php";
require_once "Utils/Validation.php";
require_once "Utils/Util.php";

// Initialize session
Session::init();

// Generate CSRF token
$csrf_token = Session::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Login - Online Learning System</title>
	<!-- Add security headers -->
	<meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:;">
	<meta http-equiv="X-XSS-Protection" content="1; mode=block">
	<meta http-equiv="X-Content-Type-Options" content="nosniff">
	<meta http-equiv="X-Frame-Options" content="DENY">
	<link rel="stylesheet" href="css/style.css">
	<link rel="stylesheet"
	      type="text/css"
	      href="Assets/css/bootstrap.min.css">
	<link rel="stylesheet"
	      type="text/css"
	      href="Assets/css/login-signup.css">
	<link rel="icon" type="image/x-icon" href="favicon.ico">
    <style>
        body {
            background-color: #f8f9fa; /* Light gray background */
        }
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #343a40; /* Dark background for the overall container */
        }
        .login-card {
            background-color: #ffffff; /* White background for the card */
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 100%;
            max-width: 400px;
        }
        .login-card .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .login-card .logo img {
            max-width: 120px;
            margin-bottom: 10px;
        }
        .login-card .logo h2 {
            font-size: 1.8rem;
            color: #343a40;
            margin-top: 10px;
        }
        .form-group label {
            font-weight: 500;
            margin-bottom: 5px;
            display: block;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .form-group button {
            width: 100%;
            padding: 12px;
            border-radius: 5px;
            font-size: 1.1rem;
            margin-top: 10px;
        }
        .form-group a {
            display: inline-block;
            margin-top: 15px;
            color: #007bff;
            text-decoration: none;
        }
        .form-group a:hover {
            text-decoration: underline;
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
	<div class="container">
		<div class="login-form">
			<h2>Login</h2>
			
			<?php if (isset($_SESSION['error'])) { ?>
				<div class="alert alert-danger">
					<?php 
						echo htmlspecialchars($_SESSION['error']);
						unset($_SESSION['error']);
					?>
				</div>
			<?php } ?>

			<?php if (isset($_SESSION['success'])) { ?>
				<div class="alert alert-success">
					<?php 
						echo htmlspecialchars($_SESSION['success']);
						unset($_SESSION['success']);
					?>
				</div>
			<?php } ?>

    <div class="login-container">
        <div class="login-card">
            <div class="logo">
                <img src="assets/img/Logo.png" alt="Logo">
                <h2>SIGN IN</h2>
            </div>
            <?php
                if (isset($_GET['error'])) { ?>
                    <p class="error"><?=htmlspecialchars($_GET['error'])?></p>
            <?php } 
                if (isset($_GET['success'])) { ?>
                    <p class="success"><?=htmlspecialchars($_GET['success'])?></p>
            <?php } ?>

            <form class="form"
                  action="Action/login.php"
                  method="POST">
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
                    <label for="password" class="form-label">Password</label>
                    <input type="password"
                           class="form-control"
                           id="password"
                           name="password"
                           placeholder="Enter your password"
                           required
                           autocomplete="current-password">
                </div>
                <div class="mb-3">
                    <label for="role" class="form-label">Role</label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="student">Student</option>
                        <option value="instructor">Instructor</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-100 mb-3">Login</button>
                <div class="d-flex justify-content-between">
                    <a href="signup.php" class="btn btn-link">Sign Up</a>
                    <a href="index.php" class="btn btn-link">Home</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>