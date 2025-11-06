<?php
// Admin User Management View: $users (array), $filters (array), $currentUser
?>
<div class="container my-4">
    <h2 class="mb-4">User Management</h2>
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-3">
            <input type="text" class="form-control" name="search" placeholder="Search by name or email" value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
        </div>
        <div class="col-md-2">
            <select class="form-select" name="role">
                <option value="">All Roles</option>
                <option value="admin" <?= ($filters['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="innovator" <?= ($filters['role'] ?? '') === 'innovator' ? 'selected' : '' ?>>Innovator</option>
                <option value="sponsor" <?= ($filters['role'] ?? '') === 'sponsor' ? 'selected' : '' ?>>Sponsor</option>
            </select>
        </div>
        <div class="col-md-2">
            <select class="form-select" name="status">
                <option value="">All Statuses</option>
                <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
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
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= ucfirst($user['role']) ?></td>
                        <td>
                            <?php if ($user['is_active']): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                        <td>
                            <a href="/admin/users/view/<?= $user['id'] ?>" class="btn btn-sm btn-outline-info">View</a>
                            <a href="/admin/users/edit/<?= $user['id'] ?>" class="btn btn-sm btn-outline-warning">Edit</a>
                            <?php if ($user['is_active']): ?>
                                <a href="/admin/users/deactivate/<?= $user['id'] ?>" class="btn btn-sm btn-outline-secondary">Deactivate</a>
                            <?php else: ?>
                                <a href="/admin/users/activate/<?= $user['id'] ?>" class="btn btn-sm btn-outline-success">Activate</a>
                            <?php endif; ?>
                            <?php if ($user['id'] !== $currentUser['id']): ?>
                                <a href="/admin/users/delete/<?= $user['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div> 