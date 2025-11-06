<?php 
  # Header
  $title = "EduPulse - Courses";
  include "inc/Header.php";
?>
<div class="wrapper">
  <!-- NavBar -->
  <?php include "inc/NavBar.php"; ?>

  <div class="main-content p-4">
    <div class="container-fluid d-flex justify-content-center">
      <div class="card bg-dark text-white shadow w-100" style="max-width:900px;">
        <div class="card-body">
          <h4 class="mb-4">Edit Course</h4>
          <form>
              <div class="mb-3">
                <label class="form-label">Course Title:</label>
                <input type="text" 
                       class="form-control"
                       value="Machine Learning Algorithms in Python">
              </div>
              <div class="mb-3">
                <label class="form-label">Course Description</label>
                <textarea name="" class="form-control" rows="3">Machine learning (ML) is a subfield of artificial intelligence (AI) that focuses on developing algorithms and models that enable computers to learn patterns from data and make predictions or decisions without being explicitly programmed.
                </textarea>
              </div>
              <button type="submit" class="btn btn-primary">Save</button>
            </form>

            <hr class="my-4">

            <h4 class="mb-4">Chapters</h4>
            <form>
              <div class="mb-3">
                <input type="text" 
                       class="form-control"
                       value="Chapter-1">
              </div>
              <div class="mb-3">
                <input type="text" 
                       class="form-control"
                       value="Chapter-2">
              </div>
            <button type="submit" class="btn btn-primary">Save</button>
            </form>

            <hr class="my-4">

            <h4 class="mb-4">Topics</h4>
            <form>
            <ul class="list-group mb-3">
              <li class="list-group-item bg-secondary text-white">
                  Chapter-1
                  <ul class="list-group mb-3 mt-2">
                     <li class="list-group-item bg-dark text-white">
                      <div class="mb-3 d-flex align-items-center">
                        <a href="Courses-content-edit.php" class="btn btn-sm btn-outline-info me-2"> <i class="fa fa-edit"></i></a>
                        <input type="text" 
                               class="form-control"
                               value="Topic-1">
                      </div>
                      <div class="mb-3 d-flex align-items-center">
                        <a href="Courses-content-edit.php" class="btn btn-sm btn-outline-info me-2"> <i class="fa fa-edit"></i></a>
                        <input type="text" 
                               class="form-control"
                               value="Topic-2">
                      </div>
                     </li>
                  </ul>
              </li>
              <li class="list-group-item bg-secondary text-white">
                  Chapter-2 
                  <ul class="list-group mb-3 mt-2">
                     <li class="list-group-item bg-dark text-white">
                      <div class="mb-3 d-flex align-items-center">
                        <a href="Courses-content-edit.php" class="btn btn-sm btn-outline-info me-2"> <i class="fa fa-edit"></i></a>
                        <input type="text" 
                               class="form-control"
                               value="Topic-1">
                      </div>
                      <div class="mb-3 d-flex align-items-center">
                        <a href="Courses-content-edit.php" class="btn btn-sm btn-outline-info me-2"> <i class="fa fa-edit"></i></a>
                        <input type="text" 
                               class="form-control"
                               value="Topic-2">
                      </div>
                     </li>
                  </ul>
              </li>
            </ul>
              <button type="submit" class="btn btn-primary">Save</button>
            </form>
        </div>
      </div>
    </div>
  </div>
</div>

 <!-- Footer -->
<?php include "inc/Footer.php"; ?>