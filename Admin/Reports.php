<?php
session_start();
include_once "../Controller/Admin/Reports.php";
include_once "inc/Header.php";
include_once "inc/NavBar.php";
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Reports & Analytics</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Summary Cards -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?php echo getTotalEnrollments(); ?></h3>
                            <p>Total Enrollments</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?php echo getCompletionRate(); ?>%</h3>
                            <p>Course Completion Rate</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?php echo getAverageProgress(); ?>%</h3>
                            <p>Average Progress</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?php echo getActiveUsers(); ?></h3>
                            <p>Active Users</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Enrollment Trends</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="enrollmentChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Course Popularity</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="courseChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Reports -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Detailed Reports</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Top Performing Courses</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Course</th>
                                                    <th>Enrollments</th>
                                                    <th>Completion Rate</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach (getTopCourses() as $course): ?>
                                                <tr>
                                                    <td><?php echo $course['title']; ?></td>
                                                    <td><?php echo $course['enrollments']; ?></td>
                                                    <td><?php echo $course['completion_rate']; ?>%</td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h5>Student Progress</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Student</th>
                                                    <th>Courses Enrolled</th>
                                                    <th>Progress</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach (getStudentProgress() as $student): ?>
                                                <tr>
                                                    <td><?php echo $student['name']; ?></td>
                                                    <td><?php echo $student['courses_enrolled']; ?></td>
                                                    <td><?php echo $student['progress']; ?>%</td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Enrollment Trends Chart
const enrollmentCtx = document.getElementById('enrollmentChart').getContext('2d');
new Chart(enrollmentCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(getEnrollmentTrends()['labels']); ?>,
        datasets: [{
            label: 'Enrollments',
            data: <?php echo json_encode(getEnrollmentTrends()['data']); ?>,
            borderColor: 'rgb(75, 192, 192)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// Course Popularity Chart
const courseCtx = document.getElementById('courseChart').getContext('2d');
new Chart(courseCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(getCoursePopularity()['labels']); ?>,
        datasets: [{
            data: <?php echo json_encode(getCoursePopularity()['data']); ?>,
            backgroundColor: [
                'rgb(255, 99, 132)',
                'rgb(54, 162, 235)',
                'rgb(255, 205, 86)',
                'rgb(75, 192, 192)',
                'rgb(153, 102, 255)'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});
</script>

<?php include_once "inc/footer.php"; ?> 