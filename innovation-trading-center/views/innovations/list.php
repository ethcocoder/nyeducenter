<?php
// Ensure filter keys exist to avoid undefined index warnings
$filters = $filters ?? [];
$filters['category'] = $filters['category'] ?? '';
$filters['stage'] = $filters['stage'] ?? '';
$filters['search'] = $filters['search'] ?? '';
$filters['location'] = $filters['location'] ?? '';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Innovations</h2>
            <?php if ($currentUser && $currentUser['role'] === 'innovator'): ?>
                <a href="/innovations/create" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Post Innovation
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Search and Filters -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="/innovations" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="search" placeholder="Search innovations..." 
                               value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="category">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>" <?= ($filters['category'] == $category['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="stage">
                            <option value="">All Stages</option>
                            <option value="idea" <?= ($filters['stage'] === 'idea') ? 'selected' : '' ?>>Idea</option>
                            <option value="prototype" <?= ($filters['stage'] === 'prototype') ? 'selected' : '' ?>>Prototype</option>
                            <option value="pilot" <?= ($filters['stage'] === 'pilot') ? 'selected' : '' ?>>Pilot</option>
                            <option value="market_ready" <?= ($filters['stage'] === 'market_ready') ? 'selected' : '' ?>>Market Ready</option>
                            <option value="scaling" <?= ($filters['stage'] === 'scaling') ? 'selected' : '' ?>>Scaling</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control" name="location" placeholder="Location" 
                               value="<?= htmlspecialchars($filters['location'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Results Count -->
<div class="row mb-3">
    <div class="col-12">
        <p class="text-muted">
            Showing <?= $innovations['from'] ?> to <?= $innovations['to'] ?> of <?= $innovations['total'] ?> innovations
        </p>
    </div>
</div>

<!-- Innovations Grid -->
<?php if (empty($innovations['data'])): ?>
    <div class="row">
        <div class="col-12">
            <div class="text-center py-5">
                <h4 class="text-muted">No innovations found</h4>
                <p class="text-muted">Try adjusting your search criteria or browse all innovations.</p>
                <a href="/innovations" class="btn btn-outline-primary">View All Innovations</a>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="row">
        <?php foreach ($innovations['data'] as $innovation): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <?php if ($innovation['featured_image']): ?>
                        <img src="<?= htmlspecialchars($innovation['featured_image']) ?>" 
                             class="card-img-top" alt="<?= htmlspecialchars($innovation['title']) ?>" 
                             style="height: 200px; object-fit: cover;">
                    <?php else: ?>
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                             style="height: 200px;">
                            <i class="bi bi-lightbulb text-muted" style="font-size: 3rem;"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-primary"><?= htmlspecialchars($innovation['category_name']) ?></span>
                            <span class="badge bg-secondary"><?= ucfirst($innovation['stage']) ?></span>
                        </div>
                        
                        <h5 class="card-title"><?= htmlspecialchars($innovation['title']) ?></h5>
                        
                        <p class="card-text text-muted">
                            <?= htmlspecialchars(substr($innovation['description'], 0, 100)) ?>...
                        </p>
                        
                        <div class="mb-2">
                            <small class="text-muted">
                                <i class="bi bi-person"></i> <?= htmlspecialchars($innovation['innovator_name']) ?>
                                <?php if ($innovation['innovator_org']): ?>
                                    (<?= htmlspecialchars($innovation['innovator_org']) ?>)
                                <?php endif; ?>
                            </small>
                        </div>
                        
                        <?php if ($innovation['location']): ?>
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($innovation['location']) ?>
                                </small>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($innovation['funding_needs']): ?>
                            <div class="mb-2">
                                <small class="text-success">
                                    <i class="bi bi-currency-dollar"></i> 
                                    <?= number_format($innovation['funding_needs']) ?> <?= $innovation['funding_currency'] ?>
                                </small>
                            </div>
                        <?php endif; ?>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="bi bi-eye"></i> <?= $innovation['views_count'] ?> views
                            </small>
                            <small class="text-muted">
                                <?= date('M j, Y', strtotime($innovation['created_at'])) ?>
                            </small>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-transparent">
                        <div class="d-flex justify-content-between">
                            <a href="/innovations/<?= $innovation['id'] ?>" class="btn btn-outline-primary btn-sm">
                                View Details
                            </a>
                            <?php if ($currentUser): ?>
                                <button class="btn btn-outline-secondary btn-sm favorite-btn" 
                                        data-innovation-id="<?= $innovation['id'] ?>">
                                    <i class="bi bi-heart"></i> Favorite
                                </button>
                            <?php endif; ?>
                            <?php if ($currentUser && ($currentUser['id'] === $innovation['user_id'] || strpos($_SERVER['REQUEST_URI'], '/my-innovations') === 0)): ?>
                                <button class="btn btn-outline-<?= $innovation['status'] === 'published' ? 'secondary' : 'success' ?> btn-sm toggle-status-btn" data-id="<?= $innovation['id'] ?>">
                                    <?= $innovation['status'] === 'published' ? 'Unpublish' : 'Publish' ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Pagination -->
    <?php if ($innovations['last_page'] > 1): ?>
        <div class="row">
            <div class="col-12">
                <nav aria-label="Innovations pagination">
                    <ul class="pagination justify-content-center">
                        <?php if ($innovations['current_page'] > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $innovations['current_page'] - 1 ?><?= http_build_query(array_filter($filters)) ?>">
                                    Previous
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $innovations['last_page']; $i++): ?>
                            <li class="page-item <?= $i == $innovations['current_page'] ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?><?= http_build_query(array_filter($filters)) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($innovations['current_page'] < $innovations['last_page']): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $innovations['current_page'] + 1 ?><?= http_build_query(array_filter($filters)) ?>">
                                    Next
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle favorite buttons
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

    document.querySelectorAll('.toggle-status-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var id = this.getAttribute('data-id');
            var button = this;
            button.disabled = true;
            fetch('/innovations/' + id + '/toggle-status', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.status === 'published') {
                        button.textContent = 'Unpublish';
                        button.className = 'btn btn-outline-secondary btn-sm toggle-status-btn';
                    } else {
                        button.textContent = 'Publish';
                        button.className = 'btn btn-outline-success btn-sm toggle-status-btn';
                    }
                }
                button.disabled = false;
            })
            .catch(() => { button.disabled = false; });
        });
    });
});
</script> 