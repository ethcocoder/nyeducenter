<?php 
session_start();
include "../Utils/Util.php";
if (isset($_SESSION['username']) && isset($_SESSION['teacher_id'])) {
    include "../Controller/Teacher/LearningPath.php";
    
    if (!isset($_GET['path_id'])) {
        $em = "Invalid request";
        Util::redirect("Learning-Paths.php", "error", $em);
    }
    
    $path_id = $_GET['path_id'];
    $learning_path = getLearningPathDetails($path_id);
    
    if (!$learning_path || $learning_path['teacher_id'] != $_SESSION['teacher_id']) {
        $em = "Learning path not found or you don't have access";
        Util::redirect("Learning-Paths.php", "error", $em);
    }
    
    # Header
    $title = "EduPulse - Add Module";
    include "inc/Header.php";
?>

<div class="wrapper">
    <?php include "inc/NavBar.php"; ?>
    
    <div class="main-content p-4">
        <div class="container-fluid">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="text-white mb-2">Add New Module</h2>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-primary me-2"><?= htmlspecialchars($learning_path['title']) ?></span>
                        <span class="badge bg-info me-2"><?= htmlspecialchars($learning_path['subject']) ?></span>
                    </div>
                </div>
                <a href="Manage-Modules.php?path_id=<?= $path_id ?>" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> Back to Modules
                </a>
            </div>

            <!-- Form Section -->
            <div class="row">
                <div class="col-md-8">
                    <div class="card bg-dark text-white">
                        <div class="card-body">
                            <form action="Action/add-module.php" method="POST" class="needs-validation" novalidate>
                                <input type="hidden" name="path_id" value="<?= $path_id ?>">
                                
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
                                        <label class="form-label">Duration (hours)</label>
                                        <input type="number" name="duration" class="form-control bg-dark text-white" 
                                               min="1" max="100" value="1" required>
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

                                <!-- Learning Objectives -->
                                <h5 class="mb-4">Learning Objectives</h5>
                                <div class="mb-4">
                                    <div id="objectives-container">
                                        <div class="objective-item mb-3">
                                            <div class="input-group">
                                                <input type="text" name="objectives[]" class="form-control bg-dark text-white" 
                                                       placeholder="Enter learning objective" required>
                                                <button type="button" class="btn btn-danger remove-objective">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary" id="add-objective">
                                        <i class="fa fa-plus"></i> Add Objective
                                    </button>
                                </div>

                                <!-- Assessment Criteria -->
                                <h5 class="mb-4">Assessment Criteria</h5>
                                <div class="mb-4">
                                    <div id="criteria-container">
                                        <div class="criterion-item mb-3">
                                            <div class="input-group">
                                                <input type="text" name="criteria[]" class="form-control bg-dark text-white" 
                                                       placeholder="Enter assessment criterion">
                                                <button type="button" class="btn btn-danger remove-criterion">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary" id="add-criterion">
                                        <i class="fa fa-plus"></i> Add Criterion
                                    </button>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-save"></i> Create Module
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
                            <h5 class="mb-0">Tips for Creating Modules</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-3">
                                    <i class="fa fa-lightbulb text-warning me-2"></i>
                                    Keep modules focused and concise
                                </li>
                                <li class="mb-3">
                                    <i class="fa fa-lightbulb text-warning me-2"></i>
                                    Include various types of resources
                                </li>
                                <li class="mb-3">
                                    <i class="fa fa-lightbulb text-warning me-2"></i>
                                    Set clear learning objectives
                                </li>
                                <li class="mb-3">
                                    <i class="fa fa-lightbulb text-warning me-2"></i>
                                    Add interactive elements
                                </li>
                                <li>
                                    <i class="fa fa-lightbulb text-warning me-2"></i>
                                    Include assessment criteria
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Resource Types Card -->
                    <div class="card bg-dark text-white">
                        <div class="card-header">
                            <h5 class="mb-0">Available Resource Types</h5>
                        </div>
                        <div class="card-body">
                            <div class="resource-type mb-3">
                                <h6 class="mb-2">
                                    <i class="fa fa-video-camera text-danger me-2"></i>
                                    Video
                                </h6>
                                <p class="text-muted small mb-0">
                                    Upload video lectures or embed from YouTube/Vimeo
                                </p>
                            </div>
                            <div class="resource-type mb-3">
                                <h6 class="mb-2">
                                    <i class="fa fa-file-text text-primary me-2"></i>
                                    Document
                                </h6>
                                <p class="text-muted small mb-0">
                                    Upload PDFs, presentations, or text documents
                                </p>
                            </div>
                            <div class="resource-type mb-3">
                                <h6 class="mb-2">
                                    <i class="fa fa-question-circle text-warning me-2"></i>
                                    Quiz
                                </h6>
                                <p class="text-muted small mb-0">
                                    Create interactive quizzes and assessments
                                </p>
                            </div>
                            <div class="resource-type">
                                <h6 class="mb-2">
                                    <i class="fa fa-tasks text-info me-2"></i>
                                    Assignment
                                </h6>
                                <p class="text-muted small mb-0">
                                    Create hands-on assignments and projects
                                </p>
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

// Add/Remove Objectives
document.getElementById('add-objective').addEventListener('click', function() {
    const container = document.getElementById('objectives-container');
    const newObjective = document.createElement('div');
    newObjective.className = 'objective-item mb-3';
    newObjective.innerHTML = `
        <div class="input-group">
            <input type="text" name="objectives[]" class="form-control bg-dark text-white" 
                   placeholder="Enter learning objective" required>
            <button type="button" class="btn btn-danger remove-objective">
                <i class="fa fa-times"></i>
            </button>
        </div>
    `;
    container.appendChild(newObjective);
});

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-objective')) {
        e.target.closest('.objective-item').remove();
    }
});

// Add/Remove Criteria
document.getElementById('add-criterion').addEventListener('click', function() {
    const container = document.getElementById('criteria-container');
    const newCriterion = document.createElement('div');
    newCriterion.className = 'criterion-item mb-3';
    newCriterion.innerHTML = `
        <div class="input-group">
            <input type="text" name="criteria[]" class="form-control bg-dark text-white" 
                   placeholder="Enter assessment criterion">
            <button type="button" class="btn btn-danger remove-criterion">
                <i class="fa fa-times"></i>
            </button>
        </div>
    `;
    container.appendChild(newCriterion);
});

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-criterion')) {
        e.target.closest('.criterion-item').remove();
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