<?php 
session_start();
include "../Utils/Util.php";
if (isset($_SESSION['username']) && isset($_SESSION['student_id'])) {
    include "../Controller/Student/LearningPath.php";
    
    // Get student's enrolled learning paths
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
    
    $learning_paths = getStudentLearningPaths($_SESSION['student_id'], $offset, $row_num);
    $total_paths = getStudentLearningPathCount($_SESSION['student_id']);
    $last_page = ceil($total_paths / $row_num);
    
    # Header
    $title = "EduPulse - My Learning Paths";
    include "inc/Header.php";
?>

<div class="wrapper">
    <?php include "inc/NavBar.php"; ?>
    
    <div class="main-content p-4">
        <div class="container-fluid">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="text-white">My Learning Paths</h2>
                <div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#enrollPathModal">
                        <i class="fa fa-plus"></i> Enroll in New Path
                    </button>
                </div>
            </div>

            <!-- Learning Paths Grid -->
            <div class="row">
                <?php if ($learning_paths) { ?>
                    <?php foreach ($learning_paths as $path) { ?>
                        <div class="col-md-4 mb-4">
                            <div class="card bg-dark text-white h-100">
                                <div class="card-header">
                                    <h5 class="mb-0"><?= htmlspecialchars($path['title']) ?></h5>
                                </div>
                                <div class="card-body">
                                    <p class="card-text"><?= htmlspecialchars($path['description']) ?></p>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="badge bg-primary"><?= htmlspecialchars($path['grade_level']) ?></span>
                                        <span class="badge bg-info"><?= htmlspecialchars($path['subject']) ?></span>
                                    </div>
                                    <div class="progress mb-3" style="height: 10px;">
                                        <div class="progress-bar bg-success" role="progressbar" 
                                             style="width: <?= $path['progress_percentage'] ?>%" 
                                             aria-valuenow="<?= $path['progress_percentage'] ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="fa fa-clock"></i> <?= $path['credits'] ?> Credits
                                            <span class="mx-2">|</span>
                                            <i class="fa fa-calendar"></i> <?= $path['semester'] ?> Semester
                                        </small>
                                        <span class="badge <?= $path['status'] == 'Active' ? 'bg-success' : 'bg-warning' ?>">
                                            <?= $path['status'] ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="card-footer bg-dark-subtle">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">Progress: <?= $path['progress_percentage'] ?>%</span>
                                        <a href="Learning-Path-View.php?id=<?= $path['path_id'] ?>" class="btn btn-primary btn-sm">
                                            Continue Learning
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="col-12">
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> You haven't enrolled in any learning paths yet.
                            Click the "Enroll in New Path" button to get started!
                        </div>
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

<!-- Enroll in Path Modal -->
<div class="modal fade" id="enrollPathModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title">Enroll in Learning Path</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Grade Level</label>
                        <select class="form-select" id="gradeFilter">
                            <option value="">All Grades</option>
                            <option value="Grade 9">Grade 9</option>
                            <option value="Grade 10">Grade 10</option>
                            <option value="Grade 11">Grade 11</option>
                            <option value="Grade 12">Grade 12</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Subject</label>
                        <select class="form-select" id="subjectFilter">
                            <option value="">All Subjects</option>
                            <!-- Subjects will be populated dynamically -->
                        </select>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-dark table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Subject</th>
                                <th>Grade</th>
                                <th>Credits</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="availablePaths">
                            <!-- Available paths will be populated dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Function to load available learning paths
function loadAvailablePaths() {
    const grade = document.getElementById('gradeFilter').value;
    const subject = document.getElementById('subjectFilter').value;
    
    fetch(`Action/get-available-paths.php?grade=${grade}&subject=${subject}`)
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('availablePaths');
            tbody.innerHTML = '';
            
            data.forEach(path => {
                tbody.innerHTML += `
                    <tr>
                        <td>${path.title}</td>
                        <td>${path.subject}</td>
                        <td>${path.grade_level}</td>
                        <td>${path.credits}</td>
                        <td>
                            <button class="btn btn-primary btn-sm" onclick="enrollInPath(${path.path_id})">
                                Enroll
                            </button>
                        </td>
                    </tr>
                `;
            });
        });
}

// Function to enroll in a learning path
function enrollInPath(pathId) {
    if (confirm('Are you sure you want to enroll in this learning path?')) {
        fetch('Action/enroll-path.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `path_id=${pathId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message);
            }
        });
    }
}

// Event listeners for filters
document.getElementById('gradeFilter').addEventListener('change', loadAvailablePaths);
document.getElementById('subjectFilter').addEventListener('change', loadAvailablePaths);

// Load paths when modal opens
document.getElementById('enrollPathModal').addEventListener('show.bs.modal', loadAvailablePaths);
</script>

<?php include "inc/Footer.php"; ?>
<?php
} else { 
    $em = "First login ";
    Util::redirect("../login.php", "error", $em);
}
?> 