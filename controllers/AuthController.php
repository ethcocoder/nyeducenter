<?php
require_once 'BaseController.php';

class AuthController extends BaseController {
    // Registration page and handler
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'password_confirm' => $_POST['password_confirm'] ?? '',
                'role' => $_POST['role'] ?? 'innovator',
                'organization' => trim($_POST['organization'] ?? ''),
                'bio' => trim($_POST['bio'] ?? ''),
            ];
            $errors = $this->validate($data, [
                'name' => 'required|max:255',
                'email' => 'required|email',
                'password' => 'required|min:6',
                'password_confirm' => 'required',
            ]);
            if ($data['password'] !== $data['password_confirm']) {
                $errors['password_confirm'] = 'Passwords do not match';
            }
            if ($this->user->findByEmail($data['email'])) {
                $errors['email'] = 'Email already registered';
            }
            if (empty($errors)) {
                $userId = $this->user->createUser($data);
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_role'] = $data['role'];
                $this->redirect('/home');
            } else {
                $this->render('auth/register', [
                    'errors' => $errors,
                    'data' => $data
                ]);
                return;
            }
        }
        $this->render('auth/register');
    }

    // Login page and handler
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $errors = [];
            if (empty($email)) $errors['email'] = 'Email is required';
            if (empty($password)) $errors['password'] = 'Password is required';
            if (empty($errors)) {
                $user = $this->user->findByEmail($email);
                if (!$user) {
                    error_log('DEBUG: No user found for email: ' . $email);
                } else {
                    error_log('DEBUG: User found: ' . print_r($user, true));
                    if (password_verify($password, $user['password_hash'])) {
                        error_log('DEBUG: Password matches for user: ' . $email);
                    } else {
                        error_log('DEBUG: Password does NOT match for user: ' . $email);
                    }
                }
                $user = $this->user->verifyPassword($email, $password);
                if ($user) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_role'] = $user['role'];
                    $this->redirect('/home');
                } else {
                    $errors['general'] = 'Invalid email or password';
                }
            }
            $this->render('auth/login', [
                'errors' => $errors,
                'data' => ['email' => $email]
            ]);
            return;
        }
        $this->render('auth/login');
    }

    // Logout handler
    public function logout() {
        session_destroy();
        header('Location: /login');
        exit;
    }
}
?> 