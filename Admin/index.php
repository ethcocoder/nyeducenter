<?php 
session_start();
include "../Utils/Util.php";
if (isset($_SESSION['username']) &&
    isset($_SESSION['admin_id'])) {
    include "../Controller/Admin/Student.php";
    include "../Controller/Admin/Instructor.php";
    include "../Controller/Admin/Course.php";
    include "../Models/EnrolledStudent.php";
    include "../Database.php";

    // Get counts for dashboard
    $student_count = getStudentCount();
    $instructor_count = getInstructorCount();
    $course_count = getCourseCount();

    $db = new Database();
    $conn = $db->getConnection();
    $enrolled_student_model = new EnrolledStudent($conn);
    $enrollment_count = $enrolled_student_model->count();

    $page = 1;
    $row_num = 5;
    $offset = 0;
    $last_page = ceil($student_count / $row_num);
    if(isset($_GET['page'])){
    if($_GET['page'] > $last_page){
        $page = $last_page;
    }else if($_GET['page'] <= 0){
        $page = 1; 
    }else $page = $_GET['page'];
    }
    if($page != 1) $offset = ($page-1) * $row_num;
    $students = getSomeStudent($offset, $row_num);
    # Header
    $title = "EduPulse - Admin Dashboard ";
    include "inc/Header.php";

?>

<div class="wrapper">
  <!-- Sidebar -->
  <?php include "inc/NavBar.php"; ?>
  
  <div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-white">Dashboard</h2>
            <div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#registerStudentModal">
                    <i class="fa fa-user-plus"></i> Register Student
                </button>
            </div>
            <div class="profile-section d-flex align-items-center">
                <span class="me-2 text-white">Welcome, Admin!</span>
                <img src="../assets/img/default.jpg" alt="Profile" class="rounded-circle" width="40" height="40">
            </div>
        </div>

        <!-- Register Student Modal -->
        <div class="modal fade" id="registerStudentModal" tabindex="-1" aria-labelledby="registerStudentModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content bg-dark text-white">
                    <div class="modal-header">
                        <h5 class="modal-title" id="registerStudentModalLabel">Register New Student</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="Action/student-register.php" method="POST" id="studentRegisterForm">
                        <div class="modal-body">
                            <div id="registerStudentMsg"></div>
                            <div class="mb-3">
                                <label for="studentFirstName" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="studentFirstName" name="fname" placeholder="Enter student's first name" required>
                            </div>
                            <div class="mb-3">
                                <label for="studentLastName" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="studentLastName" name="lname" placeholder="Enter student's last name" required>
                            </div>
                            <div class="mb-3">
                                <label for="studentDOB" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="studentDOB" name="date_of_birth" required>
                            </div>
                            <div class="mb-3">
                                <label for="studentEmail" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="studentEmail" name="email" placeholder="Enter student's email" required>
                            </div>
                            <div class="mb-3">
                                <label for="studentUsername" class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="studentUsername" name="username" placeholder="Enter student's username" required>
                            </div>
                            <div class="mb-3">
                                <label for="studentPassword" class="form-label">Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="studentPassword" name="password" placeholder="Enter new password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="generateStudentPasswordButton" onclick="generateStudentPassword()">Auto Generate</button>
                                </div>
                            </div>
                            <small class="text-muted">* Required fields</small>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary w-100">Register</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            function generateStudentPassword() {
                const randomString = Math.random().toString(36).slice(-6);
                document.getElementById('studentPassword').value = randomString;
                document.getElementById('studentPassword').type = "text";
            }
        </script>

        <!-- Info Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card dashboard-card bg-primary text-white shadow">
                    <div class="card-body text-center">
                        <i class="fa fa-users fa-3x mb-2"></i>
                        <h4>Total Students</h4>
                        <p class="display-4"><?= $student_count ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card bg-success text-white shadow">
                    <div class="card-body text-center">
                        <i class="fa fa-users fa-3x mb-2"></i>
                        <h4>Total Instructors</h4>
                        <p class="display-4"><?= $instructor_count ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card bg-warning text-white shadow">
                    <div class="card-body text-center">
                        <i class="fa fa-graduation-cap fa-3x mb-2"></i>
                        <h4>Total Courses</h4>
                        <p class="display-4"><?= $course_count ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card bg-danger text-white shadow">
                    <div class="card-body text-center">
                        <i class="fa fa-book fa-3x mb-2"></i>
                        <h4>Total Enrollments</h4>
                        <p class="display-4"><?= $enrollment_count ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Social Media Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card dashboard-card bg-primary text-white shadow">
                    <div class="card-body text-center">
                        <i class="fa fa-facebook fa-2x mb-2"></i>
                        <h4>Facebook</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card bg-info text-white shadow">
                    <div class="card-body text-center">
                        <i class="fa fa-twitter fa-2x mb-2"></i>
                        <h4>Twitter</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card bg-secondary text-white shadow">
                    <div class="card-body text-center">
                        <i class="fa fa-linkedin fa-2x mb-2"></i>
                        <h4>LinkedIn</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card bg-danger text-white shadow">
                    <div class="card-body text-center">
                        <i class="fa fa-google fa-2x mb-2"></i>
                        <h4>Google</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts and other sections -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card bg-dark text-white shadow">
                    <div class="card-header bg-dark-subtle text-white">Sales Chart</div>
                    <div class="card-body">
                        <!-- Placeholder for a chart -->
                        <canvas id="myChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-dark text-white shadow">
                    <div class="card-header bg-dark-subtle text-white">Profile Overview</div>
                    <div class="card-body">
                        <!-- Placeholder for profile info -->
                        <p>User details and quick links here.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card bg-dark text-white shadow">
                    <div class="card-header">Student List</div>
                    <div class="card-body">
                        <div class="list-table">
                            <?php if ($students) { ?>
                            <h4 class="text-center p-2">All Students (<?= $student_count ?>)</h4>

                            <table class="table table-bordered table-dark table-hover">
                                <thead>
                                    <tr>
                                        <th>#Id</th>
                                        <th>Full name</th>
                                        <th>Status</th>
                                        <th>Block/ Unblock</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $student) {?>
                                    <tr>
                                        <td><?= $student["student_id"] ?></td>
                                        <td><a href="Student.php?student_id=<?= $student["student_id"] ?>"><?= $student["first_name"] ?> <?= $student["last_name"] ?></a></td>
                                        <td class="status"> <?= $student["status"] ?></td>
                                        <td class="action_btn">
                                            <?php  
                                            $status = $student["status"];
                                            $student_id = $student["student_id"];
                                            $text_temp = $student["status"] == "Active" ? "Block": "Unblock";
                                            ?>
                                            <a href="javascript:void()" onclick="ChangeStatus(this, <?= $student_id ?>)" class="btn btn-danger"><?= $text_temp ?></a>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                            <?php if ($last_page > 1 ) { ?>
                            <div class="d-flex justify-content-center mt-3 border">
                                <?php
                                      $prev = 1;
                                      $next = 1;
                                      $next_btn = true;
                                      $prev_btn = true;
                                      if($page <= 1) $prev_btn = false; 
                                      if($last_page ==  $page) $next_btn = false; 
                                      if($page > 1) $prev = $page - 1;
                                      if($page < $last_page) $next = $page + 1;
                                      
                                      if ($prev_btn){
                                      ?>
                                      <a href="index.php?page=<?= $prev ?>" class="btn btn-secondary m-2">Prev</a>
                                     <?php }else { ?>
                                      <a href="#" class="btn btn-secondary m-2 disabled">Prev</a>
                                      
                                     <?php 
                                     }
                                     $push_mid = $page;
                                     if ($page >= 2)  $push_mid = $page - 1;
                                     if ($page > 3)  $push_mid = $page - 3;
                                    
                                     for($i = $push_mid; $i < 5 + $page; $i++){
                                      if($i == $page){ ?>
                                       <a href="index.php?page=<?= $i ?>" class="btn btn-success m-2"><?= $i ?></a>
                                     <?php }else{ ?>
                                       <a href="index.php?page=<?= $i ?>" class="btn btn-secondary m-2"><?= $i ?></a>

                                     <?php } 
                                     if($last_page <= $i)break;

                                      } 
                                      if($next_btn){
                                      ?>
                                      <a href="index.php?page=<?= $next ?>" class="btn btn-secondary m-2">Next</a>
                                  <?php }else { ?>
                                     <a href="#" class="btn btn-secondary m-2 disabled" des>Next</a>
                                  <?php } ?>
                            </div>

                            <?php }}else { ?>
                                <div class="alert alert-info" role="alert">
                                  0 students record found in the database
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

 <!-- Footer -->
<?php include "inc/Footer.php"; ?>
<script src="../assets/js/jquery-3.5.1.min.js"></script>
<script type="text/javascript">
  var valu= "";
  var btext= "";
  function ChangeStatus(current, stud_id){
    var cStatus = $(current).parent().parent().children(".status").text().toString();
   
    if (cStatus == "Active") {
      valu = "Not Active";
      btext = "Unblock";
    }
    else {
      valu= "Active";
      btext = "Block"; 
    }

    $.post("Action/active-student.php",
    {
      student_id: stud_id,
      val: valu
    },
    function(data, status){
      if (status == "success") {
        $(current).parent().parent().children(".status").text(valu);
        $(current).parent().parent().children(".action_btn").children("a").text(btext);
       
      }

    });
  }

    // Placeholder for chart initialization
    var ctx = document.getElementById('myChart').getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
            datasets: [{
                label: 'Monthly Sales',
                data: [65, 59, 80, 81, 56, 55, 40],
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Optionally handle AJAX form submission and show messages
    document.getElementById('studentRegisterForm').onsubmit = function(e) {
        // You can implement AJAX here if you want, or let the form submit normally
    };
</script>
<?php
 }else { 
$em = "First login ";
Util::redirect("../login.php", "error", $em);
} ?>