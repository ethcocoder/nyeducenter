<?php 
session_start();
include "../Utils/Util.php";
include "../Utils/Validation.php";
if (isset($_SESSION['username']) &&
    isset($_SESSION['student_id'])) {
    
   if (isset($_GET['course_id'])) {
      include "../Controller/Student/EnrolledStudent.php";
      include "../Controller/Student/Course.php";
      $course_id = Validation::clean($_GET['course_id']);
    } else {
        $em = "Invalid course id ";
        Util::redirect("../index.php", "error", $em);
    }

    $chapters = getFirstChapterByCourseId($course_id);
    $topics = getFirstTopicByCourseId($course_id);

    $_chapter_id = $chapters['chapter_id'];
    $_topic_id = $topics['topic_id'];

    if(isset($_GET['chapter_id'])) {
        $_chapter_id = Validation::clean($_GET['chapter_id']);
    }
    if(isset($_GET['topic_id'])) {
        $_topic_id = Validation::clean($_GET['topic_id']);
    }

    $page_exists = isPageExes($course_id, $_chapter_id, $_topic_id);
    if($page_exists == 0) {
        Util::redirect("../404.php", "error", "404");
    }

    $course = getById($course_id, $_chapter_id, $_topic_id);

    $student_id = $_SESSION['student_id'];
    $data = array($course_id, $student_id);
    $res = check_enrolled_student($data);

    if ($res == 0) {
        $em = "You are not enrolled in this course";
        Util::redirect("Courses.php", "error", $em);
    }
    $progress = getStudentProgress($course_id, $student_id);
    if ($progress >= 100) {
        $progress = 100;
    }

    $all_chapters = count($course['chapters']);
    $chapter_val = (1 / $all_chapters) * 100;

    # Header
    $title = "EduPulse - ".$course['course']['title'];
    include "inc/Header.php";
?>
<div class="container">
    <!-- NavBar & Profile-->
    <?php include "inc/NavBar.php";?>
    <div class="side-by-side mt-5">
        <div class="l-side shadow p-3">
            <div class="d-flex p-2 justify-content-between align-items-center bg-light">
                <b>Course Content</b>
                <button id="sideBtn" class="btn fs-3 btn-light"><i class="fa fa-bars"></i></button>
            </div>
            <ul class="list-group" id="sideMenu">
                <?php 
                $i=0; 
                foreach($course['chapters'] as $chapter) { 
                    $i++; 
                    $is_current_chapter = ($chapter['chapter_id'] == $_chapter_id);
                    if ($is_current_chapter) {
                        $progress_plus = ($chapter_val*$i);
                        $current_progress = $chapter_val * $i;
                        if ($current_progress > $progress) {
                            updateStudentProgress($course_id, $student_id, $progress_plus);
                            $progress = $progress_plus;
                        }
                    }
                ?>
                <li class="list-group-item <?= $is_current_chapter ? 'active' : '' ?>">
                    <a href="javascript:void()" class="btn badge-primary">
                        <?=$chapter['title']?>
                    </a>
                    <ul>
                        <?php
                        foreach($course['topics'] as $topic) { 
                            if ($topic['chapter_id'] == $chapter['chapter_id']) {
                                $is_current_topic = ($topic['topic_id'] == $_topic_id);
                        ?>
                        <li>
                            <a href="Courses-Enrolled.php?course_id=<?=$course_id?>&chapter_id=<?=$topic['chapter_id']?>&topic_id=<?=$topic['topic_id']?>" 
                               class="btn badge-primary <?= $is_current_topic ? 'active' : '' ?>" 
                               style="color: #0D6EFD;">
                                <b><?=$topic['title']?></b>
                            </a>
                        </li>
                        <?php 
                            } else { 
                        ?>
                        <li>
                            <a href="Courses-Enrolled.php?course_id=<?=$course_id?>&chapter_id=<?=$topic['chapter_id']?>&topic_id=<?=$topic['topic_id']?>" 
                               class="btn badge-primary">
                                <?=$topic['title']?>
                            </a>
                        </li>
                        <?php 
                            }
                        } 
                        ?>
                    </ul>
                </li>
                <?php 
                } 
                ?>
            </ul>
        </div>
        <div class="r-side p-5 shadow">
            <h6><?=$course['course']['title']?></h6>
            <h6><?=$chapter['title']?></h6>
            <hr>  
            <h5><?=$topic['title']?></h5>
            <div>
                <?php 
                if (!empty($course['content']['data'])) {
                    echo $course['content']['data'];
                }
                ?>
            </div>

            <div>
                <br>
                <hr>
                <h6>Progress</h6>
                <div class="progress mb-2">
                    <div class="progress-bar" role="progressbar" style="width: <?=$progress?>%;" 
                         aria-valuenow="<?=$progress?>" aria-valuemin="0" aria-valuemax="100">
                        <?=ceil($progress)?>%
                    </div>
                </div>
                <?php if ($progress == 100) { ?>
                <div class="text-center">
                    <a class="btn btn-success" href="Action/generateCertificate.php?course_id=<?=$course['content']['course_id']?>">
                        Get Certificate
                    </a>
                </div>
                <?php } else { ?>
                <div class="text-center">
                    <a class="btn btn-warning disabled" href="#">
                        Complete the course to get certificate
                    </a>
                </div>
                <?php } ?>
            </div>
            <hr>
        </div>
    </div>
</div>

<!-- Footer -->
<?php include "inc/Footer.php"; ?>
<script src="../assets/js/jquery-3.5.1.min.js"></script>
<script>
    // Side Menu sideMenu
    $("#sideBtn").click(function(){
        $("#sideMenu").slideToggle();
    });
</script>

<?php 
} else { 
    $em = "First login ";
    Util::redirect("../login.php", "error", $em);
}
?>