<?php 
session_start();
include "../Utils/Util.php";
include "../Utils/Validation.php";

if (isset($_SESSION['username']) && isset($_SESSION['admin_id'])) {
    $type = $_GET['for'] ?? '';
    $id = '';
    
    if ($type === 'student') {
        $id = $_GET['student_id'] ?? '';
    } else if ($type === 'instructor') {
        $id = $_GET['instructor_id'] ?? '';
    }
    
    // Debug information
    error_log("Reset Password Page - Type: " . $type);
    error_log("Reset Password Page - ID: " . $id);
    
    if (empty($type) || empty($id)) {
        $em = "Invalid request";
        Util::redirect("index.php", "error", $em);
    }

    # Header
    $title = "Reset Password - EduPulse";
    include "inc/Header.php";
?>
<div class="container">
    <!-- NavBar & Profile-->
    <?php include "inc/NavBar.php"; ?>

    <div class="p-5 shadow">
        <h4 class="">Reset <?=$type?> Password</h4><hr>
        <div id="resetPasswordMsg"></div>
        <form id="resetPasswordForm"
              method="post"
              action="Action/reset-password.php">
            <input type="hidden" name="type" value="<?=$type?>">
            <input type="hidden" name="id" value="<?=$id?>">
            
            <div class="mb-3">
                <label class="form-label">ID</label>
                <input type="text" 
                       class="form-control"
                       value="<?=$id?>"
                       readonly>
            </div>

            <div class="mb-3">
                <label for="adminPassword" class="form-label">Admin password</label>
                <input type="password" 
                       class="form-control"
                       id="adminPassword"
                       name="admin_password"
                       required>
            </div>

            <div class="mb-3">
                <label for="newPassword" class="form-label">New password</label>
                <div class="input-group">
                    <input type="password" 
                           class="form-control" 
                           id="newPassword" 
                           name="new_password"
                           placeholder="Enter new password" 
                           required>
                    <button class="btn btn-outline-secondary" 
                            type="button" 
                            id="generatePasswordButton" 
                            onclick="generatePassword()">Auto Generate</button>
                </div>
            </div>

            <div class="mb-3">
                <label for="confirmPassword" class="form-label">Confirm new password</label>
                <input type="password" 
                       class="form-control"
                       id="confirmPassword"
                       name="confirm_password"
                       required>
            </div>

            <button type="submit" class="btn btn-primary">Reset Password</button>
        </form>
    </div>
</div>

<script>
function generatePassword() {
    const randomString = Math.random().toString(36).slice(-6);
    document.getElementById('newPassword').value = randomString;
    document.getElementById('confirmPassword').value = randomString;
}

document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const adminPassword = document.getElementById('adminPassword').value;
    
    if (newPassword !== confirmPassword) {
        document.getElementById('resetPasswordMsg').innerHTML = 
            '<div class="alert alert-danger">Passwords do not match!</div>';
        return;
    }
    
    if (newPassword.length < 6) {
        document.getElementById('resetPasswordMsg').innerHTML = 
            '<div class="alert alert-danger">Password must be at least 6 characters long!</div>';
        return;
    }
    
    // Debug information
    console.log('Form submitted with type:', '<?=$type?>');
    console.log('Form submitted with id:', '<?=$id?>');
    
    this.submit();
});
</script>

<!-- Footer -->
<?php 
    include "inc/Footer.php";
} else {
    $em = "First login ";
    Util::redirect("../login.php", "error", $em);
}
?>
