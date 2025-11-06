<?php
include "inc/Header.php";
requireRole(['student']);

// Get database connection
$conn = getDBConnection();

// Include models
require_once "../Models/Project.php";
require_once "../Models/Course.php";
require_once "../Models/EnrolledStudent.php";

$project = new Project($conn);
$course = new Course($conn);
$enrolled = new EnrolledStudent($conn);

// Get project ID from query parameter
$project_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$project_id) {
    header("Location: Projects.php");
    exit();
}

// Get project details
$project_details = $project->getById($project_id);

if (!$project_details) {
    header("Location: Projects.php");
    exit();
}

// Verify student is enrolled in the course
$is_enrolled = false;
$enrolled_courses = $enrolled->getByStudentId($_SESSION['user_id']);
foreach ($enrolled_courses as $ec) {
    if ($ec['course_id'] == $project_details['course_id']) {
        $is_enrolled = true;
        break;
    }
}

if (!$is_enrolled) {
    header("Location: Projects.php");
    exit();
}

// Get student's submission
$submission = $project->getStudentSubmission($project_id, $_SESSION['user_id']);
?>

<!-- Custom CSS -->
<style>
.project-header {
    background: linear-gradient(45deg, #4b6cb7, #182848);
    color: white;
    padding: 30px;
    border-radius: 10px;
    margin-bottom: 30px;
}

.project-content {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    padding: 30px;
    margin-bottom: 30px;
}

.submission-card {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 25px;
    margin-bottom: 30px;
}

.status-badge {
    padding: 8px 15px;
    border-radius: 20px;
    font-weight: 500;
}

.section-title {
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #eee;
}

.due-date {
    background: rgba(255,255,255,0.1);
    padding: 8px 15px;
    border-radius: 20px;
    font-size: 0.9em;
}

.feedback-box {
    background: #fff;
    border-left: 4px solid #4b6cb7;
    padding: 20px;
    border-radius: 0 10px 10px 0;
    margin-top: 20px;
}

.btn-custom {
    border-radius: 20px;
    padding: 10px 25px;
    font-weight: 500;
    transition: all 0.3s;
}

.btn-custom:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.back-link {
    color: white;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 20px;
}

.back-link:hover {
    color: rgba(255,255,255,0.8);
    text-decoration: none;
}
</style>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="project-header">
                <a href="Projects.php" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back to Projects
                </a>
                <h2 class="mb-2"><?php echo htmlspecialchars($project_details['title']); ?></h2>
                <div class="d-flex align-items-center gap-3">
                    <span class="due-date">
                        <i class="far fa-clock"></i> Due: <?php echo date('F d, Y', strtotime($project_details['due_date'])); ?>
                    </span>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="project-content">
                        <h4 class="section-title">Project Description</h4>
                        <div class="mb-4">
                            <?php echo nl2br(htmlspecialchars($project_details['description'])); ?>
                        </div>

                        <h4 class="section-title">Project Rubric</h4>
                        <div>
                            <?php echo nl2br(htmlspecialchars($project_details['rubric'])); ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="submission-card">
                        <h4 class="section-title">Your Submission</h4>
                        <?php if ($submission): ?>
                            <div class="mb-4">
                                <p class="mb-2"><strong>Status:</strong></p>
                                <span class="status-badge badge badge-<?php 
                                    echo $submission['status'] === 'Passed' ? 'success' : 
                                        ($submission['status'] === 'Failed' ? 'danger' : 
                                        ($submission['status'] === 'Under Review' ? 'warning' : 'info')); 
                                ?>">
                                    <?php echo htmlspecialchars($submission['status']); ?>
                                </span>
                            </div>

                            <div class="mb-4">
                                <p class="mb-2"><strong>Submitted:</strong></p>
                                <p class="text-muted">
                                    <i class="far fa-calendar-alt"></i> 
                                    <?php echo date('F d, Y H:i', strtotime($submission['submitted_at'])); ?>
                                </p>
                            </div>

                            <div class="mb-4">
                                <p class="mb-2"><strong>Submission URL:</strong></p>
                                <a href="<?php echo htmlspecialchars($submission['submission_url']); ?>" 
                                   target="_blank" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-external-link-alt"></i> View Submission
                                </a>
                            </div>

                            <?php if ($submission['feedback']): ?>
                                <div class="feedback-box">
                                    <h5 class="mb-3">Instructor Feedback</h5>
                                    <?php echo nl2br(htmlspecialchars($submission['feedback'])); ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($submission['status'] === 'Failed'): ?>
                                <button type="button" class="btn btn-primary btn-custom btn-block mt-4" 
                                        onclick="submitProject(<?php echo $project_id; ?>)">
                                    <i class="fas fa-redo"></i> Resubmit Project
                                </button>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-upload fa-3x text-muted mb-3"></i>
                                <p class="mb-4">You haven't submitted this project yet.</p>
                                <button type="button" class="btn btn-primary btn-custom" 
                                        onclick="submitProject(<?php echo $project_id; ?>)">
                                    <i class="fas fa-upload"></i> Submit Project
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Submit Project Modal -->
<div class="modal fade" id="submitProjectModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Submit Project</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="submitProjectForm" action="Action/Project-Action.php" method="POST">
                <input type="hidden" name="project_id" id="submitProjectId">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Submission URL</label>
                        <input type="url" class="form-control" name="submission_url" required
                               placeholder="Enter the URL of your project (GitHub, Google Drive, etc.)">
                        <small class="form-text text-muted">
                            Provide a link to your project files (e.g., GitHub repository, Google Drive, etc.)
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" name="submit_project">
                        <i class="fas fa-upload"></i> Submit Project
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function submitProject(projectId) {
    document.getElementById('submitProjectId').value = projectId;
    $('#submitProjectModal').modal('show');
}
</script>

<?php include "inc/Footer.php"; ?> 