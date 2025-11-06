<?php
session_start();
include "../Utils/Util.php";
include "../Database.php";
include "../Models/Assessment.php";

// Set security headers
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:;");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");

if (isset($_SESSION['username']) &&
    isset($_SESSION['student_id'])) {

   $username = $_SESSION['username'];
   $student_id = $_SESSION['student_id'];

   include "../Models/Student.php";
   $db = new Database();
   $conn = $db->connect();
   $student = new Student($conn);
   $student_data = $student->getStudentById($student_id);

   $assessment = new Assessment($conn);
   $assessments = $assessment->getStudentAssessments($student_id);
   $total_assessments = $assessment->getStudentAssessmentCount($student_id);
   $items_per_page = 10;
   $total_pages = ceil($total_assessments / $items_per_page);
   $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
   $offset = ($current_page - 1) * $items_per_page;
   $assessments = $assessment->getStudentAssessments($student_id, $offset, $items_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student - Assessment</title>
    <!-- Add security headers -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:;">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <!-- Add CSRF token -->
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
</head>
<body>
    <?php 
        include "inc/sidebar.php";
    ?>
    <div class="content">
        <main>
            <div class="head-title">
                <div class="left">
                    <h1>Assessment</h1>
                    <ul class="breadcrumb">
                        <li>
                            <a href="#">Dashboard</a>
                        </li>
                        <li><i class="fa-solid fa-chevron-right"></i></li>
                        <li>
                            <span class="active">Assessment</span>
                        </li>
                    </ul>
                </div>
            </div>

            <?php if (isset($_GET['error'])) { ?>
                <div class="alert alert-danger" role="alert">
                    <?=htmlspecialchars($_GET['error'])?>
                </div>
            <?php } ?>

            <?php if (isset($_GET['success'])) { ?>
                <div class="alert alert-success" role="alert">
                    <?=htmlspecialchars($_GET['success'])?>
                </div>
            <?php } ?>

            <div class="table-data">
                <div class="order">
                    <div class="head">
                        <h3>Recent Assessment</h3>
                        <i class="fa-solid fa-search"></i>
                        <i class="fa-solid fa-filter"></i>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Duration</th>
                                <th>Due Date</th>
                                <th>Score</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($assessments) { ?>
                                <?php foreach ($assessments as $assessment) { ?>
                                    <tr>
                                        <td><?=htmlspecialchars($assessment['title'])?></td>
                                        <td><?=htmlspecialchars($assessment['description'])?></td>
                                        <td><?=htmlspecialchars($assessment['duration'])?> minutes</td>
                                        <td><?=htmlspecialchars($assessment['due_date'])?></td>
                                        <td>
                                            <?php if ($assessment['score'] !== null) { ?>
                                                <?=htmlspecialchars($assessment['score'])?>%
                                            <?php } else { ?>
                                                Not taken
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <?php if ($assessment['score'] === null) { ?>
                                                <a href="Take-Assessment.php?id=<?=htmlspecialchars($assessment['assessment_id'])?>" class="btn btn-primary">Take Assessment</a>
                                            <?php } else { ?>
                                                <a href="View-Result.php?id=<?=htmlspecialchars($assessment['assessment_id'])?>" class="btn btn-info">View Result</a>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="6" class="empty">
                                        <div class="empty-text">No assessments available</div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <?php if ($total_pages > 1) { ?>
                        <div class="pagination">
                            <?php if ($current_page > 1) { ?>
                                <a href="?page=<?=htmlspecialchars($current_page - 1)?>" class="btn btn-primary">&laquo; Previous</a>
                            <?php } ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                                <a href="?page=<?=htmlspecialchars($i)?>" class="btn <?=htmlspecialchars($i == $current_page ? 'btn-primary' : 'btn-secondary')?>">
                                    <?=htmlspecialchars($i)?>
                                </a>
                            <?php } ?>
                            
                            <?php if ($current_page < $total_pages) { ?>
                                <a href="?page=<?=htmlspecialchars($current_page + 1)?>" class="btn btn-primary">Next &raquo;</a>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </main>
    </div>

    <script src="../js/script.js"></script>
    <script>
        // Add CSRF token to all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
</body>
</html>

<?php
} else { 
    header("Location: ../login.php");
    exit();
}
?> 