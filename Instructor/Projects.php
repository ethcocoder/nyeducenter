<?php
include "inc/Header.php";
requireRole(['instructor']);

// Get database connection
$conn = getDBConnection();

// Include models
require_once "../Models/Project.php";
require_once "../Models/Course.php";

$project = new Project($conn);
$course = new Course($conn);

// Get instructor's courses
$courses = $course->getByInstructorId($_SESSION['user_id']);

// Get course ID from query parameter
$course_id = isset($_GET['course_id']) ? $_GET['course_id'] : null;

// Get projects for selected course
$projects = $course_id ? $project->getByCourseId($course_id) : [];
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Course Projects</h3>
                    <div class="card-tools">
                        <select class="form-control" id="courseSelect" onchange="changeCourse(this.value)">
                            <option value="">Select Course</option>
                            <?php foreach ($courses as $c): ?>
                            <option value="<?php echo $c['course_id']; ?>" 
                                    <?php echo $course_id == $c['course_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c['title']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($course_id): ?>
                        <button type="button" class="btn btn-primary ml-2" data-toggle="modal" data-target="#addProjectModal">
                            Add New Project
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <?php if ($course_id): ?>
                        <?php if (empty($projects)): ?>
                            <p class="text-center">No projects found for this course.</p>
                        <?php else: ?>
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Due Date</th>
                                        <th>Submissions</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($projects as $proj): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($proj['title']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($proj['due_date'])); ?></td>
                                        <td>
                                            <?php 
                                            $submissions = $project->getSubmissions($proj['project_id']);
                                            echo count($submissions);
                                            ?>
                                        </td>
                                        <td>
                                            <a href="Project-View.php?id=<?php echo $proj['project_id']; ?>" 
                                               class="btn btn-info btn-sm">View</a>
                                            <button type="button" class="btn btn-primary btn-sm" 
                                                    onclick="editProject(<?php echo $proj['project_id']; ?>)">Edit</button>
                                            <button type="button" class="btn btn-danger btn-sm" 
                                                    onclick="deleteProject(<?php echo $proj['project_id']; ?>)">Delete</button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-center">Please select a course to view its projects.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Project Modal -->
<?php if ($course_id): ?>
<div class="modal fade" id="addProjectModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Project</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="addProjectForm" action="Action/Project-Action.php" method="POST">
                <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" name="description" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Rubric</label>
                        <textarea class="form-control" name="rubric" rows="5" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Due Date</label>
                        <input type="date" class="form-control" name="due_date" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="add_project">Add Project</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function changeCourse(courseId) {
    window.location.href = 'Projects.php?course_id=' + courseId;
}

function editProject(projectId) {
    window.location.href = 'Project-Edit.php?id=' + projectId;
}

function deleteProject(projectId) {
    if (confirm('Are you sure you want to delete this project?')) {
        window.location.href = 'Action/Project-Action.php?delete=' + projectId;
    }
}
</script>

<?php include "inc/Footer.php"; ?> 