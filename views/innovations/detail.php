<?php
// Innovation Details View
// Variables: $innovation, $media, $canEdit, $currentUser
?>

<div class="row justify-content-center">
    <div class="col-lg-10 col-md-12">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h3 class="mb-0"><?= htmlspecialchars($innovation['title']) ?></h3>
                <div>
                    <?php if ($canEdit): ?>
                        <a href="/innovations/<?= $innovation['id'] ?>/edit" class="btn btn-warning btn-sm me-2">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <form action="/innovations/<?= $innovation['id'] ?>/delete" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this innovation?');">
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </form>
                    <?php endif; ?>
                    <?php if ($currentUser): ?>
                        <button class="btn btn-outline-secondary btn-sm favorite-btn ms-2" data-innovation-id="<?= $innovation['id'] ?>">
                            <i class="bi bi-heart"></i> Favorite
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <?php if ($innovation['featured_image']): ?>
                            <img src="<?= htmlspecialchars($innovation['featured_image']) ?>" class="img-fluid rounded mb-3" alt="Featured Image">
                        <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center rounded mb-3" style="height: 250px;">
                                <i class="bi bi-lightbulb text-muted" style="font-size: 4rem;"></i>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($media)): ?>
                            <div class="mb-2">
                                <strong>Media Gallery:</strong>
                                <div class="row g-2">
                                    <?php foreach ($media as $m): ?>
                                        <div class="col-4">
                                            <?php if ($m['media_type'] === 'image'): ?>
                                                <a href="<?= htmlspecialchars($m['file_path']) ?>" target="_blank">
                                                    <img src="<?= htmlspecialchars($m['file_path']) ?>" class="img-fluid rounded" alt="Media">
                                                </a>
                                            <?php else: ?>
                                                <a href="<?= htmlspecialchars($m['file_path']) ?>" target="_blank">
                                                    <i class="bi bi-file-earmark-text" style="font-size: 2rem;"></i> <?= htmlspecialchars($m['original_name']) ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-2">
                            <span class="badge bg-primary me-2"><?= htmlspecialchars($innovation['category_name']) ?></span>
                            <span class="badge bg-secondary me-2"><?= ucfirst($innovation['stage']) ?></span>
                            <span class="badge bg-info text-dark">Views: <?= $innovation['views_count'] ?></span>
                        </div>
                        <p class="text-muted mb-2">
                            <i class="bi bi-person"></i> <?= htmlspecialchars($innovation['innovator_name']) ?>
                            <?php if ($innovation['innovator_org']): ?>
                                (<?= htmlspecialchars($innovation['innovator_org']) ?>)
                            <?php endif; ?>
                        </p>
                        <?php if ($innovation['location']): ?>
                            <p class="mb-2"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($innovation['location']) ?></p>
                        <?php endif; ?>
                        <?php if ($innovation['funding_needs']): ?>
                            <p class="mb-2 text-success">
                                <i class="bi bi-currency-dollar"></i> Funding Needs: <?= number_format($innovation['funding_needs']) ?> <?= $innovation['funding_currency'] ?>
                            </p>
                        <?php endif; ?>
                        <p class="mb-2"><i class="bi bi-calendar"></i> Posted: <?= date('M j, Y', strtotime($innovation['created_at'])) ?></p>
                        <hr>
                        <h5>Description</h5>
                        <p><?= nl2br(htmlspecialchars($innovation['description'])) ?></p>
                        <?php if ($innovation['video_url']): ?>
                            <div class="mb-3">
                                <h6>Video</h6>
                                <div class="ratio ratio-16x9">
                                    <iframe src="<?= htmlspecialchars($innovation['video_url']) ?>" frameborder="0" allowfullscreen></iframe>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if ($innovation['website_url']): ?>
                            <p><i class="bi bi-globe"></i> <a href="<?= htmlspecialchars($innovation['website_url']) ?>" target="_blank">Visit Website</a></p>
                        <?php endif; ?>
                        <hr>
                        <h6>Contact Information</h6>
                        <?php if ($innovation['contact_email']): ?>
                            <p><i class="bi bi-envelope"></i> <a href="mailto:<?= htmlspecialchars($innovation['contact_email']) ?>">Email</a></p>
                        <?php endif; ?>
                        <?php if ($innovation['contact_phone']): ?>
                            <p><i class="bi bi-telephone"></i> <?= htmlspecialchars($innovation['contact_phone']) ?></p>
                        <?php endif; ?>
                        <?php if ($currentUser && $currentUser['id'] !== $innovation['user_id']): ?>
                            <a href="/messages/send?receiver_id=<?= $innovation['user_id'] ?>&innovation_id=<?= $innovation['id'] ?>" class="btn btn-success mb-3">
                                <i class="bi bi-envelope"></i> Contact Innovator
                            </a>
                        <?php endif; ?>
                        <?php if (isset($currentUser) && $currentUser['role'] === 'sponsor'):
                            require_once __DIR__ . '/../../models/Sponsorship.php';
                            $sponsorship = new Sponsorship();
                            $alreadySponsored = $sponsorship->hasSponsored($currentUser['id'], $innovation['id']);
                            if (!$alreadySponsored): ?>
                                <form action="/innovations/<?= $innovation['id'] ?>/sponsor-form" method="get" class="mb-3">
                                    <button type="submit" class="btn btn-success btn-lg w-100">Sponsor This Innovation</button>
                                </form>
                            <?php else: ?>
                                <div class="alert alert-info">You have already sponsored this innovation.</div>
                            <?php endif;
                        endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle favorite button
    document.querySelectorAll('.favorite-btn').forEach(button => {
        button.addEventListener('click', function() {
            const innovationId = this.dataset.innovationId;
            fetch(`/innovations/${innovationId}/favorite`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.favorited) {
                        this.innerHTML = '<i class="bi bi-heart-fill text-danger"></i> Favorited';
                        this.classList.add('btn-danger');
                        this.classList.remove('btn-outline-secondary');
                    } else {
                        this.innerHTML = '<i class="bi bi-heart"></i> Favorite';
                        this.classList.remove('btn-danger');
                        this.classList.add('btn-outline-secondary');
                    }
                }
            });
        });
    });
});
</script> 