<?php 
session_start();
include "../Utils/Util.php";
if (isset($_SESSION['username']) && isset($_SESSION['admin_id'])) {
    include "../Controller/Admin/LearningPath.php";
    
    // Get all learning paths with pagination
    $page = 1;
    $row_num = 10;
    $offset = 0;
    
    if(isset($_GET['page'])){
        if($_GET['page'] > $last_page){
            $page = $last_page;
        }else if($_GET['page'] <= 0){
            $page = 1; 
        }else $page = $_GET['page'];
    }
    
    if($page != 1) $offset = ($page-1) * $row_num;
    
    $learning_paths = getAllLearningPaths($offset, $row_num);
    $total_paths = getLearningPathCount();
    $last_page = ceil($total_paths / $row_num);
    
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
                <h2 class="text-white">Learning Paths</h2>
                <div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLearningPathModal">
                        <i class="fa fa-plus"></i> Add Learning Path
                    </button>
                </div>
            </div>

            <!-- Learning Paths Grid -->
            <div class="row">
                <?php if ($learning_paths) { ?>
                    <?php foreach ($learning_paths as $path) { ?>
                        <div class="col-md-4 mb-4">
                            <div class="card bg-dark text-white h-100">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><?= htmlspecialchars($path['title']) ?></h5>
                                    <div class="dropdown">
                                        <button class="btn btn-link text-white" type="button" data-bs-toggle="dropdown">
                                            <i class="fa fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-dark">
                                            <li><a class="dropdown-item" href="Learning-Path-Edit.php?id=<?= $path['path_id'] ?>">Edit</a></li>
                                            <li><a class="dropdown-item" href="Learning-Path-View.php?id=<?= $path['path_id'] ?>">View Details</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="deleteLearningPath(<?= $path['path_id'] ?>)">Delete</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="card-text"><?= htmlspecialchars($path['description']) ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-primary"><?= htmlspecialchars($path['grade_level']) ?></span>
                                        <span class="badge bg-info"><?= htmlspecialchars($path['subject']) ?></span>
                                    </div>
                                    <div class="mt-3">
                                        <small class="text-muted">
                                            <i class="fa fa-clock"></i> <?= $path['credits'] ?> Credits
                                            <span class="mx-2">|</span>
                                            <i class="fa fa-calendar"></i> <?= $path['semester'] ?> Semester
                                        </small>
                                    </div>
                                </div>
                                <div class="card-footer bg-dark-subtle">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge <?= $path['status'] == 'Active' ? 'bg-success' : 'bg-warning' ?>">
                                            <?= $path['status'] ?>
                                        </span>
                                        <a href="Learning-Path-Students.php?id=<?= $path['path_id'] ?>" class="btn btn-sm btn-outline-light">
                                            View Students
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="col-12">
                        <div class="alert alert-info">No learning paths found</div>
                    </div>
                <?php } ?>
            </div>

            <!-- Pagination -->
            <?php if ($last_page > 1) { ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page-1 ?>">Previous</a>
                        </li>
                        <?php for($i = 1; $i <= $last_page; $i++) { ?>
                            <li class="page-item <?= $page == $i ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php } ?>
                        <li class="page-item <?= $page >= $last_page ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page+1 ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            <?php } ?>
        </div>
    </div>
</div>

<!-- Add Learning Path Modal -->
<div class="modal fade" id="addLearningPathModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title">Add New Learning Path</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addLearningPathForm" action="Action/learning-path-add.php" method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Subject</label>
                            <input type="text" class="form-control" name="subject" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Grade Level</label>
                            <select class="form-select" name="grade_level" required>
                                <option value="Grade 9">Grade 9</option>
                                <option value="Grade 10">Grade 10</option>
                                <option value="Grade 11">Grade 11</option>
                                <option value="Grade 12">Grade 12</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Semester</label>
                            <select class="form-select" name="semester" required>
                                <option value="First">First Semester</option>
                                <option value="Second">Second Semester</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Credits</label>
                            <input type="number" class="form-control" name="credits" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Department</label>
                            <select class="form-select" name="department_id" required>
                                <?php
                                $departments = getAllDepartments();
                                foreach ($departments as $dept) {
                                    echo "<option value='{$dept['department_id']}'>{$dept['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="addLearningPathForm" class="btn btn-primary">Add Learning Path</button>
            </div>
        </div>
    </div>
</div>

<script>
function deleteLearningPath(pathId) {
    if (confirm('Are you sure you want to delete this learning path? This action cannot be undone.')) {
        window.location.href = 'Action/learning-path-delete.php?id=' + pathId;
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