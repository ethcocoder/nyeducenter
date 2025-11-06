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
    $path = getLearningPathDetails($path_id);
    
    if (!$path || $path['teacher_id'] != $_SESSION['teacher_id']) {
        $em = "Learning path not found or you don't have access";
        Util::redirect("Learning-Paths.php", "error", $em);
    }
    
    $modules = getLearningPathModules($path_id);
    $students = getEnrolledStudents($path_id);
    $progress_data = getStudentProgress($path_id);
    
    # Header
    $title = "EduPulse - Track Progress";
    include "inc/Header.php";
?>

<div class="wrapper">
    <?php include "inc/NavBar.php"; ?>
    
    <div class="main-content p-4">
        <div class="container-fluid">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="text-white mb-2">Track Progress</h2>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-primary me-2"><?= htmlspecialchars($path['title']) ?></span>
                        <span class="text-muted">
                            <i class="fa fa-users me-1"></i> <?= count($students) ?> Students
                        </span>
                    </div>
                </div>
                <div>
                    <a href="Learning-Paths.php" class="btn btn-secondary me-2">
                        <i class="fa fa-arrow-left"></i> Back to Paths
                    </a>
                    <button class="btn btn-primary" onclick="exportProgress()">
                        <i class="fa fa-download"></i> Export Progress
                    </button>
                </div>
            </div>

            <!-- Progress Overview -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-dark text-white">
                        <div class="card-body">
                            <h6 class="card-title text-muted">Average Progress</h6>
                            <h3 class="mb-0">
                                <?php
                                $total_progress = 0;
                                foreach ($progress_data as $progress) {
                                    $total_progress += $progress['overall_progress'];
                                }
                                echo count($progress_data) > 0 ? 
                                     round($total_progress / count($progress_data)) . '%' : '0%';
                                ?>
                            </h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-dark text-white">
                        <div class="card-body">
                            <h6 class="card-title text-muted">Completed Modules</h6>
                            <h3 class="mb-0">
                                <?php
                                $total_completed = 0;
                                foreach ($progress_data as $progress) {
                                    $total_completed += $progress['completed_modules'];
                                }
                                echo count($progress_data) > 0 ? 
                                     round($total_completed / count($progress_data)) : '0';
                                ?>
                            </h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-dark text-white">
                        <div class="card-body">
                            <h6 class="card-title text-muted">Average Score</h6>
                            <h3 class="mb-0">
                                <?php
                                $total_score = 0;
                                $score_count = 0;
                                foreach ($progress_data as $progress) {
                                    if ($progress['average_score'] > 0) {
                                        $total_score += $progress['average_score'];
                                        $score_count++;
                                    }
                                }
                                echo $score_count > 0 ? 
                                     round($total_score / $score_count) . '%' : 'N/A';
                                ?>
                            </h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-dark text-white">
                        <div class="card-body">
                            <h6 class="card-title text-muted">Active Students</h6>
                            <h3 class="mb-0">
                                <?php
                                $active_students = 0;
                                foreach ($progress_data as $progress) {
                                    if ($progress['last_activity'] > strtotime('-7 days')) {
                                        $active_students++;
                                    }
                                }
                                echo $active_students;
                                ?>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Student Progress Table -->
            <div class="card bg-dark text-white">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Progress</th>
                                    <th>Completed</th>
                                    <th>Average Score</th>
                                    <th>Last Activity</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($progress_data as $progress) { ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="<?= $progress['profile_pic'] ?: '../assets/img/default-avatar.png' ?>" 
                                                     class="rounded-circle me-2" width="32" height="32">
                                                <div>
                                                    <div><?= htmlspecialchars($progress['name']) ?></div>
                                                    <small class="text-muted"><?= htmlspecialchars($progress['email']) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 5px;">
                                                <div class="progress-bar bg-success" 
                                                     role="progressbar" 
                                                     style="width: <?= $progress['overall_progress'] ?>%">
                                                </div>
                                            </div>
                                            <small class="text-muted"><?= $progress['overall_progress'] ?>%</small>
                                        </td>
                                        <td>
                                            <?= $progress['completed_modules'] ?>/<?= count($modules) ?>
                                            <small class="text-muted d-block">
                                                <?= round(($progress['completed_modules'] / count($modules)) * 100) ?>%
                                            </small>
                                        </td>
                                        <td>
                                            <?php if ($progress['average_score'] > 0) { ?>
                                                <span class="badge bg-<?= $progress['average_score'] >= 70 ? 'success' : 
                                                                      ($progress['average_score'] >= 50 ? 'warning' : 'danger') ?>">
                                                    <?= $progress['average_score'] ?>%
                                                </span>
                                            <?php } else { ?>
                                                <span class="text-muted">N/A</span>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <?php
                                            $last_activity = $progress['last_activity'];
                                            $time_diff = time() - $last_activity;
                                            
                                            if ($time_diff < 3600) {
                                                echo round($time_diff / 60) . ' minutes ago';
                                            } elseif ($time_diff < 86400) {
                                                echo round($time_diff / 3600) . ' hours ago';
                                            } else {
                                                echo round($time_diff / 86400) . ' days ago';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="Student-Progress.php?path_id=<?= $path_id ?>&student_id=<?= $progress['student_id'] ?>" 
                                                   class="btn btn-sm btn-primary" 
                                                   title="View Details">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-sm btn-info" 
                                                        onclick="sendMessage(<?= $progress['student_id'] ?>)" 
                                                        title="Send Message">
                                                    <i class="fa fa-envelope"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Module Progress -->
            <div class="card bg-dark text-white mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Module Progress</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover">
                            <thead>
                                <tr>
                                    <th>Module</th>
                                    <th>Completion Rate</th>
                                    <th>Average Score</th>
                                    <th>Time Spent</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($modules as $module) { 
                                    $module_progress = getModuleProgress($module['id']);
                                ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fa fa-book text-primary me-2"></i>
                                                <?= htmlspecialchars($module['title']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 5px;">
                                                <div class="progress-bar bg-success" 
                                                     role="progressbar" 
                                                     style="width: <?= $module_progress['completion_rate'] ?>%">
                                                </div>
                                            </div>
                                            <small class="text-muted"><?= $module_progress['completion_rate'] ?>%</small>
                                        </td>
                                        <td>
                                            <?php if ($module_progress['average_score'] > 0) { ?>
                                                <span class="badge bg-<?= $module_progress['average_score'] >= 70 ? 'success' : 
                                                                      ($module_progress['average_score'] >= 50 ? 'warning' : 'danger') ?>">
                                                    <?= $module_progress['average_score'] ?>%
                                                </span>
                                            <?php } else { ?>
                                                <span class="text-muted">N/A</span>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <?= round($module_progress['average_time'] / 60) ?> minutes
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Message Modal -->
<div class="modal fade" id="messageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title">Send Message</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="messageForm">
                    <input type="hidden" name="student_id" id="messageStudentId">
                    <div class="mb-3">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" class="form-control bg-dark text-white" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea name="message" class="form-control bg-dark text-white" rows="4" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitMessage()">Send</button>
            </div>
        </div>
    </div>
</div>

<script>
// Export progress data
function exportProgress() {
    window.location.href = 'Action/export-progress.php?path_id=<?= $path_id ?>';
}

// Send message to student
function sendMessage(studentId) {
    document.getElementById('messageStudentId').value = studentId;
    new bootstrap.Modal(document.getElementById('messageModal')).show();
}

// Submit message
function submitMessage() {
    const form = document.getElementById('messageForm');
    const formData = new FormData(form);
    
    fetch('Action/send-message.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('messageModal')).hide();
            // Show success message
            const toast = new bootstrap.Toast(document.createElement('div'));
            toast.show();
        } else {
            alert(data.message);
        }
    });
}
</script>

<?php include "inc/Footer.php"; ?>
<?php
} else { 
    $em = "First login ";
    Util::redirect("../login.php", "error", $em);
}
?> 