<?php 
session_start();
include "../Utils/Util.php";
if (isset($_SESSION['username']) && isset($_SESSION['teacher_id'])) {
    include "../Controller/Teacher/LearningPath.php";
    
    $teacher_id = $_SESSION['teacher_id'];
    $learning_paths = getTeacherLearningPaths($teacher_id);
    $total_students = getTotalEnrolledStudents($teacher_id);
    $active_students = getActiveStudents($teacher_id);
    $total_modules = getTotalModules($teacher_id);
    $recent_activities = getRecentActivities($teacher_id);
    
    # Header
    $title = "EduPulse - Teacher Dashboard";
    include "inc/Header.php";
?>

<div class="wrapper">
    <?php include "inc/NavBar.php"; ?>
    
    <div class="main-content p-4">
        <div class="container-fluid">
            <!-- Welcome Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="text-white mb-2">Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h2>
                    <p class="text-muted mb-0">Here's what's happening with your learning paths</p>
                </div>
                <div>
                    <a href="Create-Learning-Path.php" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Create New Path
                    </a>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-dark text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title text-muted">Total Students</h6>
                                    <h3 class="mb-0"><?= $total_students ?></h3>
                                </div>
                                <div class="icon-box bg-primary bg-opacity-10 rounded-circle p-3">
                                    <i class="fa fa-users text-primary"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <span class="text-success">
                                    <i class="fa fa-arrow-up"></i> <?= round(($active_students / $total_students) * 100) ?>%
                                </span>
                                <span class="text-muted ms-2">Active</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-dark text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title text-muted">Learning Paths</h6>
                                    <h3 class="mb-0"><?= count($learning_paths) ?></h3>
                                </div>
                                <div class="icon-box bg-success bg-opacity-10 rounded-circle p-3">
                                    <i class="fa fa-graduation-cap text-success"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <span class="text-success">
                                    <i class="fa fa-check-circle"></i> <?= $total_modules ?>
                                </span>
                                <span class="text-muted ms-2">Total Modules</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-dark text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title text-muted">Active Students</h6>
                                    <h3 class="mb-0"><?= $active_students ?></h3>
                                </div>
                                <div class="icon-box bg-warning bg-opacity-10 rounded-circle p-3">
                                    <i class="fa fa-user-clock text-warning"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <span class="text-warning">
                                    <i class="fa fa-clock"></i> Last 7 days
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-dark text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title text-muted">Average Progress</h6>
                                    <h3 class="mb-0"><?= getAverageProgress($teacher_id) ?>%</h3>
                                </div>
                                <div class="icon-box bg-info bg-opacity-10 rounded-circle p-3">
                                    <i class="fa fa-chart-line text-info"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <span class="text-info">
                                    <i class="fa fa-trending-up"></i> Overall
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Learning Paths -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card bg-dark text-white">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Your Learning Paths</h5>
                            <a href="Learning-Paths.php" class="btn btn-sm btn-primary">View All</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-dark table-hover">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Students</th>
                                            <th>Modules</th>
                                            <th>Progress</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($learning_paths, 0, 5) as $path) { ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="fa fa-graduation-cap text-primary me-2"></i>
                                                        <?= htmlspecialchars($path['title']) ?>
                                                    </div>
                                                </td>
                                                <td><?= $path['enrolled_students'] ?></td>
                                                <td><?= $path['total_modules'] ?></td>
                                                <td>
                                                    <div class="progress" style="height: 5px;">
                                                        <div class="progress-bar bg-success" 
                                                             role="progressbar" 
                                                             style="width: <?= $path['average_progress'] ?>%">
                                                        </div>
                                                    </div>
                                                    <small class="text-muted"><?= $path['average_progress'] ?>%</small>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="Manage-Modules.php?path_id=<?= $path['id'] ?>" 
                                                           class="btn btn-sm btn-primary" 
                                                           title="Manage Modules">
                                                            <i class="fa fa-cog"></i>
                                                        </a>
                                                        <a href="Track-Progress.php?path_id=<?= $path['id'] ?>" 
                                                           class="btn btn-sm btn-info" 
                                                           title="Track Progress">
                                                            <i class="fa fa-chart-line"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-dark text-white">
                        <div class="card-header">
                            <h5 class="mb-0">Recent Activity</h5>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                <?php foreach ($recent_activities as $activity) { ?>
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

            <!-- Quick Actions -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card bg-dark text-white">
                        <div class="card-header">
                            <h5 class="mb-0">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <a href="Create-Learning-Path.php" class="btn btn-primary w-100">
                                        <i class="fa fa-plus-circle me-2"></i> New Learning Path
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <a href="Manage-Students.php" class="btn btn-info w-100">
                                        <i class="fa fa-users me-2"></i> Manage Students
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <a href="Support-Requests.php" class="btn btn-warning w-100">
                                        <i class="fa fa-life-ring me-2"></i> Support Requests
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <a href="Reports.php" class="btn btn-success w-100">
                                        <i class="fa fa-chart-bar me-2"></i> View Reports
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-dark text-white">
                        <div class="card-header">
                            <h5 class="mb-0">Upcoming Tasks</h5>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <?php foreach (getUpcomingTasks($teacher_id) as $task) { ?>
                                    <div class="list-group-item bg-dark text-white border-secondary">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1"><?= htmlspecialchars($task['title']) ?></h6>
                                                <small class="text-muted">
                                                    <?= htmlspecialchars($task['description']) ?>
                                                </small>
                                            </div>
                                            <span class="badge bg-<?= $task['priority'] == 'high' ? 'danger' : 
                                                                  ($task['priority'] == 'medium' ? 'warning' : 'info') ?>">
                                                <?= ucfirst($task['priority']) ?>
                                            </span>
                                        </div>
                                        <small class="text-muted">
                                            Due: <?= date('M d, Y', $task['due_date']) ?>
                                        </small>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.icon-box {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
}

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

<?php include "inc/Footer.php"; ?>
<?php
} else { 
    $em = "First login ";
    Util::redirect("../login.php", "error", $em);
}
?> 