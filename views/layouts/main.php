<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    <link href="/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        <?php if (!$useGlassNav): ?>
        body { background: #f8f9fa; }
        <?php else: ?>
        body, html { background: transparent !important; }
        nav.navbar.navbar-glass,
        nav.navbar.navbar-glass .container-fluid,
        nav.navbar.navbar-glass .navbar-collapse,
        nav.navbar.navbar-glass .navbar-nav {
            background: rgba(255, 255, 255, 0.1) !important;
            box-shadow: 0 2px 16px 0 rgba(0,0,0,0.10) !important;
            border-bottom: 1.5px solid rgba(255,255,255,0.13) !important;
        }
        nav.navbar.navbar-glass {
            backdrop-filter: blur(12px);
            
            position: absolute;
            top: 0; left: 0; right: 0;
            z-index: 1000;
        }
        <?php endif; ?>
        .navbar-brand {
            font-weight: 900;
            font-size: 1.45rem;
            letter-spacing: 0.5px;
            color: blue;
            text-shadow: 0 1px 4px rgba(0,0,0,0.12);
        }
        .navbar-nav .nav-link {
            color: blue ;
            font-weight: 600;
            margin-left: 0.7rem;
            margin-right: 0.2rem;
            transition: color 0.18s, background 0.18s, border 0.18s;
            border-radius: 1.2rem;
            padding: 0.35rem 1.1rem;
            text-shadow: 0 1px 4px rgba(0,0,0,0.12);
        }
        .navbar-nav .nav-link:hover, .navbar-nav .nav-link:focus {
            color: #fff !important;
            background: rgba(0,123,255,0.13);
        }
        .navbar-nav .nav-link.register-link {
            background: linear-gradient(90deg, #007bff 0%, #00c6ff 100%);
            color: #fff !important;
            font-weight: 700;
            margin-left: 1.1rem;
            box-shadow: 0 2px 12px 0 rgba(0,123,255,0.10);
            border-radius: 2rem;
            padding: 0.35rem 1.4rem;
        }
        .navbar-nav .nav-link.register-link:hover, .navbar-nav .nav-link.register-link:focus {
            background: linear-gradient(90deg, #00c6ff 0%, #007bff 100%);
            color: #fff !important;
            box-shadow: 0 4px 24px 0 rgba(0,123,255,0.18);
        }
    </style>
</head>
<body class="<?php if (
    $isHome) echo 'home-page';
    else if ($isAuth) echo 'auth-page';
    else if ($isAbout) echo 'about-page';
    else if ($isContact) echo 'contact-page';
?>">
<?php
$isHome = ($_SERVER['REQUEST_URI'] === '/' || $_SERVER['REQUEST_URI'] === '/home');
$isAuth = ($_SERVER['REQUEST_URI'] === '/login' || $_SERVER['REQUEST_URI'] === '/register');
$isAbout = ($_SERVER['REQUEST_URI'] === '/about');
$isContact = ($_SERVER['REQUEST_URI'] === '/contact');
$useGlassNav = $isHome || $isAuth;
?>
<nav class="navbar navbar-expand-lg <?php echo $useGlassNav ? 'navbar-glass' : 'navbar-dark bg-primary'; ?>">
    <div class="container-fluid">
        <a class="navbar-brand" href="/home">Innovation Trading Center</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item"><a class="nav-link" href="/home">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="/about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="/contact">Contact</a></li>
                    <li class="nav-item"><a class="nav-link" href="/profile">Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="/innovations">Innovations</a></li>
                    <li class="nav-item"><a class="nav-link" href="/messages">Messages</a></li>
                    <li class="nav-item"><a class="nav-link" href="/messages/send?contact_admin=1">Contact Admin</a></li>
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="/admin">Admin</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="/logout">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="/home">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="/about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="/contact">Contact</a></li>
                    <li class="nav-item"><a class="nav-link" href="/login">Login</a></li>
                    <li class="nav-item"><a class="nav-link register-link" href="/register">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<?php if ($isHome || $isAuth || $isAbout || $isContact): ?>
    <?php if (isset($_SESSION['flash'])): ?>
        <div class="container mt-4">
            <div class="alert alert-<?= $_SESSION['flash']['type'] ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['flash']['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['flash']); ?>
        </div>
    <?php endif; ?>
    <?= $content ?>
<?php else: ?>
    <div class="container">
        <?php if (isset($_SESSION['flash'])): ?>
            <div class="alert alert-<?= $_SESSION['flash']['type'] ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['flash']['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>
        <?= $content ?>
    </div>
<?php endif; ?>
<script src="/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html> 