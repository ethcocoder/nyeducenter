<?php 
session_start();
include "../Utils/Util.php";
if (isset($_SESSION['username']) && isset($_SESSION['student_id'])) {
    include "../Controller/Student/Dashboard.php";
    
    $student_id = $_SESSION['student_id'];
    $enrolled_paths = getEnrolledLearningPaths($student_id);
    $upcoming_deadlines = getUpcomingDeadlines($student_id);
    $recent_activities = getRecentActivities($student_id);
    $progress_stats = getProgressStatistics($student_id);
    
    # Header
    $title = "EduPulse - Student Dashboard";
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
                    <p class="text-muted mb-0">Track your learning progress</p>
                </div>
                <div>
                    <a href="Learning-Paths.php" class="btn btn-primary">
                        <i class="fa fa-graduation-cap"></i> Browse Paths
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
                                    <h6 class="card-title text-muted">Overall Progress</h6>
                                    <h3 class="mb-0"><?= $progress_stats['overall_progress'] ?>%</h3>
                                </div>
                                <div class="icon-box bg-primary bg-opacity-10 rounded-circle p-3">
                                    <i class="fa fa-chart-line text-primary"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <span class="text-success">
                                    <i class="fa fa-arrow-up"></i> <?= $progress_stats['progress_increase'] ?>%
                                </span>
                                <span class="text-muted ms-2">This week</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-dark text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title text-muted">Enrolled Paths</h6>
                                    <h3 class="mb-0"><?= count($enrolled_paths) ?></h3>
                                </div>
                                <div class="icon-box bg-success bg-opacity-10 rounded-circle p-3">
                                    <i class="fa fa-graduation-cap text-success"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <span class="text-success">
                                    <i class="fa fa-check-circle"></i> <?= $progress_stats['completed_modules'] ?>
                                </span>
                                <span class="text-muted ms-2">Completed</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-dark text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title text-muted">Average Score</h6>
                                    <h3 class="mb-0">
                                        <?php if ($progress_stats['average_score'] > 0) { ?>
                                            <span class="badge bg-<?= $progress_stats['average_score'] >= 70 ? 'success' : 
                                                                  ($progress_stats['average_score'] >= 50 ? 'warning' : 'danger') ?>">
                                                <?= $progress_stats['average_score'] ?>%
                                            </span>
                                        <?php } else { ?>
                                            <span class="text-muted">N/A</span>
                                        <?php } ?>
                                    </h3>
                                </div>
                                <div class="icon-box bg-warning bg-opacity-10 rounded-circle p-3">
                                    <i class="fa fa-star text-warning"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <span class="text-warning">
                                    <i class="fa fa-trophy"></i> <?= $progress_stats['certificates'] ?>
                                </span>
                                <span class="text-muted ms-2">Certificates</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-dark text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title text-muted">Study Time</h6>
                                    <h3 class="mb-0"><?= round($progress_stats['total_time'] / 3600, 1) ?>h</h3>
                                </div>
                                <div class="icon-box bg-info bg-opacity-10 rounded-circle p-3">
                                    <i class="fa fa-clock text-info"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <span class="text-info">
                                    <i class="fa fa-calendar"></i> <?= $progress_stats['streak'] ?>
                                </span>
                                <span class="text-muted ms-2">Day streak</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <!-- Learning Paths -->
                    <div class="card bg-dark text-white">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">My Learning Paths</h5>
                            <a href="Learning-Paths.php" class="btn btn-sm btn-primary">View All</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-dark table-hover">
                                    <thead>
                                        <tr>
                                            <th>Path</th>
                                            <th>Progress</th>
                                            <th>Next Module</th>
                                            <th>Due Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($enrolled_paths, 0, 5) as $path) { ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="fa fa-graduation-cap text-primary me-2"></i>
                                                        <?= htmlspecialchars($path['title']) ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="progress" style="height: 5px;">
                                                        <div class="progress-bar bg-success" 
                                                             role="progressbar" 
                                                             style="width: <?= $path['progress'] ?>%">
                                                        </div>
                                                    </div>
                                                    <small class="text-muted"><?= $path['progress'] ?>%</small>
                                                </td>
                                                <td>
                                                    <?php if ($path['next_module']) { ?>
                                                        <small><?= htmlspecialchars($path['next_module']) ?></small>
                                                    <?php } else { ?>
                                                        <span class="text-muted">Completed</span>
                                                    <?php } ?>
                                                </td>
                                                <td>
                                                    <?php if ($path['due_date']) { ?>
                                                        <span class="badge bg-<?= $path['days_left'] <= 3 ? 'danger' : 
                                                                              ($path['days_left'] <= 7 ? 'warning' : 'info') ?>">
                                                            <?= $path['days_left'] ?> days left
                                                        </span>
                                                    <?php } else { ?>
                                                        <span class="text-muted">No deadline</span>
                                                    <?php } ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="Continue-Learning.php?path_id=<?= $path['id'] ?>" 
                                                           class="btn btn-sm btn-primary" 
                                                           title="Continue Learning">
                                                            <i class="fa fa-play"></i>
                                                        </a>
                                                        <a href="Path-Progress.php?path_id=<?= $path['id'] ?>" 
                                                           class="btn btn-sm btn-info" 
                                                           title="View Progress">
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
                    <!-- Upcoming Deadlines -->
                    <div class="card bg-dark text-white">
                        <div class="card-header">
                            <h5 class="mb-0">Upcoming Deadlines</h5>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <?php foreach ($upcoming_deadlines as $deadline) { ?>
                                    <div class="list-group-item bg-dark text-white border-secondary">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1"><?= htmlspecialchars($deadline['title']) ?></h6>
                                                <small class="text-muted">
                                                    <?= htmlspecialchars($deadline['path_title']) ?>
                                                </small>
                                            </div>
                                            <span class="badge bg-<?= $deadline['days_left'] <= 3 ? 'danger' : 
                                                                  ($deadline['days_left'] <= 7 ? 'warning' : 'info') ?>">
                                                <?= $deadline['days_left'] ?> days left
                                            </span>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="row">
                <div class="col-md-12">
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