<?php
require_once 'models/User.php';
require_once 'models/Innovation.php';
require_once 'models/Category.php';
require_once 'models/Message.php';

abstract class BaseController {
    protected $user;
    protected $innovation;
    protected $category;
    protected $message;
    
    public function __construct() {
        $this->user = new User();
        $this->innovation = new Innovation();
        $this->category = new Category();
        $this->message = new Message();
    }
    
    protected function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    protected function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: /login');
            exit;
        }
    }
    
    protected function requireRole($role) {
        $this->requireLogin();
        
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== $role) {
            header('Location: /home');
            exit;
        }
    }
    
    protected function requireAdmin() {
        $this->requireRole('admin');
    }
    
    protected function requireInnovator() {
        $this->requireRole('innovator');
    }
    
    protected function requireSponsor() {
        $this->requireRole('sponsor');
    }
    
    protected function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return $this->user->find($_SESSION['user_id']);
    }
    
    protected function render($view, $data = [], $layout = null) {
        // Extract data to variables
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include the view file
        $viewPath = "views/{$view}.php";
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            throw new Exception("View {$view} not found");
        }
        
        // Get the content and clean the buffer
        $content = ob_get_clean();
        
        // Decide which layout to use
        if ($layout) {
            // Fix: Remove any leading 'layouts/' from the layout name
            $layout = preg_replace('#^layouts/#', '', $layout);
            include "views/layouts/{$layout}.php";
        } else if ($this->isLoggedIn() && $this->isDashboardPage($view)) {
            include 'views/layouts/dashboard.php';
        } else {
            include 'views/layouts/main.php';
        }
    }
    
    protected function renderPartial($view, $data = []) {
        // Extract data to variables
        extract($data);
        
        // Include the view file directly
        $viewPath = "views/{$view}.php";
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            throw new Exception("View {$view} not found");
        }
    }
    
    protected function redirect($url) {
        header("Location: {$url}");
        exit;
    }
    
    protected function json($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    protected function validate($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? '';
            
            if (strpos($rule, 'required') !== false && empty($value)) {
                $errors[$field] = ucfirst($field) . ' is required';
                continue;
            }
            
            if (!empty($value)) {
                if (strpos($rule, 'email') !== false && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = 'Invalid email format';
                }
                
                if (strpos($rule, 'min:') !== false) {
                    preg_match('/min:(\d+)/', $rule, $matches);
                    $min = $matches[1];
                    if (strlen($value) < $min) {
                        $errors[$field] = ucfirst($field) . " must be at least {$min} characters";
                    }
                }
                
                if (strpos($rule, 'max:') !== false) {
                    preg_match('/max:(\d+)/', $rule, $matches);
                    $max = $matches[1];
                    if (strlen($value) > $max) {
                        $errors[$field] = ucfirst($field) . " must not exceed {$max} characters";
                    }
                }
            }
        }
        
        return $errors;
    }
    
    protected function uploadFile($file, $directory = 'uploads') {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return false;
        }
        
        // Create directory if it doesn't exist
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $filepath = $directory . '/' . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return $filepath;
        }
        
        return false;
    }
    
    protected function setFlash($type, $message) {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }
    
    protected function getFlash() {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $flash;
    }
    
    protected function hasFlash() {
        return isset($_SESSION['flash']);
    }
    
    // Helper to determine if a view is a dashboard page
    protected function isDashboardPage($view) {
        return (
            strpos($view, 'dashboard/') === 0 ||
            strpos($view, 'innovations/') === 0 ||
            strpos($view, 'messages/') === 0 ||
            $view === 'profile' ||
            $view === 'profile_edit'
        );
    }
}
?> 