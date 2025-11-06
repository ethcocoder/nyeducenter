<?php
// Sponsor Form View: $innovation, $currentUser
?>
<div class="container my-4">
    <h2 class="mb-4">Sponsor "<?= htmlspecialchars($innovation['title']) ?>"</h2>
    <form action="/innovations/<?= $innovation['id'] ?>/sponsor" method="post">
        <div class="mb-3">
            <label for="amount" class="form-label">Sponsorship Amount (optional)</label>
            <input type="number" step="0.01" min="0" name="amount" id="amount" class="form-control" placeholder="Enter amount (optional)">
        </div>
        <button type="submit" class="btn btn-success">Confirm Sponsorship</button>
        <a href="/innovations/<?= $innovation['id'] ?>" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</div> 