<?php 
  # Header
  $course = $arrayName = array('name' => "Intro to Python");
  $title = $course['name']." - EduPulse";
  include "inc/Header.php";
?>
<div class="wrapper">
  <!-- NavBar & Profile-->
  <?php include "inc/NavBar.php";?>
  <div class="main-content p-4">
    <div class="container-fluid">
      <div class="row justify-content-center">
        <!-- Left Side: Chapters and Topics -->
        <div class="col-md-4 mb-4">
          <div class="card bg-dark text-white shadow h-100">
            <div class="card-body">
              <h5 class="mb-3">Course Content</h5>
              <ul class="list-group">
                  <li class="list-group-item bg-secondary text-white">
                    <a href="Courses-Enrolled.php?course_id=435&topc=4&chapter=1" class="btn btn-outline-primary w-100 text-start">Chapter 1</a>
                     <ul class="list-group mt-2 mb-3">
                       <li class="list-group-item bg-dark text-white">
                         <a href="Courses-Enrolled.php?course_id=435&topc=4&chapter=1" class="btn btn-outline-info w-100 text-start">Topic 1</a>
                       </li>
                       <li class="list-group-item bg-dark text-white">
                         <a href="Courses-Enrolled.php?course_id=435&topc=4&chapter=1" class="btn btn-outline-info w-100 text-start">Topic 1</a>
                       </li>
                       <li class="list-group-item bg-dark text-white">
                         <a href="Courses-Enrolled.php?course_id=435&topc=4&chapter=1" class="btn btn-outline-info w-100 text-start">Topic 1</a>
                       </li>
                     </ul>
                   </li>
                   <li class="list-group-item bg-secondary text-white">
                     <a href="Courses-Enrolled.php?course_id=435&topc=4&chapter=2" class="btn btn-outline-primary w-100 text-start">Chapter 2</a>
                     <ul class="list-group mt-2 mb-3">
                       <li class="list-group-item bg-dark text-white">
                         <a href="Courses-Enrolled.php?course_id=435&topc=4&chapter=1" class="btn btn-outline-info w-100 text-start">Topic 1</a>
                       </li>
                       <li class="list-group-item bg-dark text-white">
                         <a href="Courses-Enrolled.php?course_id=435&topc=4&chapter=1" class="btn btn-outline-info w-100 text-start">Topic 2</a>
                       </li>
                       <li class="list-group-item bg-dark text-white">
                         <a href="Courses-Enrolled.php?course_id=435&topc=4&chapter=1" class="btn btn-outline-info w-100 text-start">Topic 3</a>
                       </li>
                       
                     </ul>
                     </li>
                       <li class="list-group-item bg-secondary text-white">
                       <a href="../certificate.php?certificate_id=555" class="btn btn-outline-success w-100 text-start">Certificate</a>
                    </li>
                  
                  
                </ul>
            </div>
          </div>
        </div>
        <!-- Right Side: Content and Navigation -->
        <div class="col-md-8 mb-4">
          <div class="card bg-dark text-white shadow h-100 p-3">
            <a href="Course-edit.php" class="btn btn-primary mb-3">Update Course</a>
              <h4 class="mb-3">Chapter 1</h4>
              <h5 class="mb-3">Topic 1</h5>
                <div>
                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                    tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
                    quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
                    consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
                    cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
                    proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
                    <div class="d-flex justify-content-between mt-4">
                       <a href="Courses-Enrolled.php?course_id=435&topic=3&chapter=1" class="btn btn-secondary">Previous</a>
                       <a href="Courses-Enrolled.php?course_id=435&topc=4&chapter=1" class="btn btn-success">Next</a>
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