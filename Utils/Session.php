<?php
if (!class_exists('Session')) {
class Session {
    private const SESSION_LIFETIME = 3600; // 1 hour
    private const REGENERATE_TIME = 300;   // 5 minutes

    public static function init() {
        if (session_status() === PHP_SESSION_NONE) {
            // Set secure session parameters
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', 1);
            ini_set('session.cookie_samesite', 'Strict');
            ini_set('session.gc_maxlifetime', 3600); // 1 hour
            
            session_start();
        }

        // Regenerate session ID periodically
        if (!isset($_SESSION['last_regeneration'])) {
            self::regenerate();
        } else {
            $interval = time() - $_SESSION['last_regeneration'];
            if ($interval > self::REGENERATE_TIME) {
                self::regenerate();
            }
        }
    }

    public static function regenerate() {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }

    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public static function get($key) {
        return $_SESSION[$key] ?? null;
    }

    public static function remove($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public static function destroy() {
        session_destroy();
        $_SESSION = array();
        
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
    }

    public static function isLoggedIn() {
        return isset($_SESSION['user_id']) && isset($_SESSION['role']);
    }

    public static function checkTimeout() {
        $timeout = 3600; // 1 hour
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
            self::destroy();
            return false;
        }
        $_SESSION['last_activity'] = time();
        return true;
    }

    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header("Location: /login.php");
            exit();
        }
        
        if (!self::checkTimeout()) {
            header("Location: /login.php?error=Session expired");
            exit();
        }
    }

    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function requireRole($role) {
        self::requireLogin();
        
        if ($_SESSION['role'] !== $role) {
            header("Location: /login.php?error=Unauthorized access");
            exit();
        }
    }
}
} 