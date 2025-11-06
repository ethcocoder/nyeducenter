<?php /** @var array $user */ ?>
<?php /** @var array $errors */ ?>
<div class="container mt-5">
    <h2>Edit Profile</h2>
    <?php if (!empty(
$errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <form method="post" action="/profile/update" enctype="multipart/form-data">
        <div class="mb-3 text-center">
            <label class="form-label">Profile Image</label><br>
            <?php
            $profileImg = '/assets/default-profile.png';
            if (!empty($user['profile_image'])) {
                $profileImg = (strpos($user['profile_image'], '/') === 0)
                    ? $user['profile_image']
                    : '/' . $user['profile_image'];
            }
            ?>
            <img src="<?= htmlspecialchars($profileImg) ?>" alt="Profile Image" class="rounded-circle mb-2" style="width: 100px; height: 100px; object-fit: cover; border: 2px solid #eee;">
            <input type="file" class="form-control mt-2" name="profile_image" accept="image/*">
        </div>
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required maxlength="255">
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required maxlength="255">
        </div>
        <div class="mb-3">
            <label for="organization" class="form-label">Organization</label>
            <input type="text" class="form-control" id="organization" name="organization" value="<?= htmlspecialchars($user['organization'] ?? '') ?>" maxlength="255">
        </div>
        <div class="mb-3">
            <label for="bio" class="form-label">Bio</label>
            <textarea class="form-control" id="bio" name="bio" rows="4" maxlength="1000"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
        </div>
        <hr>
        <h5>Change Password <small class="text-muted">(leave blank to keep current password)</small></h5>
        <div class="mb-3">
            <label for="password" class="form-label">New Password</label>
            <input type="password" class="form-control" id="password" name="password" minlength="6">
        </div>
        <div class="mb-3">
            <label for="password_confirm" class="form-label">Confirm New Password</label>
            <input type="password" class="form-control" id="password_confirm" name="password_confirm" minlength="6">
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="/profile" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</div> 