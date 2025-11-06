<?php 
session_start();
include "../Utils/Util.php";
if (isset($_SESSION['username']) && isset($_SESSION['teacher_id'])) {
    include "../Controller/Teacher/LearningPath.php";
    
    # Header
    $title = "EduPulse - Add Learning Path";
    include "inc/Header.php";
?>

<div class="wrapper">
    <?php include "inc/NavBar.php"; ?>
    
    <div class="main-content p-4">
        <div class="container-fluid">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="text-white mb-2">Add New Learning Path</h2>
                    <p class="text-muted mb-0">Create a new learning path for your students</p>
                </div>
                <a href="Learning-Paths.php" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> Back to Learning Paths
                </a>
            </div>

            <!-- Form Section -->
            <div class="row">
                <div class="col-md-8">
                    <div class="card bg-dark text-white">
                        <div class="card-body">
                            <form action="Action/add-learning-path.php" method="POST" class="needs-validation" novalidate>
                                <!-- Basic Information -->
                                <h5 class="mb-4">Basic Information</h5>
                                <div class="row mb-4">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Title</label>
                                        <input type="text" name="title" class="form-control bg-dark text-white" required>
                                        <div class="invalid-feedback">Please enter a title</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Subject</label>
                                        <select name="subject" class="form-select bg-dark text-white" required>
                                            <option value="">Select Subject</option>
                                            <option value="Mathematics">Mathematics</option>
                                            <option value="Science">Science</option>
                                            <option value="English">English</option>
                                            <option value="History">History</option>
                                        </select>
                                        <div class="invalid-feedback">Please select a subject</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Grade Level</label>
                                        <select name="grade_level" class="form-select bg-dark text-white" required>
                                            <option value="">Select Grade</option>
                                            <option value="9">Grade 9</option>
                                            <option value="10">Grade 10</option>
                                            <option value="11">Grade 11</option>
                                            <option value="12">Grade 12</option>
                                        </select>
                                        <div class="invalid-feedback">Please select a grade level</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Semester</label>
                                        <select name="semester" class="form-select bg-dark text-white" required>
                                            <option value="">Select Semester</option>
                                            <option value="1">First Semester</option>
                                            <option value="2">Second Semester</option>
                                        </select>
                                        <div class="invalid-feedback">Please select a semester</div>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" class="form-control bg-dark text-white" rows="4" required></textarea>
                                        <div class="invalid-feedback">Please enter a description</div>
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

                                <!-- Prerequisites -->
                                <h5 class="mb-4">Prerequisites</h5>
                                <div class="mb-4">
                                    <div id="prerequisites-container">
                                        <div class="prerequisite-item mb-3">
                                            <div class="input-group">
                                                <input type="text" name="prerequisites[]" class="form-control bg-dark text-white" 
                                                       placeholder="Enter prerequisite">
                                                <button type="button" class="btn btn-danger remove-prerequisite">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary" id="add-prerequisite">
                                        <i class="fa fa-plus"></i> Add Prerequisite
                                    </button>
                                </div>

                                <!-- Additional Settings -->
                                <h5 class="mb-4">Additional Settings</h5>
                                <div class="row mb-4">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Credits</label>
                                        <input type="number" name="credits" class="form-control bg-dark text-white" 
                                               min="1" max="10" value="3" required>
                                        <div class="invalid-feedback">Please enter valid credits (1-10)</div>
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

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-save"></i> Create Learning Path
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
                            <h5 class="mb-0">Tips for Creating Learning Paths</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-3">
                                    <i class="fa fa-lightbulb text-warning me-2"></i>
                                    Write clear and concise learning objectives
                                </li>
                                <li class="mb-3">
                                    <i class="fa fa-lightbulb text-warning me-2"></i>
                                    Structure content in a logical sequence
                                </li>
                                <li class="mb-3">
                                    <i class="fa fa-lightbulb text-warning me-2"></i>
                                    Include various types of learning resources
                                </li>
                                <li class="mb-3">
                                    <i class="fa fa-lightbulb text-warning me-2"></i>
                                    Set appropriate prerequisites
                                </li>
                                <li>
                                    <i class="fa fa-lightbulb text-warning me-2"></i>
                                    Review and test before publishing
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
                            <p class="text-muted">Your learning path will be displayed to students in this format:</p>
                            <div class="preview-item mb-3">
                                <h6 class="mb-2">Title</h6>
                                <p class="text-muted small mb-0">[Your learning path title]</p>
                            </div>
                            <div class="preview-item mb-3">
                                <h6 class="mb-2">Description</h6>
                                <p class="text-muted small mb-0">[Your learning path description]</p>
                            </div>
                            <div class="preview-item">
                                <h6 class="mb-2">Learning Objectives</h6>
                                <ul class="text-muted small mb-0">
                                    <li>[Your learning objectives]</li>
                                </ul>
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

// Add/Remove Prerequisites
document.getElementById('add-prerequisite').addEventListener('click', function() {
    const container = document.getElementById('prerequisites-container');
    const newPrerequisite = document.createElement('div');
    newPrerequisite.className = 'prerequisite-item mb-3';
    newPrerequisite.innerHTML = `
        <div class="input-group">
            <input type="text" name="prerequisites[]" class="form-control bg-dark text-white" 
                   placeholder="Enter prerequisite">
            <button type="button" class="btn btn-danger remove-prerequisite">
                <i class="fa fa-times"></i>
            </button>
        </div>
    `;
    container.appendChild(newPrerequisite);
});

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-prerequisite')) {
        e.target.closest('.prerequisite-item').remove();
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