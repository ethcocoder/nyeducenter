<?php
session_start();
require_once 'config/database.php';
require_once 'config/config.php';

// Simple routing
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);

// Remove trailing slash
$path = rtrim($path, '/');

// Default route
if ($path === '' || $path === '/') {
    $path = '/home';
}

// Match /innovations/{id}
if (preg_match('#^/innovations/(\d+)$#', $path, $matches)) {
    require 'controllers/InnovationController.php';
    $controller = new InnovationController();
    $controller->show($matches[1]);
    exit;
}

// Match /innovations/{id}/edit
if (preg_match('#^/innovations/(\d+)/edit$#', $path, $matches)) {
    require 'controllers/InnovationController.php';
    $controller = new InnovationController();
    $controller->edit($matches[1]);
    exit;
}

// Match /innovations/{id}/toggle-status
if (preg_match('#^/innovations/(\d+)/toggle-status$#', $path, $matches)) {
    require 'controllers/InnovationController.php';
    $controller = new InnovationController();
    $controller->toggleStatus($matches[1]);
    exit;
}

// Match /innovations/{id}/favorite
if (preg_match('#^/innovations/(\d+)/favorite$#', $path, $matches)) {
    require 'controllers/InnovationController.php';
    $controller = new InnovationController();
    $controller->favorite($matches[1]);
    exit;
}

// Match /innovations/{id}/sponsor-form (GET)
if (preg_match('#^/innovations/(\d+)/sponsor-form$#', $path, $matches)) {
    require 'controllers/InnovationController.php';
    $controller = new InnovationController();
    $controller->sponsorForm($matches[1]);
    exit;
}

// Match /innovations/{id}/sponsor (POST)
if (preg_match('#^/innovations/(\d+)/sponsor$#', $path, $matches) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require 'controllers/InnovationController.php';
    $controller = new InnovationController();
    $controller->sponsorInnovation($matches[1]);
    exit;
}

// Route for innovator's sponsorships page
if ($path === '/my-sponsorships') {
    require 'controllers/InnovationController.php';
    $controller = new InnovationController();
    $controller->mySponsorships();
    exit;
}

// Route for updating sponsorship status
if ($path === '/update-sponsorship-status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require 'controllers/InnovationController.php';
    $controller = new InnovationController();
    $controller->updateSponsorshipStatus();
    exit;
}

// Route to appropriate controller
switch ($path) {
    case '/home':
        require 'controllers/HomeController.php';
        $controller = new HomeController();
        $controller->index();
        break;
        
    case '/login':
        require 'controllers/AuthController.php';
        $controller = new AuthController();
        $controller->login();
        break;
        
    case '/register':
        require 'controllers/AuthController.php';
        $controller = new AuthController();
        $controller->register();
        break;
        
    case '/innovations':
        require 'controllers/InnovationController.php';
        $controller = new InnovationController();
        $controller->index();
        break;
        
    case '/innovations/create':
        require 'controllers/InnovationController.php';
        $controller = new InnovationController();
        $controller->create();
        break;
        
    case '/innovations/store':
        require 'controllers/InnovationController.php';
        $controller = new InnovationController();
        $controller->store();
        break;
        
    case '/profile':
        require 'controllers/ProfileController.php';
        $controller = new ProfileController();
        $controller->index();
        break;
        
    case '/profile/edit':
        require 'controllers/ProfileController.php';
        $controller = new ProfileController();
        $controller->edit();
        break;
    case '/profile/update':
        require 'controllers/ProfileController.php';
        $controller = new ProfileController();
        $controller->update();
        break;
        
    case '/admin':
        require 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->index();
        break;
    case '/admin/users':
        require 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->users();
        break;
    case '/admin/user/view':
        require 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->userView();
        break;
    case '/admin/user/toggle':
        require 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->userToggleStatus();
        break;
    case '/admin/user/delete':
        require 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->userDelete();
        break;
    case '/admin/innovations':
        require 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->innovations();
        break;
    case '/admin/innovation/toggle':
        require 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->innovationToggleStatus();
        break;
    case '/admin/innovation/delete':
        require 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->innovationDelete();
        break;
    case '/admin/messages':
        require 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->messages();
        break;
    case '/admin/message/view':
        require 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->messageView();
        break;
    case '/admin/message/delete':
        require 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->messageDelete();
        break;
    case '/about':
        require_once 'controllers/PageController.php';
        (new PageController)->show('about');
        exit;
    case '/contact':
        require_once 'controllers/PageController.php';
        (new PageController)->show('contact');
        exit;
    case '/messages':
        require 'controllers/MessageController.php';
        $controller = new MessageController();
        $controller->inbox();
        break;
    case '/messages/sent':
        require 'controllers/MessageController.php';
        $controller = new MessageController();
        $controller->sent();
        break;
    case '/messages/inbox':
        require 'controllers/MessageController.php';
        $controller = new MessageController();
        $controller->inbox();
        break;
    case '/messages/send':
        require 'controllers/MessageController.php';
        $controller = new MessageController();
        $controller->send();
        break;
    case '/messages/group/create':
        require 'controllers/MessageController.php';
        $controller = new MessageController();
        $controller->createGroup();
        break;
    case '/logout':
        require 'controllers/AuthController.php';
        $controller = new AuthController();
        $controller->logout();
        break;
    case '/dashboard':
        require 'controllers/HomeController.php';
        $controller = new HomeController();
        $controller->dashboard();
        break;
    case '/messages/conversation':
        require 'controllers/MessageController.php';
        $controller = new MessageController();
        $controller->conversation();
        break;
    case '/my-innovations':
        require 'controllers/InnovationController.php';
        $controller = new InnovationController();
        $controller->myInnovations();
        break;
    case '/favorites':
        require 'controllers/InnovationController.php';
        $controller = new InnovationController();
        $controller->favorites();
        break;
        
    default:
        http_response_code(404);
        require 'views/404.php';
        break;
}
?> 