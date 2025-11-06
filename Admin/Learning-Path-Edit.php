<?php 
session_start();
include "../Utils/Util.php";
if (isset($_SESSION['username']) && isset($_SESSION['admin_id'])) {
    include "../Controller/Admin/LearningPath.php";
    
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $learning_path = getLearningPathById($id);
        if (!$learning_path) {
            $em = "Learning path not found";
            Util::redirect("Learning-Paths.php", "error", $em);
        }
    } else {
        $em = "Invalid request";
        Util::redirect("Learning-Paths.php", "error", $em);
    }
    
    # Header
    $title = "EduPulse - Edit Learning Path";
    include "inc/Header.php";
?>

<div class="wrapper">
    <?php include "inc/NavBar.php"; ?>
    
    <div class="main-content p-4">
        <div class="container-fluid">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="text-white">Edit Learning Path</h2>
                <a href="Learning-Paths.php" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> Back to Learning Paths
                </a>
            </div>

            <!-- Edit Form -->
            <div class="card bg-dark text-white">
                <div class="card-body">
                    <form id="editLearningPathForm" action="Action/learning-path-update.php" method="POST">
                        <input type="hidden" name="path_id" value="<?= $learning_path['path_id'] ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($learning_path['title']) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Subject</label>
                                <input type="text" class="form-control" name="subject" value="<?= htmlspecialchars($learning_path['subject']) ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Grade Level</label>
                                <select class="form-select" name="grade_level" required>
                                    <option value="Grade 9" <?= $learning_path['grade_level'] == 'Grade 9' ? 'selected' : '' ?>>Grade 9</option>
                                    <option value="Grade 10" <?= $learning_path['grade_level'] == 'Grade 10' ? 'selected' : '' ?>>Grade 10</option>
                                    <option value="Grade 11" <?= $learning_path['grade_level'] == 'Grade 11' ? 'selected' : '' ?>>Grade 11</option>
                                    <option value="Grade 12" <?= $learning_path['grade_level'] == 'Grade 12' ? 'selected' : '' ?>>Grade 12</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Semester</label>
                                <select class="form-select" name="semester" required>
                                    <option value="First" <?= $learning_path['semester'] == 'First' ? 'selected' : '' ?>>First Semester</option>
                                    <option value="Second" <?= $learning_path['semester'] == 'Second' ? 'selected' : '' ?>>Second Semester</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" required><?= htmlspecialchars($learning_path['description']) ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Credits</label>
                                <input type="number" class="form-control" name="credits" value="<?= $learning_path['credits'] ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Department</label>
                                <select class="form-select" name="department_id" required>
                                    <?php
                                    $departments = getAllDepartments();
                                    foreach ($departments as $dept) {
                                        $selected = ($dept['department_id'] == $learning_path['department_id']) ? 'selected' : '';
                                        echo "<option value='{$dept['department_id']}' {$selected}>{$dept['name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" required>
                                <option value="Active" <?= $learning_path['status'] == 'Active' ? 'selected' : '' ?>>Active</option>
                                <option value="Inactive" <?= $learning_path['status'] == 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> Save Changes
                            </button>
                            <button type="button" class="btn btn-danger" onclick="deleteLearningPath(<?= $learning_path['path_id'] ?>)">
                                <i class="fa fa-trash"></i> Delete Learning Path
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modules Section -->
            <div class="card bg-dark text-white mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Learning Modules</h5>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModuleModal">
                        <i class="fa fa-plus"></i> Add Module
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Module list will be populated here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Module Modal -->
<div class="modal fade" id="addModuleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title">Add New Module</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addModuleForm" action="Action/module-add.php" method="POST">
                    <input type="hidden" name="path_id" value="<?= $learning_path['path_id'] ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Duration (hours)</label>
                        <input type="number" class="form-control" name="duration" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="addModuleForm" class="btn btn-primary">Add Module</button>
            </div>
        </div>
    </div>
</div>

<script>
function deleteLearningPath(id) {
    if (confirm('Are you sure you want to delete this learning path? This action cannot be undone.')) {
        window.location.href = 'Action/learning-path-delete.php?id=' + id;
    }
}
</script>

<?php include "inc/Footer.php"; ?>
<?php
} else { 
    $em = "First login ";
    Util::redirect("../login.php", "error", $em);
}
?> 