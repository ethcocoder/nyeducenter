<?php
session_start();
include "../Utils/Auth.php";
include "../Database.php";
include "inc/Header.php";
requireRole(['student']);

// Get database connection
$db = new Database();
$conn = $db->getConnection();

// Include models
require_once "../Models/Project.php";
require_once "../Models/Course.php";
require_once "../Models/EnrolledStudent.php";

$project = new Project($conn);
$course = new Course($conn);
$enrolled = new EnrolledStudent($conn);

// Get student's enrolled courses
$enrolled_data = $enrolled->getEnrolled($_SESSION['student_id']);
$enrolled_courses = $enrolled_data[1] ?? []; // Get the array of enrolled courses from the result

// Get course ID from query parameter
$course_id = isset($_GET['course_id']) ? $_GET['course_id'] : null;

// Verify student is enrolled in the course
$is_enrolled = false;
if ($course_id) {
    foreach ($enrolled_courses as $ec) {
        if ($ec['course_id'] == $course_id) {
            $is_enrolled = true;
            break;
        }
    }
}

// Get projects for selected course
$projects = $is_enrolled ? $project->getByCourseId($course_id) : [];
?>

<!-- Custom CSS -->
<style>
.project-card {
    transition: transform 0.2s, box-shadow 0.2s;
    border: none;
    border-radius: 10px;
    margin-bottom: 20px;
}

.project-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.project-header {
    background: linear-gradient(45deg, #4b6cb7, #182848);
    color: white;
    border-radius: 10px 10px 0 0;
    padding: 15px 20px;
}

.project-body {
    padding: 20px;
}

.status-badge {
    padding: 8px 15px;
    border-radius: 20px;
    font-weight: 500;
}

.course-select {
    border-radius: 20px;
    padding: 8px 15px;
    border: 2px solid #e0e0e0;
    transition: border-color 0.2s;
}

.course-select:focus {
    border-color: #4b6cb7;
    box-shadow: none;
}

.project-actions {
    display: flex;
    gap: 10px;
}

.project-actions .btn {
    border-radius: 20px;
    padding: 8px 20px;
    font-weight: 500;
}

.due-date {
    color: #666;
    font-size: 0.9em;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #666;
}

.empty-state i {
    font-size: 48px;
    color: #ccc;
    margin-bottom: 20px;
}
</style>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Course Projects</h2>
                <select class="form-control course-select" id="courseSelect" onchange="changeCourse(this.value)" style="width: auto;">
                    <option value="">Select Course</option>
                    <?php foreach ($enrolled_courses as $ec): ?>
                    <option value="<?php echo $ec['course_id']; ?>" 
                            <?php echo $course_id == $ec['course_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($ec['title']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if ($course_id): ?>
                <?php if (!$is_enrolled): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> You are not enrolled in this course.
                    </div>
                <?php elseif (empty($projects)): ?>
                    <div class="empty-state">
                        <i class="fas fa-project-diagram"></i>
                        <h4>No Projects Available</h4>
                        <p>There are no projects assigned to this course yet.</p>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($projects as $proj): ?>
                        <?php 
                        $submission = $project->getStudentSubmission($proj['project_id'], $_SESSION['student_id']);
                        $status = $submission ? $submission['status'] : 'Not Submitted';
                        $statusClass = $status === 'Passed' ? 'success' : 
                                     ($status === 'Failed' ? 'danger' : 
                                     ($status === 'Under Review' ? 'warning' : 
                                     ($status === 'Submitted' ? 'info' : 'secondary')));
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card project-card">
                                <div class="project-header">
                                    <h5 class="mb-0"><?php echo htmlspecialchars($proj['title']); ?></h5>
                                </div>
                                <div class="project-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="status-badge badge badge-<?php echo $statusClass; ?>">
                                            <?php echo htmlspecialchars($status); ?>
                                        </span>
                                        <span class="due-date">
                                            <i class="far fa-clock"></i> Due: <?php echo date('M d, Y', strtotime($proj['due_date'])); ?>
                                        </span>
                                    </div>
                                    <p class="text-muted mb-3">
                                        <?php 
                                        $description = htmlspecialchars($proj['description']);
                                        echo strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description;
                                        ?>
                                    </p>
                                    <div class="project-actions">
                                        <a href="Project-View.php?id=<?php echo $proj['project_id']; ?>" 
                                           class="btn btn-outline-primary">
                                            <i class="fas fa-eye"></i> View Details
                                        </a>
                                        <?php if (!$submission || $submission['status'] === 'Failed'): ?>
                                        <button type="button" class="btn btn-primary" 
                                                onclick="submitProject(<?php echo $proj['project_id']; ?>)">
                                            <i class="fas fa-upload"></i> Submit
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-book"></i>
                    <h4>Select a Course</h4>
                    <p>Choose a course from the dropdown above to view its projects.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Submit Project Modal -->
<div class="modal fade" id="submitProjectModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Submit Project</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="submitProjectForm">
                    <input type="hidden" id="projectId" name="project_id">
                    <div class="form-group">
                        <label for="projectFile">Project File</label>
                        <input type="file" class="form-control-file" id="projectFile" name="project_file" required>
                    </div>
                    <div class="form-group">
                        <label for="projectNotes">Notes (Optional)</label>
                        <textarea class="form-control" id="projectNotes" name="notes" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="uploadProject()">Submit</button>
            </div>
        </div>
    </div>
</div>

<script>
function changeCourse(courseId) {
    if (courseId) {
        window.location.href = 'Projects.php?course_id=' + courseId;
    }
}

function submitProject(projectId) {
    $('#projectId').val(projectId);
    $('#submitProjectModal').modal('show');
}

function uploadProject() {
    var formData = new FormData($('#submitProjectForm')[0]);
    
    $.ajax({
        url: 'Action/submit-project.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            var result = JSON.parse(response);
            if (result.status === 'success') {
                alert('Project submitted successfully!');
                location.reload();
            } else {
                alert('Error: ' + result.message);
            }
        },
        error: function() {
            alert('An error occurred while submitting the project.');
        }
    });
}
</script>

<?php include "inc/Footer.php"; ?> 