<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$target_dir = '../../assets/Upload/profile/';
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] === UPLOAD_ERR_OK) {
    $file_tmp = $_FILES['profile_img']['tmp_name'];
    $file_name = basename($_FILES['profile_img']['name']);
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($file_ext, $allowed)) {
        header('Location: ../Profile.php?error=Invalid file type');
        exit();
    }
    $new_name = 'admin_' . $_SESSION['admin_id'] . '_' . time() . '.' . $file_ext;
    $target_file = $target_dir . $new_name;
    if (move_uploaded_file($file_tmp, $target_file)) {
        $_SESSION['admin_profile_img'] = $new_name;
        header('Location: ../Profile.php?success=Profile image updated');
        exit();
    } else {
        header('Location: ../Profile.php?error=Upload failed');
        exit();
    }
} else {
    header('Location: ../Profile.php?error=No file uploaded');
    exit();
} 