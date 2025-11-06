<?php
// Send Message View: $receiver (optional), $innovation (optional), $errors (optional), $data (optional), $currentUser, $admins (optional)
?>
<div class="container my-4">
    <h2>Send Message</h2>
    <div class="card mb-4">
        <div class="card-body">
            <?php if (empty($receiver) && !empty($admins)): ?>
                <form method="GET" action="">
                    <div class="mb-3">
                        <label for="receiver_id" class="form-label">Select Admin to Contact <span class="text-danger">*</span></label>
                        <select class="form-select" id="receiver_id" name="receiver_id" required>
                            <option value="">Choose an admin...</option>
                            <?php foreach ($admins as $admin): ?>
                                <option value="<?= $admin['id'] ?>"> <?= htmlspecialchars($admin['name']) ?> (<?= htmlspecialchars($admin['email']) ?>) </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Next</button>
                </form>
            <?php else: ?>
                <div class="mb-3">
                    <strong>To:</strong> <?= htmlspecialchars($receiver['name']) ?>
                    <?php if (!empty($receiver['profile_image'])): ?>
                        <img src="<?= htmlspecialchars($receiver['profile_image']) ?>" alt="Receiver" class="rounded-circle ms-2" style="width:32px;height:32px;">
                    <?php endif; ?>
                </div>
                <?php if (!empty($innovation)): ?>
                    <div class="mb-3">
                        <strong>Regarding Innovation:</strong> <a href="/innovations/<?= $innovation['id'] ?>"> <?= htmlspecialchars($innovation['title']) ?> </a>
                    </div>
                <?php endif; ?>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $field => $msg): ?>
                                <li><?= htmlspecialchars($msg) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <form method="POST" action="">
                    <input type="hidden" name="receiver_id" value="<?= htmlspecialchars($receiver['id']) ?>">
                    <?php if (!empty($innovation)): ?>
                        <input type="hidden" name="innovation_id" value="<?= htmlspecialchars($innovation['id']) ?>">
                    <?php endif; ?>
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="subject" name="subject" maxlength="255" required value="<?= htmlspecialchars($data['subject'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="body" class="form-label">Message <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="body" name="body" rows="5" required><?= htmlspecialchars($data['body'] ?? '') ?></textarea>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="/messages/inbox" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i> Send</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div> 