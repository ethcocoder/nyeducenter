<?php /** @var array $users */ ?>
<div class="container my-4" style="max-width:700px;">
    <h2>Select a User to Message</h2>
    <div class="list-group mt-4">
        <?php foreach ($users as $user): ?>
            <div class="list-group-item d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <img src="<?= !empty($user['profile_image']) ? htmlspecialchars($user['profile_image']) : '/assets/default-profile.png' ?>" alt="Profile" class="rounded-circle" style="width:48px;height:48px;object-fit:cover;">
                    <div>
                        <div style="font-weight:600; font-size:1.1rem;"><?= htmlspecialchars($user['name']) ?></div>
                        <div style="font-size:0.97rem; color:#666;"> <?= htmlspecialchars($user['email']) ?> </div>
                    </div>
                </div>
                <a href="/messages/send?receiver_id=<?= $user['id'] ?>" class="btn btn-primary btn-sm">Message</a>
            </div>
        <?php endforeach; ?>
    </div>
    <a href="/messages" class="btn btn-secondary mt-4">Back to Inbox</a>
</div> 