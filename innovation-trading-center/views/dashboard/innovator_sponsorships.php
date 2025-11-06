<?php
// Innovator Sponsorships View: $sponsorships, $currentUser
?>
<div class="container my-4">
    <h2 class="mb-4">Sponsorships for My Innovations</h2>
    <?php if (empty($sponsorships)): ?>
        <div class="alert alert-info">No sponsorships yet.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Innovation</th>
                        <th>Sponsor</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sponsorships as $s): ?>
                        <tr>
                            <td><?= htmlspecialchars($s['innovation_title']) ?></td>
                            <td><?= htmlspecialchars($s['sponsor_name']) ?></td>
                            <td><?= $s['amount'] !== null ? number_format($s['amount'], 2) : 'N/A' ?></td>
                            <td>
                                <form method="post" action="/update-sponsorship-status" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                    <select name="status" class="form-select form-select-sm d-inline w-auto" style="min-width:110px;display:inline;">
                                        <?php foreach (["pending", "approved", "completed", "rejected"] as $opt): ?>
                                            <option value="<?= $opt ?>" <?= $s['status'] === $opt ? 'selected' : '' ?>>
                                                <?= ucfirst($opt) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                </form>
                            </td>
                            <td><?= date('M j, Y', strtotime($s['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div> 