<?php
require_once '../includes/functions.php';
require_login();

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

global $conn;
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($email)) {
        $message = 'Email address is required.';
        $message_type = 'danger';
    } elseif (!validate_email($email)) {
        $message = 'Please enter a valid email address.';
        $message_type = 'danger';
    } else {
        try {
            $stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
            $stmt->execute([$email, $user_id]);
            $message = 'Profile updated successfully!';
            $message_type = 'success';
            
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
        } catch (PDOException $e) {
            $message = 'Error updating profile.';
            $message_type = 'danger';
        }
        
        if (!empty($current_password) && !empty($new_password)) {
            if (!verify_password($current_password, $user['password'])) {
                $message = 'Current password is incorrect.';
                $message_type = 'danger';
            } elseif ($new_password !== $confirm_password) {
                $message = 'New passwords do not match.';
                $message_type = 'danger';
            } elseif (strlen($new_password) < 6) {
                $message = 'New password must be at least 6 characters.';
                $message_type = 'danger';
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                try {
                    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$hashed_password, $user_id]);
                    $message = 'Profile and password updated!';
                    $message_type = 'success';
                } catch (PDOException $e) {
                    $message = 'Error updating password.';
                    $message_type = 'danger';
                }
            }
        }
    }
}

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM user_books WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_books = $stmt->fetch()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM user_folders WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_folders = $stmt->fetch()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM recent_activity WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_activities = $stmt->fetch()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - <?php echo APP_NAME; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #e74c3c;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --light-bg: #ecf0f1;
            --dark-text: #2c3e50;
            --highlight-color: #3498db;
            --text-light: #ecf0f1;
            --text-muted: #bdc3c7;
            --gradient-primary: linear-gradient(135deg, #2c3e50, #34495e);
            --gradient-secondary: linear-gradient(135deg, #34495e, #2c3e50);
            --gradient-accent: linear-gradient(135deg, #e74c3c, #c0392b);
            --shadow-light: 0 4px 10px rgba(0, 0, 0, 0.05);
            --shadow-medium: 0 8px 25px rgba(0, 0, 0, 0.1);
            --shadow-heavy: 0 15px 35px rgba(0, 0, 0, 0.15);
            --bottom-nav-height: 60px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--light-bg);
            color: var(--dark-text);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar-modern {
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-bottom: none;
            padding: 1rem 0;
        }

        .navbar-brand-modern {
            font-weight: 700;
            font-size: 1.75rem;
            color: white !important;
            background: none;
            -webkit-background-clip: unset;
            -webkit-text-fill-color: unset;
            background-clip: unset;
        }

        .profile-header {
            background: var(--primary-color);
            padding: 4rem 0 2rem;
            text-align: center;
            color: white;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: var(--secondary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
            margin: 0 auto 1rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .profile-name {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .profile-role {
            font-size: 1.1rem;
            opacity: 0.9;
            font-weight: 500;
        }

        .stats-section {
            padding: 3rem 0;
            flex: 1;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            border: 1px solid rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
            transition: transform 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: white;
            margin: 0 auto 1rem;
        }

        .stat-icon.books { background: var(--primary-color); }
        .stat-icon.folders { background: var(--success-color); }
        .stat-icon.activity { background: var(--warning-color); }

        .stat-number {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }

        .stat-label {
            color: var(--dark-text);
            font-weight: 500;
        }

        .profile-card {
            background: white;
            border-radius: 15px;
            padding: 2.5rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .form-label {
            color: var(--dark-text);
            font-weight: 600;
        }

        .form-control {
            background: #f8f9fa;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            padding: 0.75rem;
            color: var(--dark-text);
        }

        .form-control:focus {
            background: #ffffff;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(44, 62, 80, 0.25);
            color: var(--dark-text);
        }

        .btn-modern {
            background: linear-gradient(135deg, var(--success-color), #2ecc71);
            border: none;
            border-radius: 10px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            color: white;
        }

        .btn-outline-modern {
            background: transparent;
            border: 2px solid var(--secondary-color);
            border-image: none;
            border-radius: 10px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            color: var(--secondary-color);
            transition: all 0.3s ease;
        }

        .btn-outline-modern:hover {
            background: var(--secondary-color);
            color: white;
            transform: translateY(-2px);
            border-color: var(--secondary-color);
        }

        .alert {
            border-radius: 8px;
            border: none;
        }

        .alert-success {
            background: rgba(39, 174, 96, 0.1);
            color: #27ae60;
            border: 1px solid rgba(39, 174, 96, 0.2);
        }

        .alert-danger {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
            border: 1px solid rgba(231, 76, 60, 0.2);
        }

        .footer-custom {
            background: var(--primary-color);
            color: white;
            text-align: center;
            padding: 2rem 0;
            margin-top: auto;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        @media (max-width: 768px) {
            .profile-name {
                font-size: 1.8rem;
            }
            
            .profile-card {
                padding: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-modern">
        <div class="container">
            <a class="navbar-brand navbar-brand-modern" href="../index.php">
            <img src="../assets/images/logo.svg" alt="Logo" style="height: 40px; margin-right: 10px;">
            <?php echo APP_NAME; ?>
        </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link text-light" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light active" href="profile.php">
                            <i class="fas fa-user me-1"></i> Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" href="logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Profile Header -->
    <section class="profile-header">
        <div class="container">
            <div class="profile-avatar">
                <?php echo strtoupper(substr($username, 0, 1)); ?>
            </div>
            <h1 class="profile-name"><?php echo htmlspecialchars($username); ?></h1>
            <p class="profile-role">
                <i class="fas fa-user-shield me-2"></i>
                <?php echo htmlspecialchars($_SESSION['role'] ?? 'User'); ?>
            </p>
        </div>
    </section>

    <!-- Main Content -->
    <main class="stats-section">
        <div class="container">
            <!-- Statistics -->
            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon books">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="stat-number"><?php echo $total_books; ?></div>
                        <div class="stat-label">Books Uploaded</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon folders">
                            <i class="fas fa-folder"></i>
                        </div>
                        <div class="stat-number"><?php echo $total_folders; ?></div>
                        <div class="stat-label">Folders Created</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon activity">
                            <i class="fas fa-history"></i>
                        </div>
                        <div class="stat-number"><?php echo $total_activities; ?></div>
                        <div class="stat-label">Recent Activities</div>
                    </div>
                </div>
            </div>

            <!-- Profile Form -->
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="profile-card">
                <h3 class="mb-4 text-center">
                    <i class="fas fa-cog me-2"></i>Profile Settings
                </h3>
                <form method="POST" action="profile.php">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($username); ?>" disabled>
                            <small class="text-muted">Username cannot be changed</small>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" placeholder="Leave blank to keep current password">
                        </div>
                        <div class="col-md-4">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Min 6 characters">
                        </div>
                        <div class="col-md-4">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
                        </div>
                        <div class="col-12">
                            <div class="d-flex gap-3 justify-content-center">
                                <button type="submit" class="btn-modern">
                                    <i class="fas fa-save me-2"></i>Update Profile
                                </button>
                                <a href="dashboard.php" class="btn btn-outline-modern">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer-custom">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
            <p>Your Digital Library Management System</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword && newPassword !== confirmPassword) {
                e.preventDefault();
                alert('New passwords do not match!');
                return false;
            }
            
            if (newPassword && newPassword.length < 6) {
                e.preventDefault();
                alert('New password must be at least 6 characters long!');
                return false;
            }
        });
    </script>
</body>
</html>