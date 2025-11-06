<?php
session_start();
include "../Utils/Auth.php";
include "../Database.php";
include "inc/Header.php";
requireRole(['student']);

// Get database connection
$db = new Database();
$conn = $db->connect();

// Include models
require_once "../Models/EnrolledStudent.php";
require_once "../Models/Course.php";

$enrolled = new EnrolledStudent($conn);
$course = new Course($conn);

// Get student's enrolled courses
$enrolled_data = $enrolled->getEnrolled($_SESSION['student_id']);
$enrolled_courses = $enrolled_data[1] ?? [];

// Get course details for each enrolled course
$courses = [];
foreach ($enrolled_courses as $ec) {
    $course_details = $course->getById($ec['course_id']);
    if ($course_details) {
        $courses[] = $course_details;
    }
}
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">My Enrolled Courses</h2>
            
            <?php if (empty($courses)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> You are not enrolled in any courses yet.
                <a href="Courses.php" class="alert-link">Browse available courses</a>
            </div>
            <?php else: ?>
            <div class="row">
                <?php foreach ($courses as $course): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <?php if (!empty($course['image'])): ?>
                        <img src="../Upload/course/<?php echo htmlspecialchars($course['image']); ?>" 
                             class="card-img-top" alt="<?php echo htmlspecialchars($course['title']); ?>"
                             style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                             style="height: 200px;">
                            <i class="fas fa-book fa-3x text-muted"></i>
                        </div>
                        <?php endif; ?>
                        
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h5>
                            <p class="card-text text-muted">
                                <?php 
                                $description = htmlspecialchars($course['description']);
                                echo strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description;
                                ?>
                            </p>
                        </div>
                        
                        <div class="card-footer bg-white border-top-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="Courses-Enrolled.php?course_id=<?php echo $course['course_id']; ?>" 
                                   class="btn btn-primary">
                                    <i class="fas fa-play-circle"></i> Continue Learning
                                </a>
                                <a href="Projects.php?course_id=<?php echo $course['course_id']; ?>" 
                                   class="btn btn-outline-primary">
                                    <i class="fas fa-tasks"></i> Projects
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include "inc/Footer.php"; ?> 