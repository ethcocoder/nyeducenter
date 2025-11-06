<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: ../index.php");
    exit();
}

$student_id = $_SESSION['student_id'];
$title = "My Progress";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$title?> - Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'inc/NavBar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                    <h1 class="h2">My Progress</h1>
                </div>

                <!-- Progress Overview -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-dark text-white">
                            <div class="card-body">
                                <h6 class="card-title text-muted">Overall Progress</h6>
                                <div class="d-flex justify-content-between align-items-center">
                                    <h3 class="mb-0">75%</h3>
                                    <div class="icon-box bg-primary bg-opacity-10 rounded-circle p-3">
                                        <i class="fa fa-chart-line text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-dark text-white">
                            <div class="card-body">
                                <h6 class="card-title text-muted">Courses Completed</h6>
                                <div class="d-flex justify-content-between align-items-center">
                                    <h3 class="mb-0">3/5</h3>
                                    <div class="icon-box bg-success bg-opacity-10 rounded-circle p-3">
                                        <i class="fa fa-check-circle text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-dark text-white">
                            <div class="card-body">
                                <h6 class="card-title text-muted">Average Score</h6>
                                <div class="d-flex justify-content-between align-items-center">
                                    <h3 class="mb-0">85%</h3>
                                    <div class="icon-box bg-warning bg-opacity-10 rounded-circle p-3">
                                        <i class="fa fa-star text-warning"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-dark text-white">
                            <div class="card-body">
                                <h6 class="card-title text-muted">Study Streak</h6>
                                <div class="d-flex justify-content-between align-items-center">
                                    <h3 class="mb-0">7 days</h3>
                                    <div class="icon-box bg-info bg-opacity-10 rounded-circle p-3">
                                        <i class="fa fa-fire text-info"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Course Progress -->
                <div class="card bg-dark text-white mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Course Progress</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-dark table-hover">
                                <thead>
                                    <tr>
                                        <th>Course</th>
                                        <th>Progress</th>
                                        <th>Last Accessed</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Web Development Fundamentals</td>
                                        <td>
                                            <div class="progress" style="height: 5px;">
                                                <div class="progress-bar bg-success" style="width: 90%"></div>
                                            </div>
                                            <small class="text-muted">90%</small>
                                        </td>
                                        <td>2 hours ago</td>
                                        <td><span class="badge bg-success">In Progress</span></td>
                                        <td>
                                            <a href="#" class="btn btn-sm btn-primary">Continue</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Database Management</td>
                                        <td>
                                            <div class="progress" style="height: 5px;">
                                                <div class="progress-bar bg-success" style="width: 75%"></div>
                                            </div>
                                            <small class="text-muted">75%</small>
                                        </td>
                                        <td>1 day ago</td>
                                        <td><span class="badge bg-success">In Progress</span></td>
                                        <td>
                                            <a href="#" class="btn btn-sm btn-primary">Continue</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>UI/UX Design</td>
                                        <td>
                                            <div class="progress" style="height: 5px;">
                                                <div class="progress-bar bg-success" style="width: 100%"></div>
                                            </div>
                                            <small class="text-muted">100%</small>
                                        </td>
                                        <td>3 days ago</td>
                                        <td><span class="badge bg-info">Completed</span></td>
                                        <td>
                                            <a href="#" class="btn btn-sm btn-info">View Certificate</a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Learning Path Progress -->
                <div class="card bg-dark text-white mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Learning Path Progress</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-dark table-hover">
                                <thead>
                                    <tr>
                                        <th>Path</th>
                                        <th>Modules</th>
                                        <th>Progress</th>
                                        <th>Next Module</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Full Stack Development</td>
                                        <td>5/8</td>
                                        <td>
                                            <div class="progress" style="height: 5px;">
                                                <div class="progress-bar bg-success" style="width: 62%"></div>
                                            </div>
                                            <small class="text-muted">62%</small>
                                        </td>
                                        <td>Backend Development</td>
                                        <td>
                                            <a href="#" class="btn btn-sm btn-primary">Continue</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Data Science</td>
                                        <td>2/6</td>
                                        <td>
                                            <div class="progress" style="height: 5px;">
                                                <div class="progress-bar bg-success" style="width: 33%"></div>
                                            </div>
                                            <small class="text-muted">33%</small>
                                        </td>
                                        <td>Machine Learning Basics</td>
                                        <td>
                                            <a href="#" class="btn btn-sm btn-primary">Continue</a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Achievement Badges -->
                <div class="card bg-dark text-white">
                    <div class="card-header">
                        <h5 class="mb-0">Achievements</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 text-center mb-4">
                                <div class="achievement-badge">
                                    <i class="fa fa-trophy fa-3x text-warning mb-2"></i>
                                    <h6>First Course Completed</h6>
                                    <small class="text-muted">Completed your first course</small>
                                </div>
                            </div>
                            <div class="col-md-3 text-center mb-4">
                                <div class="achievement-badge">
                                    <i class="fa fa-star fa-3x text-warning mb-2"></i>
                                    <h6>Perfect Score</h6>
                                    <small class="text-muted">Achieved 100% in a course</small>
                                </div>
                            </div>
                            <div class="col-md-3 text-center mb-4">
                                <div class="achievement-badge">
                                    <i class="fa fa-fire fa-3x text-warning mb-2"></i>
                                    <h6>7-Day Streak</h6>
                                    <small class="text-muted">Studied for 7 consecutive days</small>
                                </div>
                            </div>
                            <div class="col-md-3 text-center mb-4">
                                <div class="achievement-badge">
                                    <i class="fa fa-certificate fa-3x text-warning mb-2"></i>
                                    <h6>Path Master</h6>
                                    <small class="text-muted">Completed a learning path</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../assets/js/script.js"></script>
</body>
</html> 