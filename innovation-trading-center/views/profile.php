<style>
.profile-header {
    background: linear-gradient(90deg, #007bff 0%, #00c6ff 100%);
    color: #fff;
    padding: 2.5rem 1rem 2.5rem 1rem;
    text-align: center;
    border-radius: 0 0 2rem 2rem;
    margin-bottom: 0;
    position: relative;
    z-index: 2;
}
.profile-title {
    font-size: 2.5rem;
    font-weight: 800;
    letter-spacing: -1px;
    margin-bottom: 0;
    text-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
.profile-card {
    background: #fff;
    border-radius: 2rem;
    box-shadow: 0 8px 32px 0 rgba(0,123,255,0.10);
    padding: 3.5rem 2.5rem 2.5rem 2.5rem;
    max-width: 600px;
    margin: 3.5rem auto 0 auto;
    position: relative;
    z-index: 3;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.profile-img {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border-radius: 50%;
    border: 4px solid #fff;
    box-shadow: 0 2px 12px 0 rgba(0,123,255,0.13);
    margin-top: 70px;
    background: #f8f9fa;
    position: relative;
    z-index: 4;
}
.profile-info h2 {
    font-weight: 800;
    font-size: 2rem;
    margin-bottom: 0.3rem;
}
.profile-info .badge {
    font-size: 1rem;
    margin-left: 0.5rem;
}
.profile-info p {
    color: #555;
    margin-bottom: 0.7rem;
}
.profile-meta {
    margin-top: 1.5rem;
    display: flex;
    flex-wrap: wrap;
    gap: 1.2rem;
    justify-content: center;
}
.profile-meta div {
    background: #f8f9fa;
    border-radius: 1rem;
    padding: 0.8rem 1.2rem;
    font-size: 1.05rem;
    color: #333;
    min-width: 140px;
    text-align: center;
}
.edit-profile-btn {
    position: absolute;
    right: 2rem;
    top: 2rem;
    z-index: 5;
}
@media (max-width: 700px) {
    .profile-card { padding: 1.2rem 0.5rem; }
    .profile-header { padding: 1.2rem 0.5rem; }
    .edit-profile-btn { right: 1rem; top: 1rem; }
}
</style>
<div class="profile-header">
    <div class="profile-title">My Profile</div>
</div>
<div class="profile-card position-relative">
    <a href="/profile/edit" class="btn btn-outline-primary edit-profile-btn">Edit Profile</a>
    <?php
    $profileImg = '/assets/default-profile.png';
    if (!empty($user['profile_image'])) {
        $profileImg = (strpos($user['profile_image'], '/') === 0)
            ? $user['profile_image']
            : '/' . $user['profile_image'];
    }
    ?>
    <img src="<?= htmlspecialchars($profileImg) ?>" class="profile-img mb-3" alt="Profile Image">
    <div class="profile-info text-center mt-2">
        <h2><?= htmlspecialchars($user['name']) ?></h2>
        <span class="badge bg-primary text-light text-capitalize"><?= htmlspecialchars($user['role']) ?></span>
        <?php if ($user['is_verified']): ?>
            <span class="badge bg-success">Verified</span>
        <?php endif; ?>
        <?php if (!$user['is_active']): ?>
            <span class="badge bg-danger">Inactive</span>
        <?php endif; ?>
        <p class="mt-2 mb-1"><i class="bi bi-envelope"></i> <?= htmlspecialchars($user['email']) ?></p>
        <?php if ($user['organization']): ?>
            <p class="mb-1"><i class="bi bi-building"></i> <?= htmlspecialchars($user['organization']) ?></p>
        <?php endif; ?>
        <?php if ($user['bio']): ?>
            <p class="mb-1"><i class="bi bi-person-lines-fill"></i> <?= nl2br(htmlspecialchars($user['bio'])) ?></p>
        <?php endif; ?>
    </div>
    <div class="profile-meta">
        <?php if ($user['phone']): ?>
            <div><i class="bi bi-telephone"></i> <?= htmlspecialchars($user['phone']) ?></div>
        <?php endif; ?>
        <?php if ($user['location']): ?>
            <div><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($user['location']) ?></div>
        <?php endif; ?>
        <div><i class="bi bi-calendar"></i> Joined <?= date('F Y', strtotime($user['created_at'])) ?></div>
    </div>
</div> 