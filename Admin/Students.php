<?php 
session_start();
include "../Utils/Util.php";
if (isset($_SESSION['username']) &&
    isset($_SESSION['admin_id'])) {
    include "../Controller/Admin/Student.php";
    $row_count = getStudentCount();

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
    $students = getSomeStudent($offset, $row_num);
    # Header
    $title = "EduPulse - Students";
    include "inc/Header.php";
?>

<div class="wrapper">
    <?php include "inc/NavBar.php"; ?>
    <div class="main-content p-4">
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card bg-dark text-white shadow">
                    <div class="card-header">
                        <i class="fa fa-user-graduate" aria-hidden="true"></i> All Students (<?=$row_count?>)
                        <a class="btn btn-success float-end" href="Student-add.php">Add Student</a>
                    </div>
                    <div class="card-body">
                        <div class="list-table">
                            <?php if ($students) { ?>
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
                                                <td><?=$student["student_id"]?></td>
                                                <td>
                                                    <a href="Student.php?student_id=<?=$student["student_id"]?>">
                                                        <?=$student["first_name"]?> <?=$student["last_name"]?>
                                                    </a>
                                                </td>
                                                <td class="status"><?=$student["status"]?></td>
                                                <td class="action_btn">
                                                    <?php  
                                                    $student_id = $student["student_id"];
                                                    $text_temp = $student["status"] == "Active" ? "Block": "Unblock";
                                                    $btn_class = $student["status"] == "Active" ? "btn-danger" : "btn-success";
                                                    ?>
                                                    <a href="javascript:void()" 
                                                       onclick="ChangeStatus(this, <?=$student_id?>)" 
                                                       class="btn <?=$btn_class?>"><?=$text_temp?></a>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                                <?php if ($last_page > 1) { ?>
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination justify-content-center">
                                            <?php if ($page > 1) { ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
                                                </li>
                                            <?php } ?>
                                            
                                            <?php for ($i = 1; $i <= $last_page; $i++) { ?>
                                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                                </li>
                                            <?php } ?>
                                            
                                            <?php if ($page < $last_page) { ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    </nav>
                                <?php } ?>
                            <?php } else { ?>
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

<!-- Footer -->
<?php include "inc/Footer.php"; ?>

<script src="../assets/js/jquery-3.5.1.min.js"></script>
<script type="text/javascript">
    var valu = "";
    var btext = "";
    function ChangeStatus(current, stud_id) {
        var cStatus = $(current).parent().parent().children(".status").text().toString();
        if (cStatus == "Active") {
            valu = "Not Active";
            btext = "Unblock";
        } else {
            valu = "Active";
            btext = "Block";
        }
        $.post("Action/active-student.php", {
            student_id: stud_id,
            val: valu
        }, function(data, status) {
            if (status == "success") {
                $(current).parent().parent().children(".status").text(valu);
                $(current).parent().parent().children(".action_btn").children("a").text(btext);
                if (btext === "Block") {
                    $(current).removeClass("btn-success").addClass("btn-danger");
                } else {
                    $(current).removeClass("btn-danger").addClass("btn-success");
                }
            }
        });
    }
</script>

<?php
} else { 
    $em = "First login ";
    Util::redirect("../login.php", "error", $em);
}
?> 