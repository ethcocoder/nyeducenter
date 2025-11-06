<?php 
// session_start();
include "../Utils/Util.php";
// if (isset($_SESSION['user_name']) &&
//     isset($_SESSION['student_id'])) {

// 	 include "../Controller/Student.php";
//     $student_obj->init($_SESSION['student_id']);
//     $student = $student_obj->getStudent();

// Util::redirect("Courses.php", "", "");
  # Header
  $title = "EduPulse - Certificate Request ";
  include "inc/Header.php";
?>

<div class="wrapper">
  <!-- NavBar -->
  <?php include "inc/NavBar.php"; ?>
  
  <div class="main-content p-4">
    <div class="container-fluid d-flex justify-content-center">
      <div class="card bg-dark text-white shadow w-100" style="max-width:900px;">
        <div class="card-body">
          <h4 class="mb-4">Certificate Request</h4>

          <div class="table-responsive">
            <table class="table table-bordered table-dark table-striped">
                <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Student Name</th>
                    <th scope="col">Email</th>
                    <th scope="col">Courses Completed</th>
                    <th scope="col">Generate Certificate</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <th scope="row">1</th>
                    <td>Student 1</td>
                    <td>student1@example.com</td>
                    <td>Course 1</td>
                    <td><a href="#" class="btn btn-success" onclick="generateCertificate(1)">Generate</a></td>
                </tr>
                <tr>
                    <th scope="row">2</th>
                    <td>Student 2</td>
                    <td>student2@example.com</td>
                    <td>Course 1</td>
                    <td><a href="#" class="btn btn-success" onclick="generateCertificate(2)">Generate</a></td>
                </tr>
                
                </tbody>
            </table>
          </div>
          <div class="d-flex justify-content-center mt-3 border border-secondary p-2 rounded">
                <a href="#" class="btn btn-secondary m-1">Prev</a>
                <a href="#" class="btn btn-success m-1">&nbsp;1&nbsp;</a>
                <a href="#" class="btn btn-secondary m-1">&nbsp;2&nbsp;</a>
                <a href="#" class="btn btn-secondary m-1">&nbsp;3&nbsp;</a>
                <a href="#" class="btn btn-secondary m-1">&nbsp;4&nbsp;</a>
                <a href="#" class="btn btn-secondary m-1">Next</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
 <!-- Footer -->
<?php include "inc/Footer.php"; ?>