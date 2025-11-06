<?php 
session_start();
include "../Utils/Util.php";
if (isset($_SESSION['username']) && isset($_SESSION['teacher_id'])) {
    include "../Controller/Teacher/LearningPath.php";
    
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;
    
    $learning_paths = getTeacherLearningPaths($_SESSION['teacher_id'], $offset, $limit);
    $total_paths = getTeacherLearningPathCount($_SESSION['teacher_id']);
    $total_pages = ceil($total_paths / $limit);
    
    # Header
    $title = "EduPulse - Learning Paths";
    include "inc/Header.php";
?>

<div class="wrapper">
    <?php include "inc/NavBar.php"; ?>
    
    <div class="main-content p-4">
        <div class="container-fluid">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="text-white mb-2">Learning Paths</h2>
                    <p class="text-muted mb-0">Manage your learning paths and modules</p>
                </div>
                <a href="Add-Learning-Path.php" class="btn btn-primary">
                    <i class="fa fa-plus"></i> Create New Path
                </a>
            </div>

            <!-- Filters Section -->
            <div class="card bg-dark text-white mb-4">
                <div class="card-body">
                    <form action="" method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Grade Level</label>
                            <select name="grade" class="form-select bg-dark text-white">
                                <option value="">All Grades</option>
                                <option value="9">Grade 9</option>
                                <option value="10">Grade 10</option>
                                <option value="11">Grade 11</option>
                                <option value="12">Grade 12</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Subject</label>
                            <select name="subject" class="form-select bg-dark text-white">
                                <option value="">All Subjects</option>
                                <option value="Mathematics">Mathematics</option>
                                <option value="Science">Science</option>
                                <option value="English">English</option>
                                <option value="History">History</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select bg-dark text-white">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="draft">Draft</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fa fa-filter"></i> Apply Filters
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Learning Paths Grid -->
            <div class="row">
                <?php foreach ($learning_paths as $path) { ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card bg-dark text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h5 class="card-title mb-1"><?= htmlspecialchars($path['title']) ?></h5>
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-primary me-2"><?= htmlspecialchars($path['grade_level']) ?></span>
                                            <span class="badge bg-info"><?= htmlspecialchars($path['subject']) ?></span>
                                        </div>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-link text-white" type="button" data-bs-toggle="dropdown">
                                            <i class="fa fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-dark">
                                            <li>
                                                <a class="dropdown-item" href="Edit-Learning-Path.php?id=<?= $path['id'] ?>">
                                                    <i class="fa fa-edit me-2"></i> Edit
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="Manage-Modules.php?path_id=<?= $path['id'] ?>">
                                                    <i class="fa fa-tasks me-2"></i> Manage Modules
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="View-Progress.php?path_id=<?= $path['id'] ?>">
                                                    <i class="fa fa-chart-line me-2"></i> View Progress
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="#" 
                                                   onclick="confirmDelete(<?= $path['id'] ?>)">
                                                    <i class="fa fa-trash me-2"></i> Delete
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                
                                <p class="card-text text-muted mb-3">
                                    <?= htmlspecialchars(substr($path['description'], 0, 100)) ?>...
                                </p>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-muted">
                                            <i class="fa fa-users me-1"></i> <?= $path['enrolled_students'] ?> students
                                        </small>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fa fa-tasks me-1"></i> <?= $path['total_modules'] ?> modules
                                        </small>
                                    </div>
                                    <span class="badge bg-<?= $path['status'] == 'active' ? 'success' : 
                                                          ($path['status'] == 'draft' ? 'warning' : 'secondary') ?>">
                                        <?= ucfirst($path['status']) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-footer bg-dark border-secondary">
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="Learning-Path-View.php?id=<?= $path['id'] ?>" class="btn btn-sm btn-primary">
                                        View Details
                                    </a>
                                    <small class="text-muted">
                                        Last updated: <?= date('M d, Y', strtotime($path['updated_at'])) ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1) { ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link bg-dark text-white" href="?page=<?= $page - 1 ?>">Previous</a>
                        </li>
                        <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link bg-dark text-white" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php } ?>
                        <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                            <a class="page-link bg-dark text-white" href="?page=<?= $page + 1 ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            <?php } ?>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this learning path? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="Action/delete-learning-path.php" method="POST" class="d-inline">
                    <input type="hidden" name="path_id" id="deletePathId">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(pathId) {
    document.getElementById('deletePathId').value = pathId;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>

<?php include "inc/Footer.php"; ?>
<?php
} else { 
    $em = "First login ";
    Util::redirect("../login.php", "error", $em);
}
?> 