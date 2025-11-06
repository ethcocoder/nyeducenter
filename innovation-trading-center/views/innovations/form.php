<?php
// Innovation Form View: Used for both create and edit
// Variables: $categories, $innovation (nullable), $errors (optional), $data (optional), $media (optional), $currentUser
?>

<?php 
$isEdit = isset($innovation) && $innovation;
$formAction = $isEdit ? "/innovations/{$innovation['id']}/update" : "/innovations/store";
$formTitle = $isEdit ? 'Edit Innovation' : 'Post New Innovation';
$submitLabel = $isEdit ? 'Update Innovation' : 'Post Innovation';
$formData = $data ?? $innovation ?? [];
?>

<div class="row justify-content-center">
    <div class="col-lg-8 col-md-10">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><?= $formTitle ?></h4>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $field => $msg): ?>
                                <li><?= htmlspecialchars($msg) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <form method="POST" action="<?= $formAction ?>" enctype="multipart/form-data" novalidate>
                    <div class="mb-3">
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" required maxlength="255"
                               value="<?= htmlspecialchars($formData['title'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="description" name="description" rows="5" required minlength="50"><?= htmlspecialchars($formData['description'] ?? '') ?></textarea>
                        <div class="form-text">Minimum 50 characters</div>
                    </div>
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($formData['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="funding_needs" class="form-label">Funding Needs</label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0" class="form-control" id="funding_needs" name="funding_needs"
                                       value="<?= htmlspecialchars($formData['funding_needs'] ?? '') ?>">
                                <select class="form-select" name="funding_currency">
                                    <option value="ETB" <?= ($formData['funding_currency'] ?? 'ETB') === 'ETB' ? 'selected' : '' ?>>ETB</option>
                                    <option value="USD" <?= ($formData['funding_currency'] ?? '') === 'USD' ? 'selected' : '' ?>>USD</option>
                                    <option value="EUR" <?= ($formData['funding_currency'] ?? '') === 'EUR' ? 'selected' : '' ?>>EUR</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="location" name="location"
                                   value="<?= htmlspecialchars($formData['location'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="stage" class="form-label">Stage</label>
                        <select class="form-select" id="stage" name="stage">
                            <option value="idea" <?= ($formData['stage'] ?? 'idea') === 'idea' ? 'selected' : '' ?>>Idea</option>
                            <option value="prototype" <?= ($formData['stage'] ?? '') === 'prototype' ? 'selected' : '' ?>>Prototype</option>
                            <option value="pilot" <?= ($formData['stage'] ?? '') === 'pilot' ? 'selected' : '' ?>>Pilot</option>
                            <option value="market_ready" <?= ($formData['stage'] ?? '') === 'market_ready' ? 'selected' : '' ?>>Market Ready</option>
                            <option value="scaling" <?= ($formData['stage'] ?? '') === 'scaling' ? 'selected' : '' ?>>Scaling</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="featured_image" class="form-label">Featured Image</label>
                        <?php if ($isEdit && !empty($innovation['featured_image'])): ?>
                            <div class="mb-2">
                                <img src="<?= htmlspecialchars($innovation['featured_image']) ?>" alt="Featured Image" style="max-width: 200px; max-height: 150px;">
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" id="featured_image" name="featured_image" accept="image/*">
                        <div class="form-text">JPG, PNG, GIF. Max 2MB.</div>
                    </div>
                    <div class="mb-3">
                        <label for="media" class="form-label">Additional Media (images, docs, etc.)</label>
                        <input type="file" class="form-control" id="media" name="media[]" multiple>
                        <div class="form-text">You can upload multiple files. Images, PDFs, docs allowed.</div>
                        <?php if ($isEdit && !empty($media)): ?>
                            <div class="mt-2">
                                <strong>Existing Media:</strong>
                                <ul class="list-unstyled">
                                    <?php foreach ($media as $m): ?>
                                        <li>
                                            <a href="<?= htmlspecialchars($m['file_path']) ?>" target="_blank">
                                                <?= htmlspecialchars($m['original_name']) ?>
                                            </a>
                                            <span class="badge bg-secondary ms-2"> <?= htmlspecialchars($m['media_type']) ?> </span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="video_url" class="form-label">Video URL</label>
                            <input type="url" class="form-control" id="video_url" name="video_url"
                                   value="<?= htmlspecialchars($formData['video_url'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="website_url" class="form-label">Website URL</label>
                            <input type="url" class="form-control" id="website_url" name="website_url"
                                   value="<?= htmlspecialchars($formData['website_url'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="contact_email" class="form-label">Contact Email</label>
                            <input type="email" class="form-control" id="contact_email" name="contact_email"
                                   value="<?= htmlspecialchars($formData['contact_email'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="contact_phone" class="form-label">Contact Phone</label>
                            <input type="text" class="form-control" id="contact_phone" name="contact_phone"
                                   value="<?= htmlspecialchars($formData['contact_phone'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <a href="/innovations" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> <?= $submitLabel ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div> 