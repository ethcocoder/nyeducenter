<?php 
session_start();
include "../Utils/Util.php";
if (isset($_SESSION['username']) && isset($_SESSION['teacher_id'])) {
    include "../Controller/Teacher/LearningPath.php";
    
    if (!isset($_GET['module_id']) || !isset($_GET['type'])) {
        $em = "Invalid request";
        Util::redirect("Learning-Paths.php", "error", $em);
    }
    
    $module_id = $_GET['module_id'];
    $resource_type = $_GET['type'];
    
    $module = getModuleDetails($module_id);
    
    if (!$module || $module['teacher_id'] != $_SESSION['teacher_id']) {
        $em = "Module not found or you don't have access";
        Util::redirect("Learning-Paths.php", "error", $em);
    }
    
    # Header
    $title = "EduPulse - Add Resource";
    include "inc/Header.php";
?>

<div class="wrapper">
    <?php include "inc/NavBar.php"; ?>
    
    <div class="main-content p-4">
        <div class="container-fluid">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="text-white mb-2">Add New Resource</h2>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-primary me-2"><?= htmlspecialchars($module['title']) ?></span>
                        <span class="badge bg-info me-2"><?= htmlspecialchars($module['path_title']) ?></span>
                        <span class="badge bg-<?= $resource_type == 'video' ? 'danger' : 
                                              ($resource_type == 'document' ? 'primary' : 
                                              ($resource_type == 'quiz' ? 'warning' : 'info')) ?>">
                            <?= ucfirst($resource_type) ?>
                        </span>
                    </div>
                </div>
                <a href="Manage-Resources.php?module_id=<?= $module_id ?>" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> Back to Resources
                </a>
            </div>

            <!-- Form Section -->
            <div class="row">
                <div class="col-md-8">
                    <div class="card bg-dark text-white">
                        <div class="card-body">
                            <form action="Action/add-resource.php" method="POST" class="needs-validation" novalidate 
                                  enctype="multipart/form-data">
                                <input type="hidden" name="module_id" value="<?= $module_id ?>">
                                <input type="hidden" name="type" value="<?= $resource_type ?>">
                                
                                <!-- Basic Information -->
                                <h5 class="mb-4">Basic Information</h5>
                                <div class="row mb-4">
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Title</label>
                                        <input type="text" name="title" class="form-control bg-dark text-white" required>
                                        <div class="invalid-feedback">Please enter a title</div>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" class="form-control bg-dark text-white" rows="4" required></textarea>
                                        <div class="invalid-feedback">Please enter a description</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Duration (minutes)</label>
                                        <input type="number" name="duration" class="form-control bg-dark text-white" 
                                               min="1" max="180" value="30">
                                        <div class="invalid-feedback">Please enter a valid duration</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select bg-dark text-white" required>
                                            <option value="draft">Draft</option>
                                            <option value="active">Active</option>
                                        </select>
                                        <div class="invalid-feedback">Please select a status</div>
                                    </div>
                                </div>

                                <!-- Resource Specific Fields -->
                                <?php if ($resource_type == 'video') { ?>
                                    <h5 class="mb-4">Video Content</h5>
                                    <div class="row mb-4">
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label">Video Source</label>
                                            <select name="video_source" class="form-select bg-dark text-white" required>
                                                <option value="upload">Upload Video</option>
                                                <option value="youtube">YouTube</option>
                                                <option value="vimeo">Vimeo</option>
                                            </select>
                                        </div>
                                        <div class="col-md-12 mb-3" id="upload-field">
                                            <label class="form-label">Upload Video</label>
                                            <input type="file" name="video_file" class="form-control bg-dark text-white" 
                                                   accept="video/*">
                                            <small class="text-muted">Maximum file size: 500MB</small>
                                        </div>
                                        <div class="col-md-12 mb-3" id="url-field" style="display: none;">
                                            <label class="form-label">Video URL</label>
                                            <input type="url" name="video_url" class="form-control bg-dark text-white" 
                                                   placeholder="Enter video URL">
                                        </div>
                                    </div>
                                <?php } elseif ($resource_type == 'document') { ?>
                                    <h5 class="mb-4">Document Content</h5>
                                    <div class="row mb-4">
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label">Upload Document</label>
                                            <input type="file" name="document_file" class="form-control bg-dark text-white" 
                                                   accept=".pdf,.doc,.docx,.ppt,.pptx" required>
                                            <small class="text-muted">Supported formats: PDF, DOC, DOCX, PPT, PPTX</small>
                                        </div>
                                    </div>
                                <?php } elseif ($resource_type == 'quiz') { ?>
                                    <h5 class="mb-4">Quiz Settings</h5>
                                    <div class="row mb-4">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Time Limit (minutes)</label>
                                            <input type="number" name="time_limit" class="form-control bg-dark text-white" 
                                                   min="1" max="180" value="30">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Passing Score (%)</label>
                                            <input type="number" name="passing_score" class="form-control bg-dark text-white" 
                                                   min="0" max="100" value="70">
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <div class="form-check">
                                                <input type="checkbox" name="show_answers" class="form-check-input" id="showAnswers">
                                                <label class="form-check-label" for="showAnswers">
                                                    Show correct answers after submission
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                <?php } else { ?>
                                    <h5 class="mb-4">Assignment Settings</h5>
                                    <div class="row mb-4">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Due Date</label>
                                            <input type="datetime-local" name="due_date" class="form-control bg-dark text-white">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Maximum Points</label>
                                            <input type="number" name="max_points" class="form-control bg-dark text-white" 
                                                   min="1" max="100" value="100">
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label">Instructions</label>
                                            <textarea name="instructions" class="form-control bg-dark text-white" rows="4"></textarea>
                                        </div>
                                    </div>
                                <?php } ?>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-save"></i> Create Resource
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-md-4">
                    <!-- Tips Card -->
                    <div class="card bg-dark text-white mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Tips for Creating Resources</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-3">
                                    <i class="fa fa-lightbulb text-warning me-2"></i>
                                    Keep content focused and concise
                                </li>
                                <li class="mb-3">
                                    <i class="fa fa-lightbulb text-warning me-2"></i>
                                    Use clear and engaging titles
                                </li>
                                <li class="mb-3">
                                    <i class="fa fa-lightbulb text-warning me-2"></i>
                                    Provide detailed descriptions
                                </li>
                                <li class="mb-3">
                                    <i class="fa fa-lightbulb text-warning me-2"></i>
                                    Set appropriate duration
                                </li>
                                <li>
                                    <i class="fa fa-lightbulb text-warning me-2"></i>
                                    Review before publishing
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Preview Card -->
                    <div class="card bg-dark text-white">
                        <div class="card-header">
                            <h5 class="mb-0">Preview</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Your resource will be displayed to students in this format:</p>
                            <div class="preview-item mb-3">
                                <h6 class="mb-2">Title</h6>
                                <p class="text-muted small mb-0">[Your resource title]</p>
                            </div>
                            <div class="preview-item mb-3">
                                <h6 class="mb-2">Description</h6>
                                <p class="text-muted small mb-0">[Your resource description]</p>
                            </div>
                            <div class="preview-item">
                                <h6 class="mb-2">Content</h6>
                                <p class="text-muted small mb-0">[Your resource content]</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()

// Video source toggle
document.querySelector('select[name="video_source"]').addEventListener('change', function() {
    const uploadField = document.getElementById('upload-field');
    const urlField = document.getElementById('url-field');
    
    if (this.value === 'upload') {
        uploadField.style.display = 'block';
        urlField.style.display = 'none';
    } else {
        uploadField.style.display = 'none';
        urlField.style.display = 'block';
    }
});
</script>

<?php include "inc/Footer.php"; ?>
<?php
} else { 
    $em = "First login ";
    Util::redirect("../login.php", "error", $em);
}
?> 