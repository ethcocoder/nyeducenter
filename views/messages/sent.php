<?php
// Sent View: $messages (paginated), $currentUser
?>
<div class="container my-4">
    <h2>Sent Messages</h2>
    <div class="mb-3">
        <a href="/messages/inbox" class="btn btn-outline-primary btn-sm">Inbox</a>
    </div>
    <?php if (empty($messages['data'])): ?>
        <div class="alert alert-info">No sent messages.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>To</th>
                        <th>Subject</th>
                        <th>Innovation</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages['data'] as $msg): ?>
                        <tr>
                            <td>
                                <?php if (!empty($msg['receiver_image'])): ?>
                                    <img src="<?= htmlspecialchars($msg['receiver_image']) ?>" alt="Receiver" class="rounded-circle me-2" style="width:32px;height:32px;">
                                <?php endif; ?>
                                <?= htmlspecialchars($msg['receiver_name']) ?>
                            </td>
                            <td><?= htmlspecialchars($msg['subject']) ?></td>
                            <td><?= $msg['innovation_title'] ? htmlspecialchars($msg['innovation_title']) : '-' ?></td>
                            <td><?= date('M j, Y H:i', strtotime($msg['sent_at'])) ?></td>
                            <td><?= $msg['is_read'] ? 'Read' : '<strong>Unread</strong>' ?></td>
                            <td>
                                <a href="/messages/conversation?contact_id=<?= $msg['receiver_id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                                <a href="/messages/send?receiver_id=<?= $msg['receiver_id'] ?>&innovation_id=<?= $msg['innovation_id'] ?>" class="btn btn-sm btn-outline-success">Resend</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if ($messages['last_page'] > 1): ?>
            <nav>
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $messages['last_page']; $i++): ?>
                        <li class="page-item <?= $i == $messages['current_page'] ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"> <?= $i ?> </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div> 