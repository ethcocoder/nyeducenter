<?php
// Admin Message Management View: $messages (array), $filters (array), $currentUser
?>
<div class="container my-4">
    <h2 class="mb-4">Message Management</h2>
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-3">
            <input type="text" class="form-control" name="search" placeholder="Search by sender or receiver" value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
        </div>
        <div class="col-md-2">
            <select class="form-select" name="status">
                <option value="">All Statuses</option>
                <option value="read" <?= ($filters['status'] ?? '') === 'read' ? 'selected' : '' ?>>Read</option>
                <option value="unread" <?= ($filters['status'] ?? '') === 'unread' ? 'selected' : '' ?>>Unread</option>
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
                    <th>From</th>
                    <th>To</th>
                    <th>Subject</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $msg): ?>
                    <tr class="<?= $msg['is_read'] ? '' : 'table-warning' ?>">
                        <td><?= htmlspecialchars($msg['sender_name']) ?></td>
                        <td><?= htmlspecialchars($msg['receiver_name']) ?></td>
                        <td><?= htmlspecialchars($msg['subject']) ?></td>
                        <td><?= date('M j, Y H:i', strtotime($msg['sent_at'])) ?></td>
                        <td><?= $msg['is_read'] ? 'Read' : '<strong>Unread</strong>' ?></td>
                        <td>
                            <a href="/messages/conversation?contact_id=<?= $msg['sender_id'] ?>" class="btn btn-sm btn-outline-info">View Conversation</a>
                            <a href="/admin/messages/delete/<?= $msg['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this message?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div> 