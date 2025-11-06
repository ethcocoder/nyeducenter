<?php /** @var array $users */ ?>
<div class="container my-4" style="max-width:600px;">
    <h2>Create Group Chat</h2>
    <form method="post" action="/messages/group/create" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="group_name" class="form-label">Group Name</label>
            <input type="text" class="form-control" id="group_name" name="group_name" required maxlength="100">
        </div>
        <div class="mb-3">
            <label for="group_image" class="form-label">Group Image</label>
            <input type="file" class="form-control" id="group_image" name="group_image" accept="image/*">
        </div>
        <div class="mb-3">
            <label for="members" class="form-label">Add Members</label>
            <select class="form-select" id="members" name="members[]" multiple required size="6">
                <?php foreach ($users as $user): ?>
                    <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['name']) ?> (<?= htmlspecialchars($user['email']) ?>)</option>
                <?php endforeach; ?>
            </select>
            <div class="form-text">Hold Ctrl (Windows) or Cmd (Mac) to select multiple users.</div>
        </div>
        <button type="submit" class="btn btn-primary">Create Group</button>
        <a href="/messages" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</div> 