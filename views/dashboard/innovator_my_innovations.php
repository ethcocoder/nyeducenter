<?php
// Innovator My Innovations Management View: $innovations (array), $currentUser
?>
<div class="container my-4">
    <h2 class="mb-4">My Innovations</h2>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($innovations as $inv): ?>
                    <tr>
                        <td><?= htmlspecialchars($inv['title']) ?></td>
                        <td><?= htmlspecialchars($inv['category_name']) ?></td>
                        <td>
                            <span class="badge bg-<?= $inv['status'] === 'published' ? 'success' : ($inv['status'] === 'draft' ? 'secondary' : ($inv['status'] === 'funded' ? 'info' : 'dark')) ?>">
                                <?= ucfirst($inv['status']) ?>
                            </span>
                        </td>
                        <td><?= date('M j, Y', strtotime($inv['created_at'])) ?></td>
                        <td>
                            <a href="/innovations/<?= $inv['id'] ?>" class="btn btn-sm btn-outline-info">View</a>
                            <a href="/innovations/<?= $inv['id'] ?>/edit" class="btn btn-sm btn-outline-warning">Edit</a>
                            <a href="/innovations/<?= $inv['id'] ?>/delete" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this innovation?');">Delete</a>
                            <a href="/innovations/<?= $inv['id'] ?>/stats" class="btn btn-sm btn-outline-primary">Stats</a>
                            <button class="btn btn-sm btn-outline-<?= $inv['status'] === 'published' ? 'secondary' : 'success' ?> toggle-status-btn" data-id="<?= $inv['id'] ?>">
                                <?= $inv['status'] === 'published' ? 'Unpublish' : 'Publish' ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.toggle-status-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var id = this.getAttribute('data-id');
            var row = this.closest('tr');
            var statusBadge = row.querySelector('span.badge');
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
                        statusBadge.textContent = 'Published';
                        statusBadge.className = 'badge bg-success';
                        button.textContent = 'Unpublish';
                        button.className = 'btn btn-sm btn-outline-secondary toggle-status-btn';
                    } else {
                        statusBadge.textContent = 'Draft';
                        statusBadge.className = 'badge bg-secondary';
                        button.textContent = 'Publish';
                        button.className = 'btn btn-sm btn-outline-success toggle-status-btn';
                    }
                }
                button.disabled = false;
            })
            .catch(() => { button.disabled = false; });
        });
    });
});
</script> 