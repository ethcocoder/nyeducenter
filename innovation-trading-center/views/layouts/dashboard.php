<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Innovation Trading Center Dashboard</title>
    <link href="/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f4f8fb; }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #007bff 0%, #00c6ff 100%);
            color: #fff;
            padding: 2rem 1rem 1rem 1rem;
        }
        .sidebar .nav-link {
            color: #fff;
            font-weight: 500;
            margin-bottom: 0.5rem;
            border-radius: 0.5rem;
        }
        .sidebar .nav-link.active, .sidebar .nav-link:hover {
            background: rgba(255,255,255,0.15);
            color: #fff;
        }
        .sidebar .sidebar-logo {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -1px;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        @media (max-width: 991px) {
            .sidebar { min-height: auto; padding: 1rem 0.5rem; }
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <nav class="col-lg-2 col-md-3 sidebar d-flex flex-column align-items-start">
            <?php if (isset($currentUser) && $currentUser): ?>
                <div class="w-100 text-center mb-4 p-3" style="background:rgba(255,255,255,0.10); border-radius:1rem; box-shadow:0 2px 8px rgba(0,0,0,0.04);">
                    <?php
                    $profileImg = '/assets/default-profile.png';
                    if (!empty($currentUser['profile_image'])) {
                        $profileImg = (strpos($currentUser['profile_image'], '/') === 0)
                            ? $currentUser['profile_image']
                            : '/' . $currentUser['profile_image'];
                    }
                    ?>
                    <img src="<?= htmlspecialchars($profileImg) ?>" alt="Profile Image" class="rounded-circle mb-2" style="width: 64px; height: 64px; object-fit: cover; border: 2px solid #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.10);">
                    <div style="font-weight:700; font-size:1.15rem; color:#fff; margin-bottom:0.1rem;">
                        <?= htmlspecialchars($currentUser['name'] ?? 'User') ?>
                    </div>
                    <div style="font-size:0.95rem; color:#e0eaff; opacity:0.85;">
                        <?= htmlspecialchars($currentUser['role'] ?? '') ?>
                    </div>
                </div>
            <?php endif; ?>
            <div class="sidebar-logo mb-4">
                <i class="bi bi-lightbulb"></i> Innovation Center
            </div>
            <a href="/dashboard" class="nav-link<?= strpos($_SERVER['REQUEST_URI'], '/dashboard') === 0 ? ' active' : '' ?>"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
            <a href="/innovations" class="nav-link<?= strpos($_SERVER['REQUEST_URI'], '/innovations') === 0 ? ' active' : '' ?>"><i class="bi bi-collection me-2"></i> Innovations</a>
            <a href="/messages" class="nav-link<?= strpos($_SERVER['REQUEST_URI'], '/messages') === 0 ? ' active' : '' ?>"><i class="bi bi-envelope me-2"></i> Messages</a>
            <a href="/profile" class="nav-link<?= strpos($_SERVER['REQUEST_URI'], '/profile') === 0 ? ' active' : '' ?>"><i class="bi bi-person me-2"></i> Profile</a>
            <a href="/my-innovations" class="nav-link<?= strpos($_SERVER['REQUEST_URI'], '/my-innovations') === 0 ? ' active' : '' ?>"><i class="bi bi-star me-2"></i> My Innovations</a>
            <a href="/favorites" class="nav-link<?= strpos($_SERVER['REQUEST_URI'], '/favorites') === 0 ? ' active' : '' ?>"><i class="bi bi-heart me-2"></i> Favorites</a>
            <?php if (isset($currentUser) && $currentUser['role'] === 'innovator'): ?>
                <a href="/my-sponsorships" class="nav-link<?= strpos($_SERVER['REQUEST_URI'], '/my-sponsorships') === 0 ? ' active' : '' ?>"><i class="bi bi-cash-coin me-2"></i> Sponsorships</a>
            <?php endif; ?>
            <a href="/logout" class="nav-link"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
        </nav>
        <main class="col-lg-10 col-md-9 ms-sm-auto px-4 py-4">
            <?= isset($content) ? $content : '' ?>
        </main>
    </div>
</div>
<script src="/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html> 