<?php
// Admin Innovation Management View: $innovations (array), $filters (array), $currentUser
?>
<div class="container my-4">
    <h2 class="mb-4">Innovation Management</h2>
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-3">
            <input type="text" class="form-control" name="search" placeholder="Search by title or innovator" value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
        </div>
        <div class="col-md-3">
            <select class="form-select" name="category">
                <option value="">All Categories</option>
                <?php foreach ($filters['categories'] ?? [] as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($filters['category'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select class="form-select" name="status">
                <option value="">All Statuses</option>
                <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="published" <?= ($filters['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                <option value="funded" <?= ($filters['status'] ?? '') === 'funded' ? 'selected' : '' ?>>Funded</option>
                <option value="completed" <?= ($filters['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
    </form>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Innovator</th>
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
                        <td><?= htmlspecialchars($inv['innovator_name']) ?></td>
                        <td><?= htmlspecialchars($inv['category_name']) ?></td>
                        <td>
                            <span class="badge bg-<?= $inv['status'] === 'published' ? 'success' : ($inv['status'] === 'draft' ? 'secondary' : ($inv['status'] === 'funded' ? 'info' : 'dark')) ?>">
                                <?= ucfirst($inv['status']) ?>
                            </span>
                        </td>
                        <td><?= date('M j, Y', strtotime($inv['created_at'])) ?></td>
                        <td>
                            <a href="/innovations/<?= $inv['id'] ?>" class="btn btn-sm btn-outline-info">View</a>
                            <a href="/admin/innovations/edit/<?= $inv['id'] ?>" class="btn btn-sm btn-outline-warning">Edit</a>
                            <?php if ($inv['status'] !== 'published'): ?>
                                <a href="/admin/innovations/approve/<?= $inv['id'] ?>" class="btn btn-sm btn-outline-success">Approve</a>
                            <?php endif; ?>
                            <?php if ($inv['status'] === 'published'): ?>
                                <a href="/admin/innovations/reject/<?= $inv['id'] ?>" class="btn btn-sm btn-outline-secondary">Reject</a>
                            <?php endif; ?>
                            <a href="/admin/innovations/delete/<?= $inv['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this innovation?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div> 