<?php
require_once 'BaseController.php';

class ProfileController extends BaseController {
    public function index() {
        $user = $this->getCurrentUser();
        if (!$user) {
            $this->redirect('/login');
        }
        $this->render('profile', [
            'user' => $user
        ], 'layouts/dashboard');
    }

    public function edit() {
        $user = $this->getCurrentUser();
        if (!$user) {
            $this->redirect('/login');
        }
        $this->render('profile_edit', [
            'user' => $user
        ], 'layouts/dashboard');
    }

    public function update() {
        $user = $this->getCurrentUser();
        if (!$user) {
            $this->redirect('/login');
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/profile/edit');
        }
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'organization' => trim($_POST['organization'] ?? ''),
            'bio' => trim($_POST['bio'] ?? ''),
        ];
        $errors = $this->validate($data, [
            'name' => 'required|max:255',
            'email' => 'required|email',
        ]);
        // Password change (optional)
        if (!empty($_POST['password'])) {
            if (strlen($_POST['password']) < 6) {
                $errors['password'] = 'Password must be at least 6 characters';
            } elseif ($_POST['password'] !== ($_POST['password_confirm'] ?? '')) {
                $errors['password_confirm'] = 'Passwords do not match';
            } else {
                $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }
        }
        // Email uniqueness check (if changed)
        if ($data['email'] !== $user['email'] && $this->user->findByEmail($data['email'])) {
            $errors['email'] = 'Email already in use';
        }
        // Handle profile image upload
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $uploadedFile = $this->uploadFile($_FILES['profile_image'], 'uploads/profiles');
            if ($uploadedFile) {
                $data['profile_image'] = $uploadedFile;
            } else {
                $errors['profile_image'] = 'Failed to upload profile image.';
            }
        }
        if (empty($errors)) {
            $this->user->update($user['id'], $data);
            $this->setFlash('success', 'Profile updated successfully!');
            $this->redirect('/profile');
        } else {
            $this->render('profile_edit', [
                'errors' => $errors,
                'user' => array_merge($user, $data)
            ]);
        }
    }
} 