<?php
session_start();
if (!isset($_SESSION['instructor_id'])) {
    header("Location: ../index.php");
    exit();
}

$instructor_id = $_SESSION['instructor_id'];
$title = "Progress Tracking";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$title?> - Instructor Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include 'inc/NavBar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                    <h1 class="h2">Progress Tracking</h1>
                </div>

                <!-- Overview Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-dark text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Students</h5>
                                <h2 class="card-text">150</h2>
                                <p class="text-success"><i class="fa fa-arrow-up"></i> 5% from last month</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-dark text-white">
                            <div class="card-body">
                                <h5 class="card-title">Active Courses</h5>
                                <h2 class="card-text">8</h2>
                                <p class="text-success"><i class="fa fa-arrow-up"></i> 2 new this month</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-dark text-white">
                            <div class="card-body">
                                <h5 class="card-title">Average Completion</h5>
                                <h2 class="card-text">75%</h2>
                                <p class="text-success"><i class="fa fa-arrow-up"></i> 3% from last month</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-dark text-white">
                            <div class="card-body">
                                <h5 class="card-title">Student Satisfaction</h5>
                                <h2 class="card-text">4.5/5</h2>
                                <p class="text-success"><i class="fa fa-arrow-up"></i> 0.2 from last month</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card bg-dark text-white">
                            <div class="card-body">
                                <h5 class="card-title">Course Progress Overview</h5>
                                <canvas id="courseProgressChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-dark text-white">
                            <div class="card-body">
                                <h5 class="card-title">Student Engagement</h5>
                                <canvas id="studentEngagementChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Course Progress Table -->
                <div class="card bg-dark text-white mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Course Progress Details</h5>
                        <div class="table-responsive">
                            <table class="table table-dark table-hover">
                                <thead>
                                    <tr>
                                        <th>Course</th>
                                        <th>Total Students</th>
                                        <th>Completion Rate</th>
                                        <th>Average Score</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Web Development</td>
                                        <td>45</td>
                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-success" style="width: 85%">85%</div>
                                            </div>
                                        </td>
                                        <td>92%</td>
                                        <td><span class="badge bg-success">Active</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary">View Details</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Data Science</td>
                                        <td>38</td>
                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-warning" style="width: 65%">65%</div>
                                            </div>
                                        </td>
                                        <td>78%</td>
                                        <td><span class="badge bg-success">Active</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary">View Details</button>
                                        </td>
                                    </tr>
                                    <!-- Add more courses as needed -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Student Performance -->
                <div class="card bg-dark text-white">
                    <div class="card-body">
                        <h5 class="card-title">Top Performing Students</h5>
                        <div class="table-responsive">
                            <table class="table table-dark table-hover">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Course</th>
                                        <th>Progress</th>
                                        <th>Score</th>
                                        <th>Last Active</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>John Doe</td>
                                        <td>Web Development</td>
                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-success" style="width: 95%">95%</div>
                                            </div>
                                        </td>
                                        <td>98%</td>
                                        <td>2 hours ago</td>
                                        <td>
                                            <button class="btn btn-sm btn-primary">View Profile</button>
                                        </td>
                                    </tr>
                                    <!-- Add more students as needed -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
    <script>
        // Course Progress Chart
        const courseProgressCtx = document.getElementById('courseProgressChart').getContext('2d');
        new Chart(courseProgressCtx, {
            type: 'bar',
            data: {
                labels: ['Web Dev', 'Data Science', 'Mobile Dev', 'AI/ML', 'Cloud Computing'],
                datasets: [{
                    label: 'Completion Rate',
                    data: [85, 65, 75, 80, 70],
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });

        // Student Engagement Chart
        const studentEngagementCtx = document.getElementById('studentEngagementChart').getContext('2d');
        new Chart(studentEngagementCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Active Students',
                    data: [120, 135, 145, 150, 155, 160],
                    borderColor: 'rgba(153, 102, 255, 1)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html> 