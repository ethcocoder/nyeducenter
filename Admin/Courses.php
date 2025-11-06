<?php 
session_start();
include "../Utils/Util.php";
if (isset($_SESSION['username']) &&
    isset($_SESSION['admin_id'])) {
    include "../Controller/Admin/Course.php";
    $row_count = getCourseCount();

    $page = 1;
    $row_num = 5;
    $offset = 0;
    $last_page = ceil($row_count / $row_num);
    if(isset($_GET['page'])){
    if($_GET['page'] > $last_page){
        $page = $last_page;
    }else if($_GET['page'] <= 0){
        $page = 1; 
    }else $page = $_GET['page'];
    }
    if($page != 1) $offset = ($page-1) * $row_num;
    $courses = getSomeCourses($offset, $row_num);
    # Header
    $title = "EduPulse - Courses ";
    include "inc/Header.php";

?>

<div class="wrapper">
  <?php include "inc/NavBar.php"; ?>
  <div class="main-content p-4">
    <div class="container-fluid">
      <div class="row mb-4">
        <div class="col-md-12">
          <div class="card bg-dark text-white shadow">
            <div class="card-header">
              <i class="fa fa-graduation-cap" aria-hidden="true"></i> All Courses (<?=$row_count?>)
              <a class="btn btn-success float-end" href="Course-add.php">Add Course</a>
            </div>
            <div class="card-body">
              <div class="list-table">
                <?php if ($courses) { ?>
                  <table class="table table-bordered table-dark table-hover">
                    <thead>
                      <tr>
                        <th>#Id</th>
                        <th>Full name</th>
                        <th>Status</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($courses as $course) {?>
                        <tr>
                          <td><?=$course["course_id"]?></td>
                          <td><a href="course.php?course_id=<?=$course["course_id"]?>"><?=$course["title"]?></a></td>
                          <td class="status"> <?=$course["status"]?></td>
                          <td class="action_btn">
                            <?php  
                            $status = $course["status"];
                            $course_id = $course["course_id"];
                            $text_temp = $course["status"] == "Public" ? "Private": "Public";
                            $btn_class = $course["status"] == "Public" ? "btn-warning" : "btn-success";
                            ?> 
                            <a href="javascript:void()" onclick="ChangeStatus(this, <?=$course_id?>)" class="btn <?=$btn_class?>"><?=$text_temp?></a>
                            <a href="javascript:void()" onclick="deleteCourse(<?=$course_id?>)" class="btn btn-danger">Delete</a>
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
                        <a href="Courses.php?page=<?=$prev?>" class="btn btn-secondary m-2">Prev</a>
                      <?php }else { ?>
                        <a href="#" class="btn btn-secondary m-2 disabled">Prev</a>
                      <?php }
                        $push_mid = $page;
                        if ($page >= 2)  $push_mid = $page - 1;
                        if ($page > 3)  $push_mid = $page - 3;
                        for($i = $push_mid; $i < 5 + $page; $i++){
                          if($i == $page){ ?>
                            <a href="Courses.php?page=<?=$i?>" class="btn btn-success m-2"><?=$i?></a>
                          <?php }else{ ?>
                            <a href="Courses.php?page=<?=$i?>" class="btn btn-secondary m-2"><?=$i?></a>
                          <?php } 
                          if($last_page <= $i)break;
                        } 
                        if($next_btn){
                      ?>
                        <a href="Courses.php?page=<?=$next?>" class="btn btn-secondary m-2">Next</a>
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
<script src="../assets/js/jquery-3.5.1.min.js"></script>
<script type="text/javascript">
  var valu= "";
  var btext= "";
  function ChangeStatus(current, cou_id){
    var cStatus = $(current).parent().parent().children(".status").text().toString();
    if (cStatus == "Private") {
      valu = "Public";
      btext = "Private";
    }
    else {
      valu= "Private"; 
      btext = "Public"; 
    }
    $.post("Action/active-course.php",
    {
      course_id: cou_id,
      val: valu
    },
    function(data, status){
      if (status == "success") {
        $(current).parent().parent().children(".status").text(valu);
        $(current).parent().parent().children(".action_btn").children("a").text(btext);
        if(btext === "Private") {
          $(current).removeClass("btn-success").addClass("btn-warning");
        } else {
          $(current).removeClass("btn-warning").addClass("btn-success");
        }
      }
    });
  }
</script>
<script>
function deleteCourse(courseId) {
    if (confirm('Are you sure you want to delete this course? This action cannot be undone.')) {
        window.location.href = 'Action/course-delete.php?course_id=' + courseId;
    }
}
</script>
<!-- Footer -->
<?php include "inc/Footer.php"; ?>
<?php
 }else { 
$em = "First login ";
Util::redirect("../login.php", "error", $em);
} ?>