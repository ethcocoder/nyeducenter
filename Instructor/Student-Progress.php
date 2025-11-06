<?php 
session_start();
include "../Utils/Util.php";
if (isset($_SESSION['username']) && isset($_SESSION['teacher_id'])) {
    include "../Controller/Teacher/LearningPath.php";
    
    if (!isset($_GET['path_id']) || !isset($_GET['student_id'])) {
        $em = "Invalid request";
        Util::redirect("Learning-Paths.php", "error", $em);
    }
    
    $path_id = $_GET['path_id'];
    $student_id = $_GET['student_id'];
    
    $path = getLearningPathDetails($path_id);
    $student = getStudentDetails($student_id);
    $modules = getLearningPathModules($path_id);
    $progress = getDetailedStudentProgress($path_id, $student_id);
    
    if (!$path || $path['teacher_id'] != $_SESSION['teacher_id']) {
        $em = "Learning path not found or you don't have access";
        Util::redirect("Learning-Paths.php", "error", $em);
    }
    
    if (!$student) {
        $em = "Student not found";
        Util::redirect("Learning-Paths.php", "error", $em);
    }
    
    # Header
    $title = "EduPulse - Student Progress";
    include "inc/Header.php";
?>

<div class="wrapper">
    <?php include "inc/NavBar.php"; ?>
    
    <div class="main-content p-4">
        <div class="container-fluid">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="text-white mb-2">Student Progress</h2>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-primary me-2"><?= htmlspecialchars($path['title']) ?></span>
                        <span class="badge bg-info me-2"><?= htmlspecialchars($student['name']) ?></span>
                    </div>
                </div>
                <div>
                    <a href="Track-Progress.php?path_id=<?= $path_id ?>" class="btn btn-secondary me-2">
                        <i class="fa fa-arrow-left"></i> Back to Progress
                    </a>
                    <button class="btn btn-primary" onclick="exportStudentProgress()">
                        <i class="fa fa-download"></i> Export Progress
                    </button>
                </div>
            </div>

            <!-- Student Overview -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-dark text-white">
                        <div class="card-body">
                            <h6 class="card-title text-muted">Overall Progress</h6>
                            <h3 class="mb-0"><?= $progress['overall_progress'] ?>%</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-dark text-white">
                        <div class="card-body">
                            <h6 class="card-title text-muted">Completed Modules</h6>
                            <h3 class="mb-0">
                                <?= $progress['completed_modules'] ?>/<?= count($modules) ?>
                            </h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-dark text-white">
                        <div class="card-body">
                            <h6 class="card-title text-muted">Average Score</h6>
                            <h3 class="mb-0">
                                <?php if ($progress['average_score'] > 0) { ?>
                                    <span class="badge bg-<?= $progress['average_score'] >= 70 ? 'success' : 
                                                          ($progress['average_score'] >= 50 ? 'warning' : 'danger') ?>">
                                        <?= $progress['average_score'] ?>%
                                    </span>
                                <?php } else { ?>
                                    <span class="text-muted">N/A</span>
                                <?php } ?>
                            </h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-dark text-white">
                        <div class="card-body">
                            <h6 class="card-title text-muted">Time Spent</h6>
                            <h3 class="mb-0">
                                <?= round($progress['total_time'] / 3600, 1) ?> hours
                            </h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Module Progress -->
            <div class="card bg-dark text-white">
                <div class="card-header">
                    <h5 class="mb-0">Module Progress</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover">
                            <thead>
                                <tr>
                                    <th>Module</th>
                                    <th>Progress</th>
                                    <th>Resources</th>
                                    <th>Score</th>
                                    <th>Time Spent</th>
                                    <th>Last Activity</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($modules as $module) { 
                                    $module_progress = $progress['modules'][$module['id']] ?? null;
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
                                                     style="width: <?= $module_progress ? $module_progress['progress'] : 0 ?>%">
                                                </div>
                                            </div>
                                            <small class="text-muted">
                                                <?= $module_progress ? $module_progress['progress'] : 0 ?>%
                                            </small>
                                        </td>
                                        <td>
                                            <?php if ($module_progress) { ?>
                                                <?= $module_progress['completed_resources'] ?>/<?= $module_progress['total_resources'] ?>
                                            <?php } else { ?>
                                                0/0
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <?php if ($module_progress && $module_progress['score'] > 0) { ?>
                                                <span class="badge bg-<?= $module_progress['score'] >= 70 ? 'success' : 
                                                                      ($module_progress['score'] >= 50 ? 'warning' : 'danger') ?>">
                                                    <?= $module_progress['score'] ?>%
                                                </span>
                                            <?php } else { ?>
                                                <span class="text-muted">N/A</span>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <?php if ($module_progress) { ?>
                                                <?= round($module_progress['time_spent'] / 60) ?> minutes
                                            <?php } else { ?>
                                                0 minutes
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <?php if ($module_progress && $module_progress['last_activity']) { 
                                                $last_activity = $module_progress['last_activity'];
                                                $time_diff = time() - $last_activity;
                                                
                                                if ($time_diff < 3600) {
                                                    echo round($time_diff / 60) . ' minutes ago';
                                                } elseif ($time_diff < 86400) {
                                                    echo round($time_diff / 3600) . ' hours ago';
                                                } else {
                                                    echo round($time_diff / 86400) . ' days ago';
                                                }
                                            } else { ?>
                                                <span class="text-muted">Never</span>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="Module-Progress.php?module_id=<?= $module['id'] ?>&student_id=<?= $student_id ?>" 
                                                   class="btn btn-sm btn-primary" 
                                                   title="View Details">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-sm btn-info" 
                                                        onclick="sendMessage(<?= $student_id ?>, '<?= $module['title'] ?>')" 
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

            <!-- Activity Timeline -->
            <div class="card bg-dark text-white mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Recent Activity</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <?php foreach ($progress['recent_activity'] as $activity) { ?>
                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1"><?= htmlspecialchars($activity['title']) ?></h6>
                                    <p class="text-muted mb-0">
                                        <?= htmlspecialchars($activity['description']) ?>
                                    </p>
                                    <small class="text-muted">
                                        <?php
                                        $time_diff = time() - $activity['timestamp'];
                                        if ($time_diff < 3600) {
                                            echo round($time_diff / 60) . ' minutes ago';
                                        } elseif ($time_diff < 86400) {
                                            echo round($time_diff / 3600) . ' hours ago';
                                        } else {
                                            echo round($time_diff / 86400) . ' days ago';
                                        }
                                        ?>
                                    </small>
                                </div>
                            </div>
                        <?php } ?>
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
                    <input type="hidden" name="module_title" id="messageModuleTitle">
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

<style>
.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline-item {
    position: relative;
    padding-left: 40px;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: 0;
    top: 0;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #007bff;
    border: 2px solid #fff;
}

.timeline-item:before {
    content: '';
    position: absolute;
    left: 5px;
    top: 12px;
    height: calc(100% + 8px);
    width: 2px;
    background: #007bff;
}

.timeline-item:last-child:before {
    display: none;
}
</style>

<script>
// Export student progress
function exportStudentProgress() {
    window.location.href = 'Action/export-student-progress.php?path_id=<?= $path_id ?>&student_id=<?= $student_id ?>';
}

// Send message to student
function sendMessage(studentId, moduleTitle) {
    document.getElementById('messageStudentId').value = studentId;
    document.getElementById('messageModuleTitle').value = moduleTitle;
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