<?php
require_once '../includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields.';
    } elseif (!validate_email($email)) {
        $error = 'Please enter a valid email address.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (!validate_password($password)) {
        $error = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long.';
    } else {
        // Check if username or email already exists
        $existing_user = get_user_by_username($username);
        $existing_email = get_user_by_email($email);
        
        if ($existing_user) {
            $error = 'Username already exists.';
        } elseif ($existing_email) {
            $error = 'Email already registered.';
        } else {
            // Create new user
            $user_id = create_user($username, $email, $password);
            if ($user_id) {
                $success = 'Account created successfully! Redirecting to login...';
                // Auto-login and redirect after 2 seconds
                echo "<script>
                    setTimeout(function() {
                        window.location.href = 'login.php';
                    }, 2000);
                </script>";
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo APP_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --success-color: #27ae60;
            --light-bg: #ecf0f1;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .register-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            max-width: 450px;
            width: 100%;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .register-header h2 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .register-header p {
            color: var(--secondary-color);
            font-size: 0.9rem;
        }

        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .btn-register {
            background: linear-gradient(45deg, var(--secondary-color), #5dade2);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-register:hover {
            background: linear-gradient(45deg, #2980b9, var(--secondary-color));
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(52, 152, 219, 0.3);
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .alert-success {
            background: linear-gradient(45deg, var(--success-color), #2ecc71);
            color: white;
        }

        .alert-danger {
            background: linear-gradient(45deg, var(--accent-color), #c0392b);
            color: white;
        }

        .text-center a {
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 600;
        }

        .text-center a:hover {
            color: var(--primary-color);
            text-decoration: underline;
        }

        .back-to-home {
            position: absolute;
            top: 2rem;
            left: 2rem;
            color: white;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .back-to-home:hover {
            color: var(--light-bg);
            transform: translateX(-5px);
        }

        .input-group-text {
            background: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 10px 0 0 10px;
        }

        .form-control {
            border-radius: 0 10px 10px 0;
        }

        .password-strength {
            height: 5px;
            border-radius: 5px;
            margin-top: 5px;
            transition: all 0.3s ease;
        }

        .strength-weak { background: var(--accent-color); }
        .strength-medium { background: var(--warning-color); }
        .strength-strong { background: var(--success-color); }

        .footer-custom {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(44, 62, 80, 0.9);
            color: white;
            text-align: center;
            padding: 0.5rem;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <a href="../index.php" class="back-to-home">
        <i class="fas fa-arrow-left me-2"></i>Back to Home
    </a>

    <div class="register-container">
        <div class="register-header">
            <div class="mb-3">
                <i class="fas fa-user-plus" style="font-size: 3rem; color: var(--secondary-color);"></i>
            </div>
            <h2>Create Account</h2>
            <p>Join our digital library community</p>
        </div>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="input-group mb-3">
                <span class="input-group-text">
                    <i class="fas fa-user"></i>
                </span>
                <input type="text" class="form-control" id="username" name="username" 
                       placeholder="Username" required 
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                       pattern="[a-zA-Z0-9_]{3,20}"
                       title="Username must be 3-20 characters and contain only letters, numbers, and underscores">
            </div>

            <div class="input-group mb-3">
                <span class="input-group-text">
                    <i class="fas fa-envelope"></i>
                </span>
                <input type="email" class="form-control" id="email" name="email" 
                       placeholder="Email Address" required
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>

            <div class="input-group mb-3">
                <span class="input-group-text">
                    <i class="fas fa-lock"></i>
                </span>
                <input type="password" class="form-control" id="password" name="password" 
                       placeholder="Password" required minlength="<?php echo PASSWORD_MIN_LENGTH; ?>"
                       oninput="checkPasswordStrength(this.value)">
            </div>
            <div id="password-strength" class="password-strength"></div>

            <div class="input-group mb-4">
                <span class="input-group-text">
                    <i class="fas fa-lock"></i>
                </span>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                       placeholder="Confirm Password" required>
            </div>

            <button type="submit" class="btn btn-register">
                <i class="fas fa-user-plus me-2"></i>
                Create Account
            </button>
        </form>

        <div class="text-center mt-4">
            <p class="mb-2">Already have an account?</p>
            <a href="login.php" class="text-decoration-none">
                <i class="fas fa-sign-in-alt me-2"></i>Sign In
            </a>
        </div>
    </div>

    <footer class="footer-custom">
        <div class="container">
            <p class="mb-0">
                <i class="fas fa-heart text-danger"></i>
                Powered by Ethco Coders 2025
            </p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        function checkPasswordStrength(password) {
            const strengthBar = document.getElementById('password-strength');
            let strength = 0;
            
            if (password.length >= 6) strength++;
            if (password.length >= 8) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            strengthBar.className = 'password-strength ';
            if (strength <= 2) {
                strengthBar.className += 'strength-weak';
            } else if (strength <= 4) {
                strengthBar.className += 'strength-medium';
            } else {
                strengthBar.className += 'strength-strong';
            }
        }

        // Add input animation
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });

        // Add loading animation to form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('.btn-register');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating Account...';
            submitBtn.disabled = true;
        });

        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>