<?php 
session_start();
include "../Utils/Util.php";
include "../Utils/Validation.php";
if (isset($_SESSION['username']) &&
    isset($_SESSION['admin_id'])) {
    
    include "../Controller/Admin/System.php";  
    // get Certificates
    $student_count = getstudentsCount();
    $Instructor_count = getInstructorCount();
    $Course_count = getCourseCount();
    
    # Header 
    $title = "EduPulse - System Analysis ";
    include "inc/Header.php";
?>
<div class="wrapper">
  <?php include "inc/NavBar.php"; ?>
  <div class="main-content p-4">
    <div class="container-fluid">
      <div class="row mb-4">
        <div class="col-md-12">
          <div class="card bg-dark text-white shadow">
            <div class="card-header text-center">System Analysis</div>
            <div class="card-body">
              <!-- Display Graphs/Charts for Analysis -->
              <div class="mb-5 card bg-dark text-white shadow">
                  <div class="card-header">Traffic Analysis</div>
                  <div class="card-body">
                      <canvas id="visitedStudentsChart" width="400" height="200"></canvas>
                  </div>
              </div>
              <div class="mb-5 overall-statistics card bg-dark text-white shadow">
                  <div class="card-header">Overall Statistics</div>
                  <div class="card-body">
                      <ul class="list-group list-group-flush">
                          <li class="list-group-item d-flex justify-content-between align-items-center bg-dark text-white">
                              Total Students <span class="badge bg-primary rounded-pill"><?=$student_count?></span>
                          </li>
                          <li class="list-group-item d-flex justify-content-between align-items-center bg-dark text-white">
                              Total Instructors <span class="badge bg-success rounded-pill"><?=$Instructor_count?></span>
                          </li>
                          <li class="list-group-item d-flex justify-content-between align-items-center bg-dark text-white">
                              Total Courses <span class="badge bg-warning rounded-pill"><?=$Course_count?></span>
                          </li>
                      </ul>
                  </div>
              </div>
              <div class="mb-4 system-activities card bg-dark text-white shadow">
                  <div class="card-header">Recent Activities</div>
                  <div class="card-body">
                      <ul class="list-group list-group-flush">
                          <li class="list-group-item bg-dark text-white">10 new students joined this week.</li>
                          <li class="list-group-item bg-dark text-white">5 new courses were created.</li>
                          <li class="list-group-item bg-dark text-white">Quiz completion rates have increased by 15%.</li>
                      </ul>
                  </div>
              </div>
              <div class="mb-5 enrollment-statistics card bg-dark text-white shadow">
                  <div class="card-header">Course Enrollment Statistics</div>
                  <div class="card-body">
                      <p>Top 3 Courses with Highest Enrollment</p>
                      <ul class="list-group list-group-flush">
                          <li class="list-group-item d-flex justify-content-between align-items-center bg-dark text-white">
                              Course A <span class="badge bg-primary rounded-pill">150 students</span>
                          </li>
                          <li class="list-group-item d-flex justify-content-between align-items-center bg-dark text-white">
                              Course B <span class="badge bg-success rounded-pill">100 students</span>
                          </li>
                          <li class="list-group-item d-flex justify-content-between align-items-center bg-dark text-white">
                              Course C <span class="badge bg-warning rounded-pill">120 students</span>
                          </li>
                      </ul>
                  </div>
              </div>
              <div class="mb-5 card bg-dark text-white shadow">
                  <div class="card-header">Expected vs Actual Student Registration This Week</div>
                  <div class="card-body d-flex justify-content-center">
                      <canvas id="registrationPieChart" width="400" height="400"></canvas>
                  </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
    // Sample data for enrollment pie chart
    var registrationPieChart = {
        labels: ['Actual', 'Expected'],
        datasets: [{
            data: [300, 500],
            backgroundColor: ['#0D6EFD', '#eee'],
        }]
    };

    // Create enrollment pie chart
    var enrollmentPieChart = new Chart(document.getElementById('registrationPieChart'), {
        type: 'pie',
        data: registrationPieChart
    });

    // Sample data for visited students bar chart
    var visitedStudentsData = {
        labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
        datasets: [{
            label: 'Visited Students',
            data: [20, 30, 25, 15],
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    };

    // Create visited students bar chart
    var visitedStudentsChart = new Chart(document.getElementById('visitedStudentsChart'), {
        type: 'bar',
        data: visitedStudentsData
    });
</script>
<!-- Footer -->
<?php include "inc/Footer.php"; ?>


<?php
 }else { 
$em = "First login ";
Util::redirect("../login.php", "error", $em);
} ?>